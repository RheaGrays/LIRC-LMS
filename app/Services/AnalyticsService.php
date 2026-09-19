<?php

namespace App\Services;

use App\Models\AcademicTerm;
use App\Models\AttendanceLog;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * AnalyticsService — Single Source of Truth for Library Attendance Analytics.
 *
 * Database-agnostic (SQLite & MySQL compatible) with SQL-level aggregation,
 * intelligent multi-tier caching, and term/period filtering.
 */
class AnalyticsService
{
    /**
     * Get analytics data (traffic, departments, summary) with intelligent TTL caching.
     */
    public function getAnalyticsData(string $period = 'today', ?string $termId = null): array
    {
        $cacheKey = "analytics_data_{$period}_" . ($termId ?? 'none');

        // Real-time 60s cache for today; 300s (5 min) for historical periods or specific terms.
        $ttl = ($period === 'today' && !$termId) ? 60 : 300;

        return Cache::remember($cacheKey, $ttl, function () use ($period, $termId) {
            return $this->buildAnalyticsData($period, $termId);
        });
    }

    /**
     * Core aggregation logic using SQL GROUP BY instead of in-memory collections.
     */
    public function buildAnalyticsData(string $period, ?string $termId = null): array
    {
        $query = AttendanceLog::query()->where('action', 'check_in');

        // 1. Date Filtering
        if ($termId) {
            $term = AcademicTerm::find($termId, ['*']);
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

        $deptQuery = clone $query;
        $driver = DB::connection()->getDriverName();

        // 2. Traffic Series Calculation
        $trafficLabels = [];
        $trafficValues = [];

        if ($termId || $period === 'year') {
            $groupExpr = $driver === 'sqlite' ? "strftime('%Y-%m', logged_at)" : "DATE_FORMAT(logged_at, '%Y-%m')";
            $grouped = (clone $query)
                ->selectRaw("{$groupExpr} as period_key, COUNT(*) as cnt", [])
                ->groupBy('period_key')
                ->orderBy('period_key')
                ->pluck('cnt', 'period_key');

            foreach ($grouped as $yearMonth => $count) {
                $trafficLabels[] = Carbon::createFromFormat('Y-m', $yearMonth)->format('M Y');
                $trafficValues[] = (int) $count;
            }
        } elseif ($period === 'month' || $period === 'week') {
            $groupExpr = $driver === 'sqlite' ? "strftime('%Y-%m-%d', logged_at)" : "DATE(logged_at)";
            $grouped = (clone $query)
                ->selectRaw("{$groupExpr} as period_key, COUNT(*) as cnt", [])
                ->groupBy('period_key')
                ->orderBy('period_key')
                ->pluck('cnt', 'period_key');

            foreach ($grouped as $dateStr => $count) {
                $trafficLabels[] = Carbon::parse($dateStr)->format('M d (D)');
                $trafficValues[] = (int) $count;
            }
        } else {
            $groupExpr = $driver === 'sqlite' ? "CAST(strftime('%H', logged_at) AS INTEGER)" : "HOUR(logged_at)";
            $hourlyCounts = (clone $query)
                ->selectRaw("{$groupExpr} as period_key, COUNT(*) as cnt", [])
                ->groupBy('period_key')
                ->pluck('cnt', 'period_key');

            for ($h = 6; $h <= 22; $h++) {
                $label = $h < 12 ? "{$h}AM" : ($h === 12 ? "12PM" : ($h - 12) . "PM");
                $trafficLabels[] = $label;
                $trafficValues[] = (int) ($hourlyCounts[$h] ?? 0);
            }
        }

        // 3. Department Breakdown
        $deptData = $deptQuery->join('students', 'attendance_logs.student_id', '=', 'students.id', 'inner', false)
            ->leftJoin('academic_departments', 'students.department_id', '=', 'academic_departments.id', 'left', false)
            ->selectRaw("COALESCE(NULLIF(academic_departments.code, ''), academic_departments.name, 'Unknown') as department, COUNT(*) as aggregate", [])
            ->groupBy('department')
            ->orderByDesc('aggregate')
            ->get();

        $deptLabels = [];
        $deptValues = [];
        foreach ($deptData as $row) {
            $deptLabels[] = $row->department;
            $deptValues[] = (int) $row->aggregate;
        }

        // 4. Cached Global Summary Counters
        $totalPatrons = Cache::remember('analytics_summary_total_patrons', 300, function () {
            return Student::query()->count('*');
        });

        $todayTraffic = Cache::remember('analytics_summary_today_traffic', 60, function () {
            return AttendanceLog::query()
                ->where('action', 'check_in')
                ->whereDate('logged_at', now()->toDateString())
                ->count('*');
        });

        $monthTraffic = Cache::remember('analytics_summary_month_traffic', 300, function () {
            return AttendanceLog::query()
                ->where('action', 'check_in')
                ->whereMonth('logged_at', now()->month)
                ->whereYear('logged_at', now()->year)
                ->count('*');
        });

        $mostActiveDept = !empty($deptLabels) ? $deptLabels[0] : 'N/A';

        // 5. Top Patron for Filtered Scope
        $nameExpr = $driver === 'sqlite'
            ? "(students.first_name || ' ' || students.last_name)"
            : "CONCAT(students.first_name, ' ', students.last_name)";

        $topPatron = (clone $query)
            ->join('students', 'attendance_logs.student_id', '=', 'students.id', 'inner', false)
            ->selectRaw("{$nameExpr} as name, COUNT(*) as aggregate", [])
            ->groupBy('students.id', 'students.first_name', 'students.last_name')
            ->orderByDesc('aggregate')
            ->first();

        $topPatronName = $topPatron ? $topPatron->name : 'N/A';

        return [
            'summary' => [
                'total_patrons' => $totalPatrons,
                'today_traffic' => $todayTraffic,
                'month_traffic' => $monthTraffic,
                'active_dept'   => $mostActiveDept,
                'top_patron'    => $topPatronName,
            ],
            'traffic' => [
                'labels' => $trafficLabels,
                'values' => $trafficValues,
            ],
            'departments' => [
                'labels' => $deptLabels,
                'values' => $deptValues,
            ],
        ];
    }
}
