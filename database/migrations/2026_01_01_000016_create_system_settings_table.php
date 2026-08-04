<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->json('value');
            $table->timestamps();
        });

        // Seed default settings
        DB::table('system_settings')->insert([
            ['key' => 'active_term',           'value' => json_encode('2025-2026-2'),   'created_at' => now(), 'updated_at' => now()],
            ['key' => 'idle_timeout',           'value' => json_encode(60),              'created_at' => now(), 'updated_at' => now()],
            ['key' => 'max_occupancy',          'value' => json_encode(200),             'created_at' => now(), 'updated_at' => now()],
            ['key' => 'show_occupancy',         'value' => json_encode(true),            'created_at' => now(), 'updated_at' => now()],
            ['key' => 'enable_webcam',          'value' => json_encode(false),           'created_at' => now(), 'updated_at' => now()],
            ['key' => 'sound_on_checkin',       'value' => json_encode(false),           'created_at' => now(), 'updated_at' => now()],
            ['key' => 'alert_capacity',         'value' => json_encode(true),            'created_at' => now(), 'updated_at' => now()],
            ['key' => 'alert_daily_summary',    'value' => json_encode(false),           'created_at' => now(), 'updated_at' => now()],
            ['key' => 'alert_repeated_denied',  'value' => json_encode(true),            'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
