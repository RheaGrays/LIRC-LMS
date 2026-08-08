<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicTerm extends Model
{
    protected $fillable = [
        'academic_year',
        'semester',
        'start_date',
        'end_date',
        'holidays',
        'is_active',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'holidays' => 'integer',
    ];

    public function getNameAttribute()
    {
        return "{$this->semester} AY {$this->academic_year}";
    }
}
