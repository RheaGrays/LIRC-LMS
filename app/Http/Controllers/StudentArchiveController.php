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
        // Fetch all academic programs so we can map program_id -> Years
        $programs = AcademicProgram::query()->get()->keyBy('id');
        $currentYear = (int) date('Y');
        $filter = $request->query('filter', 'all');

        // PERF-FIX: Replaced chunk(200) + accumulate pattern with a single SQL query.
        // Uses a subquery to get last_logged_at as a scalar column instead of
        // eager-loading full AttendanceLog model instances per student.
        // LIMIT enforces the cap in SQL rather than breaking out of a PHP loop.
        $maxCandidates = 3000;

        $lastAttendanceSub = \App\Models\AttendanceLog::query()
            ->selectRaw('student_id, MAX(logged_at) as last_logged_at', [])
            ->groupBy('student_id');

        $students = Student::query()
            ->where('status', 'active')
            ->leftJoinSub($lastAttendanceSub, 'latest_att', function ($join) {
                $join->on('students.id', '=', 'latest_att.student_id');
            })
            ->select('students.*', 'latest_att.last_logged_at')
            ->limit($maxCandidates + 1) // fetch one extra to detect capping
            ->get();

        $capped = $students->count() > $maxCandidates;
        if ($capped) {
            $students = $students->take($maxCandidates);
        }

        $candidates = [];
        $now = now();

        foreach ($students as $student) {
            // Extract numeric year level (e.g. "1st Year" -> 1, "Grade 7" -> 7)
            preg_match('/\d+/', $student->year_level ?? '', $matches);
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
            
            // Get last visit date from the subquery column (no extra model loaded)
            $lastVisit = $student->last_logged_at ? Carbon::parse($student->last_logged_at) : null;
            $daysSinceLastVisit = $lastVisit ? $lastVisit->diffInDays($now) : ($student->created_at ? $student->created_at->diffInDays($now) : 0);
            
            // Determine flags
            $isGraduated = $currentYear > $expectedGraduationYear;
            
            $student->expected_graduation_year = $expectedGraduationYear;
            $student->days_since_last_visit = $daysSinceLastVisit;
            $student->last_visit_date = $lastVisit ? $lastVisit->format('M d, Y') : 'Never';
            $student->is_graduated = $isGraduated;
            $candidates[] = $student;
        }

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
            'filter'     => $filter,
            'stats'      => $stats,
            'capped'     => $capped,
            'maxCandidates' => $maxCandidates,
        ]);
    }
    
    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'string|exists:students,id'
        ]);

        // Intelephense stubs require all 4 whereIn() args explicitly (even though 'and' and false are defaults).
        Student::query()->whereIn('id', $request->student_ids, 'and', false)->update(['status' => 'inactive']);


        return redirect()->route('admin.students.archive')->with('success', count($request->student_ids) . ' students successfully archived (deactivated).');
    }
}
