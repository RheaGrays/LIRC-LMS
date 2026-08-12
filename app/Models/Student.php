<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'last_name', 'first_name', 'middle_name',
        'department_id', 'program_id', 'year_level', 'email', 'contact',
        'patron_category', 'photo_path', 'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class, 'student_id', 'id');
    }

    public function latestAttendance()
    {
        return $this->hasOne(AttendanceLog::class, 'student_id', 'id')->latestOfMany();
    }

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class, 'student_id', 'id');
    }

    public function academicDepartment(): BelongsTo
    {
        return $this->belongsTo(AcademicDepartment::class, 'department_id', 'id');
    }

    public function academicProgram(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id', 'id');
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
        
        $relative = ltrim($this->photo_path, '/');

        // Single file_exists() check — no glob() wildcard search.
        // Previously, a glob() fallback searched the directory for files matching
        // the student ID prefix, causing O(n) blocking disk I/O when loading lists.
        if (file_exists(public_path('storage/' . $relative)) || file_exists(storage_path('app/public/' . $relative))) {
            return asset('storage/' . $relative);
        }

        return null;
    }
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeDepartment($query, $dept)
    {
        return $query->where('department_id', $dept);
    }

    public function scopePatronCategory($query, $category)
    {
        return $query->where('patron_category', $category);
    }

    public function scopeSearch($query, $term)
    {
        // BUG-A04 FIX: Replace orWhereHas() correlated subquery with a LEFT JOIN.
        // orWhereHas() generates an EXISTS subquery per row — O(n) at scale.
        // A single LEFT JOIN lets MySQL use indexes and scan once.
        return $query
            ->leftJoin('academic_departments', 'students.department_id', '=', 'academic_departments.id')
            ->where(function ($q) use ($term) {
                $q->where('students.id',         'LIKE', "%{$term}%")
                  ->orWhere('students.last_name',  'LIKE', "%{$term}%")
                  ->orWhere('students.first_name',  'LIKE', "%{$term}%")
                  ->orWhere('academic_departments.name', 'LIKE', "%{$term}%");
            })
            ->select('students.*'); // prevent JOIN columns from shadowing student columns
    }
}
