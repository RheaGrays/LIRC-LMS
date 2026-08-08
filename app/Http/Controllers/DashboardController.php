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
        $todayEntries = AttendanceLog::query()
            ->where('action', 'check_in')
            ->where('logged_at', '>=', $today)
            ->count();

        // Total active students
        $totalStudents = Student::query()->where('status', 'active')->count();

        // Occupancy (unique visitors today)
        $occupancy = \App\Services\OccupancyService::today();
        $inside = $occupancy['inside'];
        $maxOccupancy = $occupancy['max'];

        // Recent activity feed (latest activity per student today)
        $latestLogIds = AttendanceLog::query()
            ->selectRaw('MAX(id) as id', [])
            ->where('logged_at', '>=', $today)
            ->groupBy('student_id')
            ->pluck('id');

        $recentLogs = AttendanceLog::query()
            ->with('student')
            ->whereIn('id', $latestLogIds, 'and', false)
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
