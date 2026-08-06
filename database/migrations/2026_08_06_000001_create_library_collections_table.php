<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_collections', function (Blueprint $table) {
            $table->id();
            $table->string('badge');           // e.g. "Book Collection"
            $table->string('badge_color')->default('#c0392b'); // hex color for badge
            $table->string('title');           // e.g. "Print & Reference Archives"
            $table->text('description');       // short description shown on kiosk card
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default collections
        DB::table('library_collections')->insert([
            [
                'badge'       => 'Book Collection',
                'badge_color' => '#c0392b',
                'title'       => 'Print & Reference Archives',
                'description' => 'A curated collection of print, serial, and reference materials available for student use inside the LIRC.',
                'sort_order'  => 1,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'badge'       => 'Periodicals',
                'badge_color' => '#1a6b4a',
                'title'       => 'Journals & Magazines',
                'description' => 'Browse academic journals, magazines, and serials available for reading within the library premises.',
                'sort_order'  => 2,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'badge'       => 'Digital Resources',
                'badge_color' => '#1d4e8f',
                'title'       => 'E-Books & Online Databases',
                'description' => 'Access digital learning resources, e-books, and online databases through the LIRC student portal.',
                'sort_order'  => 3,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'badge'       => 'Special Collections',
                'badge_color' => '#7d3c98',
                'title'       => 'Filipiniana & Theses',
                'description' => 'Explore rare local publications, Filipiniana titles, and student thesis archives preserved in the LIRC.',
                'sort_order'  => 4,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('library_collections');
    }
};
