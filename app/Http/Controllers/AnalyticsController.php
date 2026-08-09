<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Student;
use App\Models\AcademicDepartment;
use App\Models\AcademicProgram;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AnalyticsController extends Controller
{
    public function index()
    {
        $terms = Cache::remember('academic_terms_all', 600, function () {
            return \App\Models\AcademicTerm::orderBy('start_date', 'desc')->get();
        });

        $departments = AcademicDepartment::orderBy('name')->get();
        $programs = AcademicProgram::orderBy('name')->get();

        return view('admin.analytics.index', compact('terms', 'departments', 'programs'));
    }

    public function data(Request $request): JsonResponse
    {
        $period = $request->input('period', 'today');
        $termId = $request->input('term_id');

        $cacheKey = "analytics_data_{$period}_" . ($termId ?? 'none');

        // Cache analytics data for 5 seconds for instant real-time chart updates
        $data = Cache::remember($cacheKey, 5, function () use ($period, $termId) {
            return $this->buildAnalyticsData($period, $termId);
        });

        return response()->json($data);
    }

    /**
     * Generate & Download Monthly Attendance Report per Program in Excel (.xlsx) format.
     */
    public function exportMonthlyReport(Request $request)
    {
        $monthInput = $request->input('month', now()->format('Y-m')); // e.g. 2026-08
        $programId  = $request->input('program_id');
        $deptId     = $request->input('department_id');

        $startDate  = Carbon::parse($monthInput)->startOfMonth();
        $endDate    = Carbon::parse($monthInput)->endOfMonth();

        $query = AttendanceLog::with(['student.academicDepartment', 'student.academicProgram'])
            ->whereBetween('logged_at', [$startDate, $endDate]);

        if ($programId) {
            $query->whereHas('student', function ($q) use ($programId) {
                $q->where('program_id', $programId);
            });
        }

        if ($deptId) {
            $query->whereHas('student', function ($q) use ($deptId) {
                $q->where('department_id', $deptId);
            });
        }

        $logs = $query->orderBy('logged_at', 'asc')->get();

        $programName = $programId ? (AcademicProgram::find($programId)?->name ?? 'All Programs') : 'All Programs';
        $deptName    = $deptId ? (AcademicDepartment::find($deptId)?->name ?? 'All Departments') : 'All Departments';
        $monthLabel  = $startDate->format('F Y');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Monthly Attendance');

        // Header Titles
        $sheet->setCellValue('A1', 'COR JESU COLLEGE — LIBRARY & INFORMATION RESOURCE CENTER');
        $sheet->setCellValue('A2', "MONTHLY ATTENDANCE REPORT PER PROGRAM ({$monthLabel})");
        $sheet->setCellValue('A3', "Program Filter: {$programName} | Department Filter: {$deptName}");

        $sheet->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A2')->getFont()->setSize(12);

        // Table Columns
        $headers = ['#', 'Date & Time', 'Student ID', 'Student Full Name', 'Category', 'Department', 'Program / Course', 'Year Level', 'Action'];
        $sheet->fromArray($headers, null, 'A5');
        $sheet->getStyle('A5:I5')->getFont()->setBold(true);

        $rowNum = 6;
        $counter = 1;
        foreach ($logs as $log) {
            $student = $log->student;
            $sheet->fromArray([
                $counter++,
                $log->logged_at->format('Y-m-d h:i:s A'),
                $log->student_id,
                $student ? $student->full_name : $log->student_id,
                $student ? $student->patron_category : 'Student',
                $student?->academicDepartment?->name ?? '—',
                $student?->academicProgram?->name ?? '—',
                $student?->year_level ?? '—',
                $log->action === 'check_in' ? 'CHECK-IN (ENTERED)' : 'CHECK-OUT (EXITED)',
            ], null, 'A' . $rowNum);
            $rowNum++;
        }

        // Summary Statistics Row
        $sheet->setCellValue('A' . ($rowNum + 1), "Total Attendance Log Entries for {$monthLabel}: " . count($logs));
        $sheet->getStyle('A' . ($rowNum + 1))->getFont()->setBold(true);

        // Auto-fit column widths
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Monthly_Attendance_Report_' . str_replace(' ', '_', $programName) . '_' . $startDate->format('Y_m') . '.xlsx';
        $writer   = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }

    /**
     * Database-agnostic analytics query logic.
     */
    private function buildAnalyticsData(string $period, ?string $termId): array
    {
        $query = AttendanceLog::query()->where('action', 'check_in');

        // Apply Date Filters
        if ($termId) {
            $term = \App\Models\AcademicTerm::find($termId, ['*']);
            if ($term) {
                $query->whereBetween('logged_at', [
                    $term->start_date->startOfDay(), 
                    $term->end_date->endOfDay()
                ]);
            }
        } else {
            $now = now();
            if ($period === 'today') {
                $query->where('logged_at', '>=', $now->copy()->startOfDay());
            } elseif ($period === 'week') {
                $query->where('logged_at', '>=', $now->copy()->startOfWeek());
            } elseif ($period === 'month') {
                $query->where('logged_at', '>=', $now->copy()->startOfMonth());
            } elseif ($period === 'year') {
                $query->where('logged_at', '>=', $now->copy()->startOfYear());
            }
        }

        $deptQuery = clone $query;

        $logs = $query->get(['id', 'student_id', 'logged_at']);

        $trafficLabels = [];
        $trafficValues = [];

        if ($termId || $period === 'year') {
            $grouped = $logs->groupBy(function ($log) {
                return Carbon::parse($log->logged_at)->format('Y-m');
            })->sortKeys();

            foreach ($grouped as $yearMonth => $group) {
                $trafficLabels[] = Carbon::createFromFormat('Y-m', $yearMonth)->format('M Y');
                $trafficValues[] = $group->count();
            }
        } elseif ($period === 'month' || $period === 'week') {
            $grouped = $logs->groupBy(function ($log) {
                return Carbon::parse($log->logged_at)->format('Y-m-d');
            })->sortKeys();

            foreach ($grouped as $dateStr => $group) {
                $trafficLabels[] = Carbon::parse($dateStr)->format('M d (D)');
                $trafficValues[] = $group->count();
            }
        } else {
            $hourlyCounts = $logs->groupBy(function ($log) {
                return (int) Carbon::parse($log->logged_at)->format('G');
            });

            for ($h = 6; $h <= 22; $h++) {
                $label = $h < 12 ? "{$h}AM" : ($h === 12 ? "12PM" : ($h - 12) . "PM");
                $trafficLabels[] = $label;
                $trafficValues[] = isset($hourlyCounts[$h]) ? $hourlyCounts[$h]->count() : 0;
            }
        }

        $deptData = $deptQuery->join('students', 'attendance_logs.student_id', '=', 'students.id')
            ->leftJoin('academic_departments', 'students.department_id', '=', 'academic_departments.id')
            ->selectRaw("COALESCE(academic_departments.name, 'Unknown') as department, COUNT(*) as aggregate", [])
            ->groupBy('department')
            ->orderByDesc('aggregate')
            ->get();

        $deptLabels = [];
        $deptValues = [];
        foreach ($deptData as $row) {
            $deptLabels[] = $row->department;
            $deptValues[] = (int) $row->aggregate;
        }

        return [
            'traffic' => [
                'labels' => $trafficLabels,
                'values' => $trafficValues
            ],
            'departments' => [
                'labels' => $deptLabels,
                'values' => $deptValues
            ]
        ];
    }
}
