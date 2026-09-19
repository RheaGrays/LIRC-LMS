<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PERF-06 FIX: Add indexes to students table for high-frequency name queries and lookups.
     * last_name and first_name are used in all student searches, kiosk name resolution,
     * and default alphabetical sorting across the system.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->index(['last_name', 'first_name'], 'idx_students_name');
            $table->index('first_name', 'idx_students_first_name');
            $table->index('email', 'idx_students_email');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_name');
            $table->dropIndex('idx_students_first_name');
            $table->dropIndex('idx_students_email');
        });
    }
};
