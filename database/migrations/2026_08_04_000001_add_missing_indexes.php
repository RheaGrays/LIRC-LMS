<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Students table indexes
        Schema::table('students', function (Blueprint $table) {
            $table->index('status');
            $table->index('department_id');
            $table->index('patron_category');
            $table->index(['department_id', 'status']);
        });

        // Violations table indexes
        Schema::table('violations', function (Blueprint $table) {
            $table->index('date');
            $table->index('severity');
            $table->index(['student_id', 'date']);
        });

        // Admins table indexes
        Schema::table('admins', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('role');
        });

        // Attendance logs — index on action for filtered counts
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['department_id']);
            $table->dropIndex(['department_id', 'status']);
        });

        Schema::table('violations', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['severity']);
            $table->dropIndex(['student_id', 'date']);
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['role']);
        });

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropIndex(['action']);
        });
    }
};
