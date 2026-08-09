<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $admin   = Auth::guard('admin')->user();
        $today   = now()->startOfDay();

        $todayEntries = Cache::remember('dashboard_today_entries', 30, function () use ($today) {
            return AttendanceLog::query()
                ->where('action', 'check_in')
                ->where('logged_at', '>=', $today)
                ->count();
        });

        $totalStudents = Cache::remember('dashboard_total_active_students', 300, function () {
            return Student::query()->where('status', 'active')->count();
        });

        $occupancy = \App\Services\OccupancyService::today();
        $inside = $occupancy['inside'];
        $maxOccupancy = $occupancy['max'];

        $recentLogs = AttendanceLog::query()
            ->with(['student.academicDepartment'])
            ->where('logged_at', '>=', $today)
            ->orderByDesc('logged_at')
            ->limit(50)
            ->get()
            ->map(fn($log) => [
                'id'           => $log->id,
                'student_id'   => $log->student_id,
                'name'         => $log->student?->full_name ?? $log->student_id,
                'dept'         => $log->student?->academicDepartment?->name ?? '—',
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

    public function stats()
    {
        $today = now()->startOfDay();

        $todayEntries = Cache::remember('dashboard_today_entries', 30, function () use ($today) {
            return AttendanceLog::query()
                ->where('action', 'check_in')
                ->where('logged_at', '>=', $today)
                ->count();
        });

        $totalStudents = Cache::remember('dashboard_total_active_students', 300, function () {
            return Student::query()->where('status', 'active')->count();
        });

        $occupancy = \App\Services\OccupancyService::today();

        $recentLogs = AttendanceLog::query()
            ->with(['student.academicDepartment'])
            ->where('logged_at', '>=', $today)
            ->orderByDesc('logged_at')
            ->limit(50)
            ->get()
            ->map(fn($log) => [
                'id'           => $log->id,
                'student_id'   => $log->student_id,
                'name'         => $log->student?->full_name ?? $log->student_id,
                'dept'         => $log->student?->academicDepartment?->name ?? '—',
                'year'         => $log->student?->year_level ?? '—',
                'photo_url'    => $log->student?->photo_url,
                'status'       => $log->action === 'check_in' ? 'entered' : 'exited',
                'logged_at'    => $log->logged_at->toIso8601String(),
                'time_label'   => $log->logged_at->format('h:i A'),
            ]);

        return response()->json([
            'todayEntries'  => $todayEntries,
            'totalStudents' => $totalStudents,
            'inside'        => $occupancy['inside'],
            'maxOccupancy'  => $occupancy['max'],
            'recentLogs'    => $recentLogs,
        ]);
    }
}
