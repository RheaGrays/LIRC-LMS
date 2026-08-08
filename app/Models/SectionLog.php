<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'section_code', 'section_name', 'date', 'hour',
        'occupied', 'reserved', 'available', 'total_capacity', 'updated_at',
    ];

    protected $casts = [
        'date'     => 'date',
        'hour'     => 'integer',
        'occupied' => 'integer',
        'reserved' => 'integer',
        'available' => 'integer',
        'total_capacity' => 'integer',
    ];

    /**
     * Upsert a section snapshot for the current hour.
     */
    public static function upsertSnapshot(array $section): void
    {
        $now   = now();
        $date  = $now->toDateString();
        $hour  = $now->hour;

        static::updateOrCreate(
            ['section_code' => $section['id'], 'date' => $date, 'hour' => $hour],
            [
                'section_name'   => $section['name'],
                'occupied'       => $section['occupied'],
                'reserved'       => $section['reserved'],
                'available'      => max(0, $section['total'] - $section['occupied'] - $section['reserved']),
                'total_capacity' => $section['total'],
                'updated_at'     => $now,
            ]
        );
    }
}
