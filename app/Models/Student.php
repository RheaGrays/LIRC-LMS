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

    public function getViolationsCountAttribute(): int
    {
        if (array_key_exists('violations_count', $this->attributes)) {
            return (int) $this->attributes['violations_count'];
        }
        if ($this->relationLoaded('violations')) {
            return $this->violations->count();
        }
        return $this->violations()->count();
    }

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
        $term = trim($term);
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $fullNameExpr = $driver === 'sqlite' 
            ? "students.first_name || ' ' || COALESCE(students.middle_name, '') || ' ' || students.last_name"
            : "CONCAT(students.first_name, ' ', COALESCE(students.middle_name, ''), ' ', students.last_name)";

        $reversedNameExpr = $driver === 'sqlite'
            ? "students.last_name || ' ' || students.first_name"
            : "CONCAT(students.last_name, ' ', students.first_name)";

        return $query
            ->leftJoin('academic_departments', 'students.department_id', '=', 'academic_departments.id')
            ->leftJoin('academic_programs',    'students.program_id',    '=', 'academic_programs.id')
            ->where(function ($q) use ($term, $fullNameExpr, $reversedNameExpr) {
                $q->where('students.id',               'LIKE', "%{$term}%")
                  ->orWhere('students.last_name',       'LIKE', "%{$term}%")
                  ->orWhere('students.first_name',      'LIKE', "%{$term}%")
                  ->orWhere('students.middle_name',     'LIKE', "%{$term}%")
                  ->orWhere('students.patron_category', 'LIKE', "%{$term}%")
                  ->orWhere('students.year_level',      'LIKE', "%{$term}%")
                  ->orWhere('academic_departments.name','LIKE', "%{$term}%")
                  ->orWhere('academic_departments.code','LIKE', "%{$term}%")
                  ->orWhere('academic_programs.name',   'LIKE', "%{$term}%")
                  ->orWhere('academic_programs.code',   'LIKE', "%{$term}%")
                  ->orWhereRaw("{$fullNameExpr} LIKE ?", ["%{$term}%"])
                  ->orWhereRaw("{$reversedNameExpr} LIKE ?", ["%{$term}%"]);
            })
            ->select('students.*');
    }
}
