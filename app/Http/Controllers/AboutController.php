<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Student;
use App\Models\AcademicDepartment;
use App\Models\AcademicProgram;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function index()
    {
        $totalStudents    = Student::query()->count('*');
        $totalLogs        = AttendanceLog::query()->count('*');
        $totalDepartments = AcademicDepartment::query()->count('*');
        $totalPrograms    = AcademicProgram::query()->count('*');


        return view('about.index', compact(
            'totalStudents',
            'totalLogs',
            'totalDepartments',
            'totalPrograms'
        ));
    }
}
