<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OccupancyService
{
    /**
     * Get the number of students CURRENTLY INSIDE the library (net occupancy)
     * and the max capacity setting.
     *
     * BUG-NEW-05 FIX: The previous implementation counted ALL distinct students
     * who checked in today — meaning a student who checked out was still counted
     * as "inside". This is wrong in toggle mode.
     *
     * Correct logic: a student is "inside" if their LAST attendance action today
     * is check_in (i.e. they checked in but have not yet checked out).
     *
     * Uses a subquery that finds the latest log per student today, then counts
     * those whose latest action is check_in.
     *
     * Cached for 15 seconds.
     */
    public static function today(): array
    {
        return Cache::remember('occupancy_today', 15, function () {
            $today = now()->startOfDay()->toDateTimeString();

            // Subquery: for each student, get their latest log_at today
            // Then count those whose last action was check_in
            $inside = (int) DB::table('attendance_logs as a')
                ->join(
                    DB::raw('(
                        SELECT student_id, MAX(logged_at) as last_log
                        FROM attendance_logs
                        WHERE logged_at >= \'' . $today . '\'
                        AND deleted_at IS NULL
                        GROUP BY student_id
                    ) as latest'),
                    function ($join) {
                        $join->on('a.student_id', '=', 'latest.student_id')
                             ->on('a.logged_at',  '=', 'latest.last_log');
                    }
                )
                ->where('a.action', 'check_in')
                ->whereNull('a.deleted_at')
                ->count();

            $max = (int) SystemSetting::get('max_occupancy', 200);

            return ['inside' => $inside, 'max' => $max];
        });
    }

    /**
     * Get just the current net occupancy count (students inside right now).
     * Reuses the today() cache to avoid a second identical DB query.
     */
    public static function todayCount(): int
    {
        // PERF-NEW-02 FIX: reuse today() cache instead of running a duplicate query
        return self::today()['inside'];
    }
}
