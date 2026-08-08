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
        // Fetch all academic programs so we can map Department name -> Years
        $programs = AcademicProgram::all()->keyBy('id');
        $currentYear = (int) date('Y');
        $filter = $request->query('filter', 'all');
        
        $candidates = [];

        // Chunk through active students to prevent memory spikes
        Student::query()
            ->where('status', 'active')
            ->with(['latestAttendance'])
            ->chunk(200, function ($students) use (&$candidates, $programs, $currentYear) {
                foreach ($students as $student) {
                    // Extract numeric year level (e.g. "1st Year" -> 1, "Grade 7" -> 7)
                    preg_match('/\d+/', $student->year_level, $matches);
                    $numericYearLevel = !empty($matches) ? (int)$matches[0] : 1;

                    // Default program duration
                    $programDuration = 4;
                    
                    // Check if student's program matches an academic program
                    if ($student->program_id && isset($programs[$student->program_id])) {
                        $programDuration = $programs[$student->program_id]->years;
                    } elseif (stripos($student->year_level, 'grade') !== false) {
                        if ($numericYearLevel <= 10) {
                            $programDuration = 10;
                        } else {
                            $programDuration = 12;
                        }
                    }
                    
                    // Get registration year from created_at
                    $registrationYear = (int) ($student->created_at ? $student->created_at->format('Y') : date('Y'));
                    
                    // Calculate expected graduation year
                    $yearsRemaining = max(0, $programDuration - $numericYearLevel + 1);
                    $expectedGraduationYear = $registrationYear + $yearsRemaining;
                    
                    // Get last visit date from logged_at (fallback to registration date if never visited)
                    $lastVisit = $student->latestAttendance ? $student->latestAttendance->logged_at : null;
                    $daysSinceLastVisit = $lastVisit ? $lastVisit->diffInDays(now()) : ($student->created_at ? $student->created_at->diffInDays(now()) : 0);
                    
                    // Determine flags
                    $isGraduated = $currentYear > $expectedGraduationYear;
                    
                    $student->expected_graduation_year = $expectedGraduationYear;
                    $student->days_since_last_visit = $daysSinceLastVisit;
                    $student->last_visit_date = $lastVisit ? $lastVisit->format('M d, Y') : 'Never';
                    $student->is_graduated = $isGraduated;
                    $candidates[] = $student;
                }
            });

        $stats = [
            'total' => count($candidates),
            'graduated' => collect($candidates)->where('is_graduated', true)->count(),
            'inactive_1' => collect($candidates)->where('days_since_last_visit', '>=', 365)->count(),
            'inactive_4' => collect($candidates)->where('days_since_last_visit', '>=', 1460)->count(),
        ];

        // Filter candidates after calculating stats
        if ($filter !== 'all') {
            $candidates = array_filter($candidates, function($student) use ($filter) {
                if ($filter === 'graduated') return $student->is_graduated;
                if ($filter === 'inactive_1_year') return $student->days_since_last_visit >= 365;
                if ($filter === 'inactive_3_years') return $student->days_since_last_visit >= 1095;
                if ($filter === 'inactive_4_years') return $student->days_since_last_visit >= 1460;
                return false;
            });
        }

        // Sort by days since last visit DESC
        usort($candidates, function($a, $b) {
            return $b->days_since_last_visit <=> $a->days_since_last_visit;
        });

        return view('admin.students.archive', [
            'candidates' => collect($candidates),
            'filter' => $filter,
            'stats' => $stats
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
