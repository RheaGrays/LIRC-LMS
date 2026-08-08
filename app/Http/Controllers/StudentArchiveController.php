<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\AcademicProgram;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudentArchiveController extends Controller
{
    public function index(Request $request)
    {
        // 1. Fetch all active students and their latest attendance log
        $students = Student::query()->where('status', 'active')
            ->with('latestAttendance')
            ->get();
            
        // 2. Fetch all academic programs so we can map Department name -> Years
        $programs = AcademicProgram::all()->keyBy('name');

        $currentYear = (int) date('Y');
        
        $candidates = [];

        foreach ($students as $student) {
            // Extract numeric year level (e.g. "1st Year" -> 1, "Grade 7" -> 7)
            preg_match('/\d+/', $student->year_level, $matches);
            $numericYearLevel = !empty($matches) ? (int)$matches[0] : 1;

            // Default program duration
            $programDuration = 4;
            
            // Check if student's department matches an academic program
            if (isset($programs[$student->department])) {
                $programDuration = $programs[$student->department]->years;
            } elseif (stripos($student->year_level, 'grade') !== false) {
                // If JHS or SHS, assume default duration relative to Grade 10 or Grade 12
                if ($numericYearLevel <= 10) {
                    $programDuration = 10; // JHS ends at Grade 10
                } else {
                    $programDuration = 12; // SHS ends at Grade 12
                }
            }
            
            // Get registration year from created_at
            $registrationYear = (int) $student->created_at->format('Y');
            
            // Calculate expected graduation year
            $yearsRemaining = max(0, $programDuration - $numericYearLevel + 1);
            $expectedGraduationYear = $registrationYear + $yearsRemaining;
            
            // Get last visit date (fallback to registration date if never visited)
            $lastVisit = $student->latestAttendance ? $student->latestAttendance->created_at : null;
            $daysSinceLastVisit = $lastVisit ? $lastVisit->diffInDays(now()) : $student->created_at->diffInDays(now());
            
            // Determine flags
            $isGraduated = $currentYear > $expectedGraduationYear;
            
            $filter = $request->query('filter', 'all');
            
            $include = false;
            if ($filter === 'all') {
                $include = true;
            } elseif ($filter === 'graduated' && $isGraduated) {
                $include = true;
            } elseif ($filter === 'inactive_1_year' && $daysSinceLastVisit >= 365) {
                $include = true;
            } elseif ($filter === 'inactive_3_years' && $daysSinceLastVisit >= 1095) {
                $include = true;
            } elseif ($filter === 'inactive_4_years' && $daysSinceLastVisit >= 1460) {
                $include = true;
            }

            if ($include) {
                $student->expected_graduation_year = $expectedGraduationYear;
                $student->days_since_last_visit = $daysSinceLastVisit;
                $student->last_visit_date = $lastVisit ? $lastVisit->format('M d, Y') : 'Never';
                $student->is_graduated = $isGraduated;
                $candidates[] = $student;
            }
        }

        // Sort by days since last visit DESC
        usort($candidates, function($a, $b) {
            return $b->days_since_last_visit <=> $a->days_since_last_visit;
        });

        return view('admin.students.archive', [
            'candidates' => collect($candidates),
            'filter' => $request->query('filter', 'all')
        ]);
    }
    
    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'string|exists:students,id'
        ]);

        Student::query()->whereIn('id', $request->student_ids, 'and', false)->update(['status' => 'inactive']);

        return redirect()->route('admin.students.archive')->with('success', count($request->student_ids) . ' students successfully archived (deactivated).');
    }
}
