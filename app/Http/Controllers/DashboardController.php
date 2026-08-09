<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    // ── PERF-04 FIX: Extracted shared data-building logic into one private method.
    // Both index() (page load) and stats() (AJAX polling) now call buildDashboardData()
    // instead of duplicating the same 40 lines of queries.
    private function buildDashboardData(): array
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
                'id'         => $log->id,
                'student_id' => $log->student_id,
                'name'       => $log->student?->full_name ?? $log->student_id,
                'dept'       => $log->student?->academicDepartment?->name ?? '—',
                'year'       => $log->student?->year_level ?? '—',
                'photo_url'  => $log->student?->photo_url,
                'status'     => $log->action === 'check_in' ? 'entered' : 'exited',
                'logged_at'  => $log->logged_at->toIso8601String(),
                'time_label' => $log->logged_at->format('h:i A'),
            ]);

        return [
            'todayEntries'  => $todayEntries,
            'totalStudents' => $totalStudents,
            'inside'        => $occupancy['inside'],
            'maxOccupancy'  => $occupancy['max'],
            'recentLogs'    => $recentLogs,
        ];
    }

    public function index()
    {
        $admin = Auth::guard('admin')->user();
        $data  = $this->buildDashboardData();

        return view('admin.dashboard.index', [
            'admin'         => $admin,
            'todayEntries'  => $data['todayEntries'],
            'totalStudents' => $data['totalStudents'],
            'inside'        => $data['inside'],
            'maxOccupancy'  => $data['maxOccupancy'],
            'recentLogs'    => $data['recentLogs'],
        ]);
    }

    public function stats()
    {
        return response()->json($this->buildDashboardData());
    }
}
