<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\SystemSetting;

class OccupancyService
{
    /**
     * Get the number of unique students who checked in today
     * and the max capacity setting.
     */
    public static function today(): array
    {
        $today = now()->startOfDay();

        $visitors = AttendanceLog::where('action', 'check_in')
            ->where('logged_at', '>=', $today)
            ->distinct('student_id')
            ->count('student_id');

        $max = (int) SystemSetting::get('max_occupancy', 200);

        return ['inside' => $visitors, 'max' => $max];
    }

    /**
     * Get just the visitor count for today.
     */
    public static function todayCount(): int
    {
        return AttendanceLog::where('action', 'check_in')
            ->where('logged_at', '>=', now()->startOfDay())
            ->distinct('student_id')
            ->count('student_id');
    }
}
