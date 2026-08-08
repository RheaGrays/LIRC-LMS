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

        // We need to clone the query for department grouping, because traffic grouping alters the select.
        $deptQuery = clone $query;

        // Process Traffic Data (Line chart)
        $trafficLabels = [];
        $trafficValues = [];

        if ($termId || $period === 'year') {
            // Group by Month
            $trafficData = $query->selectRaw("DATE_FORMAT(logged_at, '%b %Y') as label, COUNT(*) as aggregate", [])
                ->groupBy('label')
                ->orderByRaw("MIN(logged_at)") // Ensure chronological order
                ->get();
        } elseif ($period === 'month' || $period === 'week') {
            // Group by Day
            $trafficData = $query->selectRaw("DATE_FORMAT(logged_at, '%b %d (%a)') as label, COUNT(*) as aggregate", [])
                ->groupBy('label')
                ->orderByRaw("MIN(logged_at)")
                ->get();
        } else {
            // Group by Hour (Today)
            // SQL HOUR() returns 0-23
            $trafficData = $query->selectRaw("HOUR(logged_at) as hour, COUNT(*) as aggregate", [])
                ->groupBy('hour')
                ->get()->keyBy('hour');
            
            for ($h = 6; $h <= 22; $h++) {
                $label = $h < 12 ? "{$h}AM" : ($h === 12 ? "12PM" : ($h - 12) . "PM");
                $trafficLabels[] = $label;
                $trafficValues[] = isset($trafficData[$h]) ? $trafficData[$h]->aggregate : 0;
            }
            $trafficData = null; // skip the foreach below
        }

        if (isset($trafficData)) {
            foreach ($trafficData as $row) {
                $trafficLabels[] = $row->label;
                $trafficValues[] = $row->aggregate;
            }
        }

        // Process Department Data (Doughnut chart)
        // Join students and academic_departments to group by department without loading all models
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
            $deptValues[] = $row->aggregate;
        }

        return response()->json([
            'traffic' => [
                'labels' => $trafficLabels,
                'values' => $trafficValues
            ],
            'departments' => [
                'labels' => $deptLabels,
                'values' => $deptValues
            ]
        ]);
    }
}
