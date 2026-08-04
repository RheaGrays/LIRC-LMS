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
        $id = trim($request->input('student_id', ''));
        if (!$id) {
            return response()->json(['error' => 'No student ID provided.'], 422);
        }

        $student = Student::find($id);
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
     * Log an attendance action (check_in or check_out).
     */
    public function log(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => ['required', 'string', 'exists:students,id'],
            'action'     => ['required', 'in:check_in,check_out'],
        ]);

        AttendanceLog::create([
            'student_id' => $request->student_id,
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
        $studentId = trim($request->input('student_id', ''));
        $today     = now()->startOfDay();

        $log = AttendanceLog::where('student_id', $studentId)
            ->where('logged_at', '>=', $today)
            ->orderByDesc('logged_at')
            ->first();

        return response()->json(['action' => $log?->action]);
    }

    /**
     * Get current library occupancy.
     */
    public function occupancy(): JsonResponse
    {
        $maxCapacity = (int) \App\Models\SystemSetting::get('max_occupancy', 200);
        $today       = now()->startOfDay();

        $logs = AttendanceLog::select('student_id', 'action')
            ->where('logged_at', '>=', $today)
            ->orderByDesc('logged_at')
            ->get();

        $seen   = [];
        $inside = 0;
        foreach ($logs as $log) {
            if (!isset($seen[$log->student_id])) {
                $seen[$log->student_id] = true;
                if ($log->action === 'check_in') $inside++;
            }
        }

        return response()->json(['inside' => $inside, 'max' => $maxCapacity]);
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
