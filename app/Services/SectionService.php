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
            $logs = SectionLog::whereDate('created_at', $today)
                ->orderBy('created_at', 'desc')
                ->get()
                ->unique('section_name')
                ->values();
                
            return $logs;
        });
    }

    /**
     * Record or update a section headcount.
     */
    public function upsertLog(string $sectionName, int $headcount, $adminId = null)
    {
        $today = Carbon::today();
        
        // Find if there's already a log for this section today that we can just update
        // Alternatively, if you want history, always create a new record.
        // Let's create a new record if the last one was over 5 minutes ago, otherwise update.
        
        $lastLog = SectionLog::where('section_name', $sectionName)
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'desc')
            ->first();
            
        if ($lastLog && $lastLog->created_at->diffInMinutes(Carbon::now()) < 5) {
            $lastLog->update([
                'headcount' => $headcount,
                'admin_id' => $adminId ?? $lastLog->admin_id
            ]);
            $log = $lastLog;
        } else {
            $log = SectionLog::create([
                'section_name' => $sectionName,
                'headcount' => $headcount,
                'admin_id' => $adminId
            ]);
        }
        
        Cache::forget('latest_section_logs');
        
        return $log;
    }
}
