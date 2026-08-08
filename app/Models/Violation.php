<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Violation extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['student_id', 'violation_type_id', 'notes', 'severity', 'date'];

    protected $casts = [
        'date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function violationType(): BelongsTo
    {
        return $this->belongsTo(ViolationType::class, 'violation_type_id', 'id');
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
