<?php

namespace App\Services;

use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    public function getAnalyticsData(string $period)
    {
        return Cache::remember("analytics_service_{$period}", 300, function () use ($period) {
            $traffic = $this->getTrafficData($period);
            $departments = $this->getDepartmentData($period);
            
            return [
                'traffic' => $traffic,
                'departments' => $departments
            ];
        });
    }
    
    private function getTrafficData(string $period)
    {
        $query = AttendanceLog::where('action', 'check_in');
        
        $now = Carbon::now();
        switch ($period) {
            case 'today':
                $query->where('logged_at', '>=', $now->copy()->startOfDay());
                break;
            case 'week':
                $query->where('logged_at', '>=', $now->copy()->startOfWeek());
                break;
            case 'month':
                $query->where('logged_at', '>=', $now->copy()->startOfMonth());
                break;
            case 'year':
                $query->where('logged_at', '>=', $now->copy()->startOfYear());
                break;
        }

        $logs = $query->get(['id', 'logged_at']);
        
        $labels = [];
        $values = [];
        
        switch ($period) {
            case 'today':
                $hourly = $logs->groupBy(fn($l) => (int) Carbon::parse($l->logged_at)->format('G'));
                for ($i = 7; $i <= 19; $i++) {
                    $labels[] = Carbon::createFromTime($i, 0, 0)->format('g A');
                    $values[] = isset($hourly[$i]) ? $hourly[$i]->count() : 0;
                }
                break;
                
            case 'week':
                $startOfWeek = $now->copy()->startOfWeek();
                $byDate = $logs->groupBy(fn($l) => Carbon::parse($l->logged_at)->format('Y-m-d'));
                for ($i = 0; $i < 7; $i++) {
                    $date = $startOfWeek->copy()->addDays($i);
                    $dateStr = $date->format('Y-m-d');
                    $labels[] = $date->format('D');
                    $values[] = isset($byDate[$dateStr]) ? $byDate[$dateStr]->count() : 0;
                }
                break;
                
            case 'month':
                $startOfMonth = $now->copy()->startOfMonth();
                $byDate = $logs->groupBy(fn($l) => Carbon::parse($l->logged_at)->format('Y-m-d'));
                $daysInMonth = $now->daysInMonth;
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $date = $startOfMonth->copy()->addDays($i - 1);
                    $dateStr = $date->format('Y-m-d');
                    $labels[] = ($i % 3 == 0 || $i == 1 || $i == $daysInMonth) ? $i : ''; 
                    $values[] = isset($byDate[$dateStr]) ? $byDate[$dateStr]->count() : 0;
                }
                break;
                
            case 'year':
                $byMonth = $logs->groupBy(fn($l) => (int) Carbon::parse($l->logged_at)->format('n'));
                for ($i = 1; $i <= 12; $i++) {
                    $labels[] = Carbon::createFromDate(null, $i, 1)->format('M');
                    $values[] = isset($byMonth[$i]) ? $byMonth[$i]->count() : 0;
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
            
        $now = Carbon::now();
        switch ($period) {
            case 'today': $query->where('attendance_logs.logged_at', '>=', $now->copy()->startOfDay()); break;
            case 'week':  $query->where('attendance_logs.logged_at', '>=', $now->copy()->startOfWeek()); break;
            case 'month': $query->where('attendance_logs.logged_at', '>=', $now->copy()->startOfMonth()); break;
            case 'year':  $query->where('attendance_logs.logged_at', '>=', $now->copy()->startOfYear()); break;
        }
        
        $results = $query->selectRaw("COALESCE(academic_departments.name, 'Unknown') as department, COUNT(*) as aggregate")
            ->groupBy('department')
            ->orderByDesc('aggregate')
            ->get();
            
        $labels = [];
        $values = [];
        
        foreach ($results as $row) {
            $labels[] = $row->department;
            $values[] = (int) $row->aggregate;
        }
        
        return ['labels' => $labels, 'values' => $values];
    }
}
