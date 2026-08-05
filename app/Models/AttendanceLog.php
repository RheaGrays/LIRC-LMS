<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['student_id', 'action', 'logged_at'];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
    public function scopeToday($query)
    {
        return $query->where('logged_at', '>=', now()->startOfDay());
    }

    public function scopeCheckIns($query)
    {
        return $query->where('action', 'check_in');
    }

    public function scopeCheckOuts($query)
    {
        return $query->where('action', 'check_out');
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('logged_at', [$from, $to]);
    }
}
