<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->string('id')->primary(); // e.g. "2024-00001"
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            
            $table->unsignedBigInteger('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('academic_departments')->nullOnDelete();
            
            $table->unsignedBigInteger('program_id')->nullable();
            $table->foreign('program_id')->references('id')->on('academic_programs')->nullOnDelete();
            
            $table->string('year_level')->nullable();
            $table->string('email')->nullable();
            $table->string('patron_category')->default('Student');
            $table->string('contact')->nullable();
            $table->string('photo_path')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
