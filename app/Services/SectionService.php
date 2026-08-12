<?php

namespace App\Services;

use App\Models\SectionLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class SectionService
{
    /**
     * Get the latest section logs for today.
     * Uses cache to avoid excessive DB hits since Kiosk might poll this.
     */
    public function getLatestLogs()
    {
        return Cache::remember('latest_section_logs', 60, function () {
            $today = Carbon::today();
            
            // Get the most recent log per section for today
            $latestIds = SectionLog::query()->whereDate('date', '=', $today, 'and')
                ->selectRaw('MAX(id) as id', [])
                ->groupBy('section_code')
                ->pluck('id');

            return SectionLog::query()->whereIn('id', $latestIds, 'and', false)
                ->orderBy('section_code')
                ->get();
        });
    }

    /**
     * Record or update a section snapshot.
     */
    public function upsertLog(array $section): void
    {
        SectionLog::upsertSnapshot($section);
        Cache::forget('latest_section_logs');
    }
}
