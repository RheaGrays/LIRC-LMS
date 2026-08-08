<?php

namespace App\Services;

use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getAnalyticsData(string $period)
    {
        $traffic = $this->getTrafficData($period);
        $departments = $this->getDepartmentData($period);
        
        return [
            'traffic' => $traffic,
            'departments' => $departments
        ];
    }
    
    private function getTrafficData(string $period)
    {
        $query = AttendanceLog::where('action', 'check_in');
        
        $labels = [];
        $values = [];
        
        switch ($period) {
            case 'today':
                $query->whereDate('logged_at', Carbon::today());
                
                // Group by hour
                $results = $query->select(DB::raw('HOUR(logged_at) as hour'), DB::raw('count(*) as total'))
                    ->groupBy('hour')
                    ->orderBy('hour')
                    ->get()
                    ->keyBy('hour');
                
                // Fill 7 AM to 7 PM
                for ($i = 7; $i <= 19; $i++) {
                    $labels[] = Carbon::createFromTime($i, 0, 0)->format('g A');
                    $values[] = isset($results[$i]) ? $results[$i]->total : 0;
                }
                break;
                
            case 'week':
                $startOfWeek = Carbon::now()->startOfWeek();
                $query->where('logged_at', '>=', $startOfWeek);
                
                $results = $query->select(DB::raw('DATE(logged_at) as date'), DB::raw('count(*) as total'))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->keyBy('date');
                    
                for ($i = 0; $i < 7; $i++) {
                    $date = $startOfWeek->copy()->addDays($i);
                    $dateStr = $date->format('Y-m-d');
                    $labels[] = $date->format('D'); // Mon, Tue
                    $values[] = isset($results[$dateStr]) ? $results[$dateStr]->total : 0;
                }
                break;
                
            case 'month':
                $startOfMonth = Carbon::now()->startOfMonth();
                $query->where('logged_at', '>=', $startOfMonth);
                
                $results = $query->select(DB::raw('DATE(logged_at) as date'), DB::raw('count(*) as total'))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->keyBy('date');
                    
                $daysInMonth = Carbon::now()->daysInMonth;
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $date = $startOfMonth->copy()->addDays($i - 1);
                    $dateStr = $date->format('Y-m-d');
                    // Show label every 3 days to avoid crowding
                    $labels[] = ($i % 3 == 0 || $i == 1 || $i == $daysInMonth) ? $i : ''; 
                    $values[] = isset($results[$dateStr]) ? $results[$dateStr]->total : 0;
                }
                break;
                
            case 'year':
                $startOfYear = Carbon::now()->startOfYear();
                $query->where('logged_at', '>=', $startOfYear);
                
                $results = $query->select(DB::raw('MONTH(logged_at) as month'), DB::raw('count(*) as total'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->keyBy('month');
                    
                for ($i = 1; $i <= 12; $i++) {
                    $labels[] = Carbon::createFromDate(null, $i, 1)->format('M');
                    $values[] = isset($results[$i]) ? $results[$i]->total : 0;
                }
                break;
        }
        
        return ['labels' => $labels, 'values' => $values];
    }
    
    private function getDepartmentData(string $period)
    {
        $query = AttendanceLog::join('students', 'attendance_logs.student_id', '=', 'students.id')
            ->leftJoin('academic_departments', 'students.department_id', '=', 'academic_departments.id')
            ->where('attendance_logs.action', 'check_in');
            
        switch ($period) {
            case 'today': $query->whereDate('attendance_logs.logged_at', Carbon::today()); break;
            case 'week':  $query->where('attendance_logs.logged_at', '>=', Carbon::now()->startOfWeek()); break;
            case 'month': $query->where('attendance_logs.logged_at', '>=', Carbon::now()->startOfMonth()); break;
            case 'year':  $query->where('attendance_logs.logged_at', '>=', Carbon::now()->startOfYear()); break;
        }
        
        $results = $query->select(DB::raw("COALESCE(academic_departments.name, 'Unknown') as department"), DB::raw('count(*) as total'))
            ->groupBy('department')
            ->orderByDesc('total')
            ->get();
            
        $labels = [];
        $values = [];
        
        foreach ($results as $row) {
            $labels[] = $row->department;
            $values[] = $row->total;
        }
        
        return ['labels' => $labels, 'values' => $values];
    }
}
