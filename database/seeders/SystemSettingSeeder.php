<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'active_term'           => '2025-2026-2',
            'idle_timeout'          => 60,
            'max_occupancy'         => 200,
            'show_occupancy'        => true,
            'enable_webcam'         => false,
            'sound_on_checkin'      => false,
            'alert_capacity'        => true,
            'alert_daily_summary'   => false,
            'alert_repeated_denied' => true,
            'patron_categories'     => ['Student', 'Employee', 'Post Graduate', 'Alumni', 'Visitor'],
        ];

        foreach ($defaults as $key => $value) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value'      => json_encode($value),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
