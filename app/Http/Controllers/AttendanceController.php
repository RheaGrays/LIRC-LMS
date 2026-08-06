<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\AttendanceLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            return response()->json(['status' => 'error', 'message' => 'Student ID not found in the system.']);
        }

        if ($student->status === 'inactive') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Student account is inactive.',
                'student' => $this->formatStudent($student),
            ]);
        }

        // ── Cooldown Buffer (Prevents duplicate scans within 5 minutes) ──
        $cooldownMinutes = (int) \App\Models\SystemSetting::get('checkin_cooldown_minutes', 5);
        $recentLog = AttendanceLog::query()->where('student_id', $student->id)
            ->where('logged_at', '>=', now()->subMinutes($cooldownMinutes))
            ->orderByDesc('logged_at')
            ->first();

        if ($recentLog) {
            return response()->json([
                'status'    => 'success',
                'action'    => $recentLog->action,
                'message'   => 'Duplicate scan ignored (within 5-minute cooldown).',
                'student'   => $this->formatStudent($student)
            ]);
        }

        // Always log as check_in (as requested by librarian, no check-out)
        $nextAction = 'check_in';

        // Log action
        AttendanceLog::create([
            'student_id' => $student->id,
            'action'     => $nextAction,
            'logged_at'  => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'action'  => $nextAction,
            'message' => 'Successfully checked in.',
            'student' => $this->formatStudent($student)
        ]);
    }

    /**
     * Search students for Kiosk manual entry autocomplete
     */
    public function search(Request $request): JsonResponse
    {
        $term = trim($request->input('q', ''));
        if (strlen($term) < 2) return response()->json([]);

        $searchTerm = '%' . $term . '%';
        
        $students = Student::query()->where('status', 'active')
            ->where(function($query) use ($searchTerm, $term) {
                $query->where('id', 'LIKE', $searchTerm)
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$searchTerm])
                      ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", [$searchTerm]);
            })
            ->limit(5)
            ->get();

        return response()->json($students->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->first_name . ' ' . $s->last_name,
                'department' => $s->department,
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

    private function resolveStudent(string $term): Student|string|null
    {
        if (!$term) return null;

        // Try exact ID match first
        /** @var Student|null $student */
        $student = Student::query()->find($term);
        if ($student) return $student;

        // Try name search with proper parameter binding
        $searchTerm = '%' . trim($term) . '%';
        
        /** @var \Illuminate\Database\Eloquent\Collection $students */
        $students = Student::query()->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$searchTerm], 'and')
            ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", [$searchTerm], 'or')
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
        return [
            'id'        => $s->id,
            'name'      => $s->full_name,
            'dept'      => $s->department,
            'year'      => $s->year_level,
            'photo_url' => $s->photo_url,
        ];
    }
}
