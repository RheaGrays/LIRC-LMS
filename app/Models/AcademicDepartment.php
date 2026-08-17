<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicDepartment extends Model
{
    //
    protected $fillable = ['level', 'name', 'code'];

    public function programs()
    {
        return $this->hasMany(AcademicProgram::class, 'department_id');
    }
}
