<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryCollection extends Model
{
    protected $fillable = [
        'badge',
        'badge_color',
        'title',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Return only active collections, ordered by sort_order.
     */
    public static function active()
    {
        return static::query()->where('is_active', true)
                     ->orderBy('sort_order')
                     ->get();
    }
}
