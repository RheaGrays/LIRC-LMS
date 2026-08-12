<?php

namespace App\Services;

use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * AnalyticsService — available for use by AnalyticsController.
 *
 * CQ-A01 NOTE: This service is currently not wired into AnalyticsController
 * (the controller does its own data building inline). This service is kept
 * as a clean, testable alternative with the SQL-level optimisations applied.
 * To activate it, inject AnalyticsService in AnalyticsController and call
 * getAnalyticsData($period) instead of buildAnalyticsData().
 */
class AnalyticsService
{
    public function getAnalyticsData(string $period): array
    {
        return Cache::remember("analytics_service_{$period}", 300, function () use ($period) {
            return [
                'traffic'     => $this->getTrafficData($period),
                'departments' => $this->getDepartmentData($period),
            ];
        });
    }

    /**
     * PERF-A01 FIX: Push all groupBy/count operations into SQL.
     * Previously: loaded ALL matching rows into PHP with ->get(['id', 'logged_at'])
     *             then did ->groupBy() in a PHP Collection — O(n) memory.
     * Now: SELECT HOUR/DATE/MONTH(...), COUNT(*) GROUP BY — O(1) memory,
     *      DB engine does the work and returns only the summary rows.
     */
    private function getTrafficData(string $period): array
    {
        $now    = Carbon::now();
        $labels = [];
        $values = [];

        $base = AttendanceLog::query()->where('action', 'check_in');

        switch ($period) {
            case 'today':
                $hourly = (clone $base)
                    ->where('logged_at', '>=', $now->copy()->startOfDay())
                    ->selectRaw('HOUR(logged_at) as hour, COUNT(*) as cnt')
                    ->groupBy('hour')
                    ->pluck('cnt', 'hour');

                for ($i = 7; $i <= 19; $i++) {
                    $labels[] = Carbon::createFromTime($i, 0, 0)->format('g A');
                    $values[] = (int) ($hourly[$i] ?? 0);
                }
                break;

            case 'week':
                $startOfWeek = $now->copy()->startOfWeek();
                $byDate = (clone $base)
                    ->where('logged_at', '>=', $startOfWeek)
                    ->selectRaw('DATE(logged_at) as date, COUNT(*) as cnt')
                    ->groupBy('date')
                    ->pluck('cnt', 'date');

                for ($i = 0; $i < 7; $i++) {
                    $date    = $startOfWeek->copy()->addDays($i);
                    $labels[] = $date->format('D');
                    $values[] = (int) ($byDate[$date->format('Y-m-d')] ?? 0);
                }
                break;

            case 'month':
                $startOfMonth = $now->copy()->startOfMonth();
                $byDate = (clone $base)
                    ->where('logged_at', '>=', $startOfMonth)
                    ->selectRaw('DATE(logged_at) as date, COUNT(*) as cnt')
                    ->groupBy('date')
                    ->pluck('cnt', 'date');

                $daysInMonth = $now->daysInMonth;
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $date    = $startOfMonth->copy()->addDays($i - 1);
                    $labels[] = ($i % 3 == 0 || $i == 1 || $i == $daysInMonth) ? $i : '';
                    $values[] = (int) ($byDate[$date->format('Y-m-d')] ?? 0);
                }
                break;

            case 'year':
                $byMonth = (clone $base)
                    ->where('logged_at', '>=', $now->copy()->startOfYear())
                    ->selectRaw('MONTH(logged_at) as month, COUNT(*) as cnt')
                    ->groupBy('month')
                    ->pluck('cnt', 'month');

                for ($i = 1; $i <= 12; $i++) {
                    $labels[] = Carbon::createFromDate(null, $i, 1)->format('M');
                    $values[] = (int) ($byMonth[$i] ?? 0);
                }
                break;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function getDepartmentData(string $period): array
    {
        $now   = Carbon::now();
        $query = AttendanceLog::query()
            ->join('students', 'attendance_logs.student_id', '=', 'students.id')
            ->leftJoin('academic_departments', 'students.department_id', '=', 'academic_departments.id')
            ->where('attendance_logs.action', 'check_in');

        switch ($period) {
            case 'today':  $query->where('attendance_logs.logged_at', '>=', $now->copy()->startOfDay());   break;
            case 'week':   $query->where('attendance_logs.logged_at', '>=', $now->copy()->startOfWeek());  break;
            case 'month':  $query->where('attendance_logs.logged_at', '>=', $now->copy()->startOfMonth()); break;
            case 'year':   $query->where('attendance_logs.logged_at', '>=', $now->copy()->startOfYear());  break;
        }

        $results = $query
            ->selectRaw("COALESCE(academic_departments.name, 'Unknown') as department, COUNT(*) as aggregate")
            ->groupBy('department')
            ->orderByDesc('aggregate')
            ->get();

        return [
            'labels' => $results->pluck('department')->toArray(),
            'values' => $results->pluck('aggregate')->map(fn($v) => (int) $v)->toArray(),
        ];
    }
}
