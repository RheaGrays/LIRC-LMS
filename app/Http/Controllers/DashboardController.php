<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $admin   = Auth::guard('admin')->user();
        $today   = now()->startOfDay();

        // Today's total entries
        $todayEntries = AttendanceLog::where('action', 'check_in')
            ->where('logged_at', '>=', $today)
            ->count();

        // Total active students
        $totalStudents = Student::where('status', 'active')->count();

        // Current occupancy
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

        $maxOccupancy = (int) \App\Models\SystemSetting::get('max_occupancy', 200);

        // Recent activity feed (last 50)
        $recentLogs = AttendanceLog::with('student')
            ->where('logged_at', '>=', $today)
            ->orderByDesc('logged_at')
            ->limit(50)
            ->get()
            ->map(fn($log) => [
                'id'           => $log->id,
                'student_id'   => $log->student_id,
                'name'         => $log->student?->full_name ?? $log->student_id,
                'dept'         => $log->student?->department ?? '—',
                'year'         => $log->student?->year_level ?? '—',
                'photo_url'    => $log->student?->photo_url,
                'status'       => $log->action === 'check_in' ? 'entered' : 'exited',
                'logged_at'    => $log->logged_at->toIso8601String(),
                'time_label'   => $log->logged_at->format('h:i A'),
            ]);

        return view('admin.dashboard.index', compact(
            'admin', 'todayEntries', 'totalStudents', 'inside', 'maxOccupancy', 'recentLogs'
        ));
    }
}
