<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OccupancyService
{
    /**
     * Get the number of unique students who checked in today
     * and the max capacity setting.
     * Uses optimized DB index query cached for 15s.
     */
    public static function today(): array
    {
        return Cache::remember('occupancy_today', 15, function () {
            $today = now()->startOfDay();

            // Uses compound index idx_attendance_occupancy (action, logged_at, student_id)
            $visitors = (int) DB::table('attendance_logs')
                ->where('action', 'check_in')
                ->where('logged_at', '>=', $today)
                ->whereNull('deleted_at')
                ->count(DB::raw('DISTINCT student_id'));

            $max = (int) SystemSetting::get('max_occupancy', 200);

            return ['inside' => $visitors, 'max' => $max];
        });
    }

    /**
     * Get just the visitor count for today.
     * Uses optimized DB index query cached for 15s.
     */
    public static function todayCount(): int
    {
        return Cache::remember('occupancy_today_count', 15, function () {
            return (int) DB::table('attendance_logs')
                ->where('action', 'check_in')
                ->where('logged_at', '>=', now()->startOfDay())
                ->whereNull('deleted_at')
                ->count(DB::raw('DISTINCT student_id'));
        });
    }
}
