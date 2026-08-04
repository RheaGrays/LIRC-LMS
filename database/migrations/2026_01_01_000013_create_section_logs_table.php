<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_logs', function (Blueprint $table) {
            $table->id();
            $table->string('section_code');
            $table->string('section_name');
            $table->date('date');
            $table->tinyInteger('hour'); // 0–23
            $table->integer('occupied')->default(0);
            $table->integer('reserved')->default(0);
            $table->integer('available')->default(0);
            $table->integer('total_capacity')->default(0);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['section_code', 'date', 'hour']);
            $table->index(['section_code', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_logs');
    }
};
