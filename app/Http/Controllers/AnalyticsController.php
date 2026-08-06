<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index()
    {
        $terms = \App\Models\AcademicTerm::orderBy('start_date', 'desc')->get();
        return view('admin.analytics.index', compact('terms'));
    }

    public function data(Request $request): JsonResponse
    {
        $period = $request->input('period', 'today');
        $termId = $request->input('term_id');
        
        $query = AttendanceLog::where('action', 'check_in');

        // If an Academic Term is selected, restrict the query to its date range
        if ($termId) {
            $term = \App\Models\AcademicTerm::find($termId);
            if ($term) {
                // When term is selected, we want to see the traffic across the entire term
                // Since term can span months, we'll just group by month or week
                $query->whereBetween('logged_at', [
                    $term->start_date->startOfDay(), 
                    $term->end_date->endOfDay()
                ]);
            }
        }

        // We fetch the data from the query and process it
        $logs = $query->get();

        // If no term is selected, we filter by the selected period
        if (!$termId) {
            $now = now();
            if ($period === 'today') {
                $logs = $logs->where('logged_at', '>=', $now->startOfDay());
            } elseif ($period === 'week') {
                $logs = $logs->where('logged_at', '>=', $now->copy()->startOfWeek());
            } elseif ($period === 'month') {
                $logs = $logs->where('logged_at', '>=', $now->copy()->startOfMonth());
            } elseif ($period === 'year') {
                $logs = $logs->where('logged_at', '>=', $now->copy()->startOfYear());
            }
        }

        // Process Traffic Data (Line chart)
        $trafficLabels = [];
        $trafficValues = [];

        if ($termId || $period === 'year') {
            // Group by Month
            $grouped = $logs->groupBy(fn($log) => $log->logged_at->format('M Y'));
            foreach ($grouped as $label => $group) {
                $trafficLabels[] = $label;
                $trafficValues[] = $group->count();
            }
        } elseif ($period === 'month' || $period === 'week') {
            // Group by Day
            $grouped = $logs->groupBy(fn($log) => $log->logged_at->format('M d (D)'));
            foreach ($grouped as $label => $group) {
                $trafficLabels[] = $label;
                $trafficValues[] = $group->count();
            }
        } else {
            // Group by Hour (Today)
            for ($h = 6; $h <= 22; $h++) {
                $label = $h < 12 ? "{$h}AM" : ($h === 12 ? "12PM" : ($h - 12) . "PM");
                $trafficLabels[] = $label;
                $trafficValues[] = $logs->filter(fn($l) => $l->logged_at->hour === $h)->count();
            }
        }

        // Process Department Data (Doughnut chart)
        // Ensure student relation is loaded
        $logs->load('student');
        
        $deptCounts = [];
        foreach ($logs as $log) {
            $dept = $log->student?->department ?? 'Unknown';
            if (!isset($deptCounts[$dept])) {
                $deptCounts[$dept] = 0;
            }
            $deptCounts[$dept]++;
        }
        
        // Sort descending
        arsort($deptCounts);
        
        return response()->json([
            'traffic' => [
                'labels' => $trafficLabels,
                'values' => $trafficValues
            ],
            'departments' => [
                'labels' => array_keys($deptCounts),
                'values' => array_values($deptCounts)
            ]
        ]);
    }
}
