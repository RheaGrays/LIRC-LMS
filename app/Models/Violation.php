<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Violation extends Model
{
    use HasUuids;

    protected $fillable = ['student_id', 'type', 'notes', 'severity', 'date'];

    protected $casts = [
        'date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
    public function scopeSeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }
}
