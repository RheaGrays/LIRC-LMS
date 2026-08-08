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
        Schema::create('academic_terms', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year');
            $table->string('semester');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('holidays')->default(0);
            $table->boolean('is_active')->default(false); // Only one should be true at a time
            $table->timestamps();
            
            $table->unique(['academic_year', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_terms');
    }
};
