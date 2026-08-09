<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    public function index()
    {
        $terms = Cache::remember('academic_terms_all', 600, function () {
            return \App\Models\AcademicTerm::orderBy('start_date', 'desc')->get();
        });
        return view('admin.analytics.index', compact('terms'));
    }

    public function data(Request $request): JsonResponse
    {
        $period = $request->input('period', 'today');
        $termId = $request->input('term_id');

        $cacheKey = "analytics_data_{$period}_" . ($termId ?? 'none');

        // Cache analytics data for 5 seconds for instant real-time chart updates
        $data = Cache::remember($cacheKey, 5, function () use ($period, $termId) {
            return $this->buildAnalyticsData($period, $termId);
        });

        return response()->json($data);
    }

    /**
     * Fix #9: Database-agnostic analytics query logic.
     * Uses Carbon & Collections instead of MySQL-specific HOUR() and DATE_FORMAT() functions.
     */
    private function buildAnalyticsData(string $period, ?string $termId): array
    {
        $query = AttendanceLog::query()->where('action', 'check_in');

        // Apply Date Filters
        if ($termId) {
            $term = \App\Models\AcademicTerm::find($termId, ['*']);
            if ($term) {
                $query->whereBetween('logged_at', [
                    $term->start_date->startOfDay(), 
                    $term->end_date->endOfDay()
                ]);
            }
        } else {
            $now = now();
            if ($period === 'today') {
                $query->where('logged_at', '>=', $now->copy()->startOfDay());
            } elseif ($period === 'week') {
                $query->where('logged_at', '>=', $now->copy()->startOfWeek());
            } elseif ($period === 'month') {
                $query->where('logged_at', '>=', $now->copy()->startOfMonth());
            } elseif ($period === 'year') {
                $query->where('logged_at', '>=', $now->copy()->startOfYear());
            }
        }

        // We clone query for department aggregation
        $deptQuery = clone $query;

        // Fetch timestamps for database-agnostic traffic processing
        $logs = $query->get(['id', 'student_id', 'logged_at']);

        $trafficLabels = [];
        $trafficValues = [];

        if ($termId || $period === 'year') {
            // Group by Month (e.g. "Jan 2026")
            $grouped = $logs->groupBy(function ($log) {
                return Carbon::parse($log->logged_at)->format('Y-m');
            })->sortKeys();

            foreach ($grouped as $yearMonth => $group) {
                $trafficLabels[] = Carbon::createFromFormat('Y-m', $yearMonth)->format('M Y');
                $trafficValues[] = $group->count();
            }
        } elseif ($period === 'month' || $period === 'week') {
            // Group by Day (e.g. "Jan 15 (Thu)")
            $grouped = $logs->groupBy(function ($log) {
                return Carbon::parse($log->logged_at)->format('Y-m-d');
            })->sortKeys();

            foreach ($grouped as $dateStr => $group) {
                $trafficLabels[] = Carbon::parse($dateStr)->format('M d (D)');
                $trafficValues[] = $group->count();
            }
        } else {
            // Group by Hour (Today) — 6 AM to 10 PM
            $hourlyCounts = $logs->groupBy(function ($log) {
                return (int) Carbon::parse($log->logged_at)->format('G');
            });

            for ($h = 6; $h <= 22; $h++) {
                $label = $h < 12 ? "{$h}AM" : ($h === 12 ? "12PM" : ($h - 12) . "PM");
                $trafficLabels[] = $label;
                $trafficValues[] = isset($hourlyCounts[$h]) ? $hourlyCounts[$h]->count() : 0;
            }
        }

        // Department Data (Doughnut chart)
        $deptData = $deptQuery->join('students', 'attendance_logs.student_id', '=', 'students.id')
            ->leftJoin('academic_departments', 'students.department_id', '=', 'academic_departments.id')
            ->selectRaw("COALESCE(academic_departments.name, 'Unknown') as department, COUNT(*) as aggregate", [])
            ->groupBy('department')
            ->orderByDesc('aggregate')
            ->get();

        $deptLabels = [];
        $deptValues = [];
        foreach ($deptData as $row) {
            $deptLabels[] = $row->department;
            $deptValues[] = (int) $row->aggregate;
        }

        return [
            'traffic' => [
                'labels' => $trafficLabels,
                'values' => $trafficValues
            ],
            'departments' => [
                'labels' => $deptLabels,
                'values' => $deptValues
            ]
        ];
    }
}
