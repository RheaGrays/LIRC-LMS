<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\AttendanceLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Lookup a student by ID (called via AJAX from kiosk).
     */
    public function lookup(Request $request): JsonResponse
    {
        $term = trim($request->input('student_id', ''));
        if (!$term) {
            return response()->json(['error' => 'No student ID or name provided.'], 422);
        }

        $student = $this->resolveStudent($term);
        if ($student === 'AMBIGUOUS') {
            return response()->json(['found' => false, 'reason' => 'Multiple students match that name. Please use your exact Student ID.']);
        }
        if (!$student) {
            return response()->json(['found' => false, 'reason' => 'Student not found.']);
        }

        if ($student->status === 'inactive') {
            return response()->json([
                'found'  => true,
                'denied' => true,
                'reason' => 'Student account is inactive.',
                'student' => $this->formatStudent($student),
            ]);
        }

        return response()->json([
            'found'   => true,
            'denied'  => false,
            'student' => $this->formatStudent($student),
        ]);
    }

    /**
     * Process a student scan (lookup, check last action, and log) in a single request.
     */
    public function process(Request $request): JsonResponse
    {
        // Sanitize input: Barcode scanners sometimes type '/' instead of '-' depending on keyboard layout
        $term = str_replace('/', '-', trim($request->input('student_id', '')));
        
        if (!$term) {
            return response()->json(['status' => 'error', 'message' => 'No student ID or name provided.'], 422);
        }

        $student = $this->resolveStudent($term);
        if ($student === 'AMBIGUOUS') {
            return response()->json(['status' => 'error', 'message' => 'Multiple students match that name. Please use your exact Student ID.']);
        }
        if (!$student) {
            $event = [
                'status'   => 'error',
                'action'   => 'unregistered',
                'message'  => "Student ID \"{$term}\" is not registered in the system. Please register first.",
                'student'  => [
                    'id'    => $term,
                    'name'  => 'UNREGISTERED PATRON',
                    'dept'  => 'Registration Required',
                    'photo_url' => null,
                ]
            ];
            $event = $this->pushScanEvent($event);
            return response()->json($event);
        }

        if ($student->status === 'inactive') {
            $event = [
                'status'   => 'error',
                'action'   => 'inactive',
                'message'  => 'Student account is inactive.',
                'student'  => $this->formatStudent($student),
            ];
            $event = $this->pushScanEvent($event);
            return response()->json($event);
        }

        // ── Cooldown Buffer (Prevents duplicate scans within 5 minutes) ──
        // Use an atomic lock to prevent race conditions from concurrent requests
        $cooldownMinutes = (int) \App\Models\SystemSetting::get('checkin_cooldown_minutes', 5);
        $lockKey = 'checkin_lock:' . $student->id;

        $requestedAction = $request->input('action');
        $kioskMode = \App\Models\SystemSetting::get('kiosk_mode', 'check_in_only');

        try {
            $event = Cache::lock($lockKey, 10)->block(2, function () use ($student, $cooldownMinutes, $requestedAction, $kioskMode) {
                $recentLog = AttendanceLog::query()->where('student_id', $student->id)
                    ->where('logged_at', '>=', now()->subMinutes($cooldownMinutes))
                    ->orderByDesc('logged_at')
                    ->first();

                if ($recentLog) {
                    return [
                        'status'   => 'cooldown',
                        'action'   => $recentLog->action,
                        'message'  => 'Already ' . ($recentLog->action === 'check_out' ? 'checked out' : 'checked in') . ' (5-min cooldown active).',
                        'student'  => $this->formatStudent($student)
                    ];
                }

                // Determine action: explicit parameter > toggle mode > default check_in
                if (in_array($requestedAction, ['check_in', 'check_out'], true)) {
                    $nextAction = $requestedAction;
                } elseif ($kioskMode === 'toggle') {
                    $lastLog = AttendanceLog::query()
                        ->where('student_id', $student->id)
                        ->where('logged_at', '>=', now()->startOfDay())
                        ->orderByDesc('logged_at')
                        ->first();
                    $nextAction = ($lastLog && $lastLog->action === 'check_in') ? 'check_out' : 'check_in';
                } else {
                    $nextAction = 'check_in';
                }

                // Log action
                $log = AttendanceLog::create([
                    'student_id' => $student->id,
                    'action'     => $nextAction,
                    'logged_at'  => now(),
                ]);

                // Invalidate real-time occupancy and dashboard statistics cache
                Cache::forget('occupancy_today');
                Cache::forget('dashboard_today_entries');

                $message = $nextAction === 'check_out' ? 'Successfully checked out.' : 'Successfully checked in.';

                return [
                    'db_id'   => $log->id,
                    'status'  => 'success',
                    'action'  => $nextAction,
                    'message' => $message,
                    'student' => $this->formatStudent($student)
                ];
            });
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            // BUG-01 FIX: Do NOT fake a success. Return a retriable server-busy error
            // so the kiosk falls back to the offline queue rather than silently losing the scan.
            return response()->json([
                'status'  => 'error',
                'message' => 'Server is busy processing another scan. Please try again.',
            ], 503);
        }

        $event = $this->pushScanEvent($event);

        return response()->json($event);
    }

    /**
     * Search students for Kiosk manual entry autocomplete
     */
    public function search(Request $request): JsonResponse
    {
        $term = trim($request->input('q', ''));
        if (strlen($term) < 2) return response()->json([]);

        $searchTerm = '%' . $term . '%';
        
        $driver = DB::connection()->getDriverName();
        $concatSql = $driver === 'sqlite' ? "first_name || ' ' || last_name" : "CONCAT(first_name, ' ', last_name)";
        $concatSqlRev = $driver === 'sqlite' ? "last_name || ' ' || first_name" : "CONCAT(last_name, ' ', first_name)";

        $students = Student::query()->with('academicDepartment')
            ->where(function($q) use ($searchTerm, $concatSql, $concatSqlRev) {
                // BUG-02 FIX: whereRaw/orWhereRaw only accept 2 args; removed invalid 3rd arg
                $q->where('id', 'LIKE', $searchTerm)
                  ->orWhereRaw("{$concatSql} LIKE ?", [$searchTerm])
                  ->orWhereRaw("{$concatSqlRev} LIKE ?", [$searchTerm]);
            })
            ->limit(5)
            ->get();

        return response()->json($students->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->first_name . ' ' . $s->last_name,
                'department' => $s->academicDepartment?->name ?? '—',
                'photo' => $s->photo_path ? asset('storage/' . $s->photo_path) : '/default-avatar.png'
            ];
        }));
    }

    /**
     * Log an attendance action (check_in or check_out).
     */
    public function log(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => ['required', 'string'],
            'action'     => ['required', 'in:check_in,check_out'],
        ]);

        $student = $this->resolveStudent($request->student_id);
        if ($student === 'AMBIGUOUS') {
            return response()->json(['error' => 'Multiple students match that name. Please use your exact Student ID.'], 400);
        }
        if (!$student) {
            return response()->json(['error' => 'Student not found.'], 404);
        }

        // ── Cooldown Buffer (Prevents duplicate scans within 5 minutes) ──
        $cooldownMinutes = (int) \App\Models\SystemSetting::get('checkin_cooldown_minutes', 5);
        $lockKey = 'checkin_lock:' . $student->id;

        // BUG-NEW-03 FIX: Wrap in try/catch so a lock timeout returns a retriable 503
        // instead of an uncaught exception that crashes offline queue replay.
        try {
            $result = Cache::lock($lockKey, 10)->block(5, function () use ($student, $request, $cooldownMinutes) {
                $recentLog = AttendanceLog::query()->where('student_id', $student->id)
                    ->where('logged_at', '>=', now()->subMinutes($cooldownMinutes))
                    ->first();

                if ($recentLog) {
                    return response()->json([
                        'success'   => true,
                        'duplicate' => true,
                        'message'   => 'Duplicate scan ignored (within 5-minute cooldown).'
                    ]);
                }

                AttendanceLog::create([
                    'student_id' => $student->id,
                    'action'     => $request->action,
                    'logged_at'  => now(),
                ]);

                // BUG-NEW-02 FIX: Invalidate occupancy and dashboard caches after write
                // so offline-replayed scans are reflected immediately in the dashboard.
                Cache::forget('occupancy_today');
                Cache::forget('dashboard_today_entries');

                return response()->json(['success' => true]);
            });
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server busy processing another scan. Please retry.',
            ], 503);
        }

        return $result;
    }


    /**
     * Get the last action today for a student (check_in or check_out).
     */
    public function lastAction(Request $request): JsonResponse
    {
        $term = trim($request->input('student_id', ''));
        $student = $this->resolveStudent($term);
        if ($student === 'AMBIGUOUS') {
            return response()->json(['error' => 'Multiple students match that name.'], 400);
        }
        $studentId = $student ? $student->id : null;

        $today = now()->startOfDay();

        $log = null;
        if ($studentId) {
            $log = AttendanceLog::query()->where('student_id', $studentId)
                ->where('logged_at', '>=', $today)
                ->orderByDesc('logged_at')
                ->first();
        }

        return response()->json(['action' => $log?->action]);
    }

    /**
     * Get current library occupancy.
     */
    public function occupancy(): JsonResponse
    {
        return response()->json(\App\Services\OccupancyService::today());
    }

    /**
     * Polling endpoint for real-time kiosk display updates.
     * Returns all unread events with seq_id > after_id from the sequential event queue.
     */
    public function latestScan(Request $request): JsonResponse
    {
        $afterSeq = (int) $request->query('after_id', 0);
        $events = Cache::get('kiosk_scan_events_queue', []);

        // Filter unread events with seq_id > afterSeq
        $unreadEvents = array_values(array_filter($events, function ($e) use ($afterSeq) {
            return isset($e['seq_id']) && (int)$e['seq_id'] > $afterSeq;
        }));

        if (!empty($unreadEvents)) {
            return response()->json($unreadEvents);
        }

        // BUG-NEW-06 FIX: The fallback now queries by today's date, not by log ID.
        // The previous code used `WHERE id > $afterSeq`, but $afterSeq is a Cache::increment()
        // counter (starts at 1 each boot) while attendance_logs.id is a DB auto-increment
        // potentially in the thousands — so the comparison was meaningless and would either
        // replay old logs or return nothing depending on ID alignment.
        //
        // The fallback's only purpose is to recover display continuity after a cache flush.
        // Returning the most recent log from today is the correct recovery behaviour.
        $latestLog = AttendanceLog::query()
            ->where('logged_at', '>=', now()->startOfDay())
            ->with('student')
            ->orderByDesc('logged_at')
            ->first();

        if (!$latestLog || !$latestLog->student) {
            return response()->json(null);
        }

        $fallbackEvent = [
            'seq_id'  => $latestLog->id,
            'id'      => $latestLog->id,
            'status'  => 'success',
            'action'  => $latestLog->action,
            'message' => $latestLog->action === 'check_out' ? 'Successfully checked out.' : 'Successfully checked in.',
            'student' => $this->formatStudent($latestLog->student)
        ];

        return response()->json([$fallbackEvent]);
    }

    /**
     * Push a scan event onto the global sequential event queue with a monotonic sequence ID.
     */
    private function pushScanEvent(array $event): array
    {
        $seqId = Cache::increment('kiosk_global_event_seq');
        $event['seq_id'] = $seqId;
        $event['id']     = $seqId;

        $queue = Cache::get('kiosk_scan_events_queue', []);
        $queue[] = $event;

        // Keep last 50 events in buffer
        if (count($queue) > 50) {
            $queue = array_slice($queue, -50);
        }

        Cache::put('kiosk_scan_events_queue', $queue, 300);

        return $event;
    }

    private function resolveStudent(string $term): Student|string|null
    {
        if (!$term) return null;

        // Try exact ID match first
        /** @var Student|null $student */
        $student = Student::query()->find($term);
        if ($student) return $student;

        // Try name search with proper parameter binding
        $searchTerm = '%' . trim($term) . '%';
        
        $driver = DB::connection()->getDriverName();
        $concatSql = $driver === 'sqlite' ? "first_name || ' ' || last_name" : "CONCAT(first_name, ' ', last_name)";
        $concatSqlRev = $driver === 'sqlite' ? "last_name || ' ' || first_name" : "CONCAT(last_name, ' ', first_name)";
        
        // BUG-02 FIX: whereRaw/orWhereRaw only accept 2 args; removed invalid 3rd arg
        $students = Student::query()
            ->where(function ($q) use ($concatSql, $concatSqlRev, $searchTerm) {
                $q->whereRaw("{$concatSql} LIKE ?", [$searchTerm])
                  ->orWhereRaw("{$concatSqlRev} LIKE ?", [$searchTerm])
                  ->orWhere('first_name', 'LIKE', $searchTerm)
                  ->orWhere('last_name', 'LIKE', $searchTerm);
            })
            ->get();
            
        if ($students->count() === 1) {
            return $students->first();
        } elseif ($students->count() > 1) {
            return 'AMBIGUOUS';
        }
        
        return null;
    }
    
    private function formatStudent(Student $s): array
    {
        $s->loadMissing(['academicDepartment', 'academicProgram']);
        
        $deptText = $s->academicDepartment?->name ?? '—';
        if ($s->academicProgram) {
            $deptText .= ' - ' . $s->academicProgram->code;
        }

        return [
            'id'        => $s->id,
            'name'      => $s->full_name,
            'dept'      => $deptText,
            'year'      => $s->year_level,
            'photo_url' => $s->photo_url,
        ];
    }
}
