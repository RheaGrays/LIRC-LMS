<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('academic_terms', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->string('academic_year')->after('id');
            $table->string('semester')->after('academic_year');
            $table->integer('holidays')->default(0)->after('end_date');
            $table->enum('status', ['Active', 'Archived'])->default('Archived')->after('holidays');
        });
    }

    public function down(): void
    {
        Schema::table('academic_terms', function (Blueprint $table) {
            $table->dropColumn(['academic_year', 'semester', 'holidays', 'status']);
            $table->string('name')->unique();
        });
    }
};
