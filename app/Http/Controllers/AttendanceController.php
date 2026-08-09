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
        $term = trim($request->input('student_id', ''));
        if (!$term) {
            return response()->json(['status' => 'error', 'message' => 'No student ID or name provided.'], 422);
        }

        $student = $this->resolveStudent($term);
        if ($student === 'AMBIGUOUS') {
            return response()->json(['status' => 'error', 'message' => 'Multiple students match that name. Please use your exact Student ID.']);
        }
        if (!$student) {
            $event = [
                'id'       => time() * 1000,
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
            Cache::put('kiosk_latest_scan_event', $event, 30);
            return response()->json($event);
        }

        if ($student->status === 'inactive') {
            $event = [
                'id'       => time() * 1000,
                'status'   => 'error',
                'action'   => 'inactive',
                'message'  => 'Student account is inactive.',
                'student'  => $this->formatStudent($student),
            ];
            Cache::put('kiosk_latest_scan_event', $event, 30);
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
                        'id'       => time() * 1000,
                        'status'   => 'success',
                        'action'   => $recentLog->action,
                        'message'  => $recentLog->action === 'check_out' ? 'Successfully checked out.' : 'Successfully checked in.',
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

                $message = $nextAction === 'check_out' ? 'Successfully checked out.' : 'Successfully checked in.';

                return [
                    'id'      => $log->id,
                    'status'  => 'success',
                    'action'  => $nextAction,
                    'message' => $message,
                    'student' => $this->formatStudent($student)
                ];
            });
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            $event = [
                'id'       => time() * 1000,
                'status'   => 'success',
                'action'   => 'check_in',
                'message'  => 'Successfully checked in.',
                'student'  => $this->formatStudent($student)
            ];
        }

        Cache::put('kiosk_latest_scan_event', $event, 30);

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

        $students = Student::with('academicDepartment')
            ->where(function($q) use ($searchTerm, $concatSql, $concatSqlRev) {
                $q->where('id', 'LIKE', $searchTerm)
                  ->orWhereRaw("{$concatSql} LIKE ?", [$searchTerm], 'or')
                  ->orWhereRaw("{$concatSqlRev} LIKE ?", [$searchTerm], 'or');
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
        // Use an atomic lock to prevent race conditions from concurrent requests
        $cooldownMinutes = (int) \App\Models\SystemSetting::get('checkin_cooldown_minutes', 5);
        $lockKey = 'checkin_lock:' . $student->id;

        $result = Cache::lock($lockKey, 10)->block(5, function () use ($student, $request, $cooldownMinutes) {
            $recentLog = AttendanceLog::query()->where('student_id', $student->id)
                ->where('logged_at', '>=', now()->subMinutes($cooldownMinutes))
                ->first();

            if ($recentLog) {
                // Return success so Kiosk still greets the student, but DO NOT save duplicate to DB
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

            return response()->json(['success' => true]);
        });

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
     */
    public function latestScan(Request $request): JsonResponse
    {
        $afterId = (int) $request->query('after_id', 0);

        // 1. Check for recent unregistered or error scan event
        $cachedEvent = Cache::get('kiosk_latest_scan_event');
        if ($cachedEvent && isset($cachedEvent['id']) && $cachedEvent['id'] > $afterId) {
            return response()->json($cachedEvent);
        }
        
        // 2. Check for latest valid attendance log
        $latestLog = AttendanceLog::query()
            ->where('id', '>', $afterId)
            ->with('student')
            ->orderBy('id', 'asc')
            ->first();

        if (!$latestLog || !$latestLog->student) {
            return response()->json(null);
        }

        return response()->json([
            'id' => $latestLog->id,
            'status' => 'success',
            'action' => $latestLog->action,
            'message' => 'Successfully checked in.',
            'student' => $this->formatStudent($latestLog->student)
        ]);
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
        
        /** @var \Illuminate\Database\Eloquent\Collection $students */
        $students = Student::query()->whereRaw("{$concatSql} LIKE ?", [$searchTerm], 'and')
            ->orWhereRaw("{$concatSqlRev} LIKE ?", [$searchTerm], 'or')
            ->orWhere('first_name', 'LIKE', $searchTerm)
            ->orWhere('last_name', 'LIKE', $searchTerm)
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
        $s->loadMissing('academicDepartment');
        return [
            'id'        => $s->id,
            'name'      => $s->full_name,
            'dept'      => $s->academicDepartment?->name ?? '—',
            'year'      => $s->year_level,
            'photo_url' => $s->photo_url,
        ];
    }
}
