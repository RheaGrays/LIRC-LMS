<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicProgram extends Model
{
    //
    protected $fillable = ['department_id', 'name', 'code', 'years'];

    public function department()
    {
        return $this->belongsTo(AcademicDepartment::class, 'department_id');
    }
}
