<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'last_name', 'first_name', 'middle_name',
        'department', 'year_level', 'email', 'contact',
        'photo_path', 'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class, 'student_id', 'id');
    }

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class, 'student_id', 'id');
    }

    // ── Accessors ─────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) return null;
        if (str_starts_with($this->photo_path, 'http')) return $this->photo_path;
        return asset('storage/' . $this->photo_path);
    }
}
