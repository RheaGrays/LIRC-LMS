<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Student;
use App\Models\AcademicDepartment;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
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
            return AcademicTerm::orderBy('start_date', 'desc')->get();
        });

        $departments = AcademicDepartment::query()->orderBy('name', 'asc')->get();
        $programs    = AcademicProgram::query()->orderBy('name', 'asc')->get();

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
     * Unified Multi-Filter Report Generator
     * Filters: School Year (Term), Month, Program, and Type (Excel, Word, PDF)
     */
    public function exportMonthlyReport(Request $request)
    {
        $termId     = $request->input('term_id');
        $schoolYear = $request->input('school_year');
        $monthInput = $request->input('month');
        $programId  = $request->input('program_id');
        $deptId     = $request->input('department_id');
        $patronId   = $request->input('patron_id');
        $format     = strtolower($request->input('format', 'excel'));

        // PERF-03 FIX: Build the query once and reuse it for both COUNT and lazy iteration.
        // This avoids loading the entire result set into a PHP Collection in memory.
        $query = AttendanceLog::with(['student.academicDepartment', 'student.academicProgram']);

        $schoolYearLabel = 'All School Years';
        $monthLabel      = 'All Months';

        // 1. Filter by School Year / Academic Term
        if ($termId) {
            $term = AcademicTerm::query()->find($termId);
            if ($term) {
                $query->whereBetween('logged_at', [$term->start_date->startOfDay(), $term->end_date->endOfDay()]);
                $schoolYearLabel = $term->name;
            }
        } elseif ($schoolYear) {
            $yearNum = (int) preg_replace('/[^0-9]/', '', substr($schoolYear, 0, 8)) ?: now()->year;
            $query->whereYear('logged_at', $yearNum);
            $schoolYearLabel = "AY {$yearNum}-" . ($yearNum + 1);
        } else {
            $schoolYearLabel = "AY " . now()->format('Y') . "-" . (now()->year + 1);
        }

        // 2. Filter by Month
        if (!empty($monthInput)) {
            if (strlen($monthInput) === 7) {
                $startDate = Carbon::parse($monthInput)->startOfMonth();
                $endDate   = Carbon::parse($monthInput)->endOfMonth();
                $query->whereBetween('logged_at', [$startDate, $endDate]);
                $monthLabel = $startDate->format('F Y');
            } else {
                $monthNum = (int) $monthInput;
                if ($monthNum >= 1 && $monthNum <= 12) {
                    $query->whereMonth('logged_at', $monthNum);
                    $monthLabel = Carbon::create()->month($monthNum)->format('F');
                }
            }
        }

        // 3. Filter by Program
        if ($programId) {
            $query->whereHas('student', function ($q) use ($programId) {
                $q->where('program_id', $programId);
            });
        }

        // 4. Filter by Department
        if ($deptId) {
            $query->whereHas('student', function ($q) use ($deptId) {
                $q->where('department_id', $deptId);
            });
        }

        $programName = $programId ? (AcademicProgram::query()->find($programId)?->name ?? 'All Programs') : 'All Programs';
        $deptName    = $deptId ? (AcademicDepartment::query()->find($deptId)?->name ?? 'All Departments') : 'All Departments';

        // 5. Filter by Patron
        if ($patronId) {
            $query->where('student_id', $patronId);
            $programName .= " | Patron: {$patronId}";
        }

        // PERF-03 FIX: Use a fast COUNT query for the total — avoid loading all rows just for count().
        $totalCount = (clone $query)->count();

        // PERF-03 FIX: lazy() fetches rows in chunks of 500 using a cursor,
        // never holding the full result set in PHP memory at once.
        $logs = $query->orderBy('logged_at', 'asc')->lazy(500);

        if ($format === 'word' || $format === 'doc') {
            return $this->exportWordReport($logs, $totalCount, $schoolYearLabel, $monthLabel, $programName, $deptName);
        }

        if ($format === 'pdf') {
            return $this->exportPdfReport($logs, $totalCount, $schoolYearLabel, $monthLabel, $programName, $deptName);
        }

        return $this->exportExcelReport($logs, $totalCount, $schoolYearLabel, $monthLabel, $programName, $deptName);
    }

    // PERF-03 FIX: $logs is now a LazyCollection (cursor-based, ~500 rows in memory at a time).
    // $totalCount is pre-computed via COUNT() so we don't need to materialise the collection.
    private function exportExcelReport(iterable $logs, int $totalCount, string $schoolYearLabel, string $monthLabel, string $programName, string $deptName)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance Report');

        $sheet->setCellValue('A1', 'COR JESU COLLEGE — LIBRARY & INFORMATION RESOURCE CENTER');
        $sheet->setCellValue('A2', "OFFICIAL ATTENDANCE REPORT PER PROGRAM");
        $sheet->setCellValue('A3', "School Year: {$schoolYearLabel} | Month: {$monthLabel} | Program: {$programName}");

        $sheet->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A2')->getFont()->setSize(12);

        $headers = ['#', 'Date & Time', 'Student ID', 'Student Full Name', 'Category', 'Department', 'Program / Course', 'Year Level', 'Action'];
        $sheet->fromArray($headers, null, 'A5');
        $sheet->getStyle('A5:I5')->getFont()->setBold(true);

        $rowNum  = 6;
        $counter = 1;
        // Iterates the lazy cursor — each chunk of 500 is loaded, written, then discarded
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

        $sheet->setCellValue('A' . ($rowNum + 1), "Total Log Entries: {$totalCount}");
        $sheet->getStyle('A' . ($rowNum + 1))->getFont()->setBold(true);

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Attendance_Report_' . str_replace(' ', '_', $schoolYearLabel) . '_' . str_replace(' ', '_', $monthLabel) . '.xlsx';
        $writer   = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }

    private function exportWordReport(iterable $logs, int $totalCount, string $schoolYearLabel, string $monthLabel, string $programName, string $deptName)
    {
        $filename = 'Attendance_Report_' . str_replace(' ', '_', $schoolYearLabel) . '_' . str_replace(' ', '_', $monthLabel) . '.doc';

        $rowsHtml = '';
        $counter  = 1;
        foreach ($logs as $log) {
            $student = $log->student;
            $name   = htmlspecialchars($student ? $student->full_name : $log->student_id);
            $dept   = htmlspecialchars($student?->academicDepartment?->name ?? '—');
            $prog   = htmlspecialchars($student?->academicProgram?->name ?? '—');
            $action = $log->action === 'check_in'
                ? '<span style="color:#15803d;font-weight:bold;">CHECK-IN</span>'
                : '<span style="color:#b91c1c;font-weight:bold;">CHECK-OUT</span>';

            $rowsHtml .= "
                <tr>
                    <td style='padding:6px;border:1px solid #cbd5e1;text-align:center;'>{$counter}</td>
                    <td style='padding:6px;border:1px solid #cbd5e1;'>{$log->logged_at->format('Y-m-d h:i A')}</td>
                    <td style='padding:6px;border:1px solid #cbd5e1;'>{$log->student_id}</td>
                    <td style='padding:6px;border:1px solid #cbd5e1;'>{$name}</td>
                    <td style='padding:6px;border:1px solid #cbd5e1;'>{$dept}</td>
                    <td style='padding:6px;border:1px solid #cbd5e1;'>{$prog}</td>
                    <td style='padding:6px;border:1px solid #cbd5e1;text-align:center;'>{$action}</td>
                </tr>
            ";
            $counter++;
        }

        $html = "
            <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
            <head>
                <meta charset='utf-8'>
                <title>Attendance Report</title>
                <style>
                    body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #0f172a; }
                    h1 { font-size: 16pt; color: #0f2744; margin-bottom: 4px; }
                    h2 { font-size: 13pt; color: #c41e3a; margin-top: 0; margin-bottom: 12px; }
                    .meta { font-size: 10pt; color: #475569; margin-bottom: 16px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10pt; }
                    th { background-color: #0f2744; color: #ffffff; padding: 8px; border: 1px solid #0f2744; text-align: left; }
                    .summary { margin-top: 20px; font-weight: bold; font-size: 11pt; color: #0f2744; }
                </style>
            </head>
            <body>
                <h1>COR JESU COLLEGE</h1>
                <h2>Library & Information Resource Center — Attendance Report</h2>
                <div class='meta'>
                    <strong>School Year:</strong> {$schoolYearLabel}<br>
                    <strong>Month:</strong> {$monthLabel}<br>
                    <strong>Program Filter:</strong> {$programName}<br>
                    <strong>Generated Date:</strong> " . now()->format('F d, Y h:i A') . "
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date & Time</th>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Department</th>
                            <th>Program</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$rowsHtml}
                    </tbody>
                </table>
                <div class='summary'>Total Log Entries: {$totalCount}</div>
            </body>
            </html>
        ";

        return response($html)
            ->header('Content-Type', 'application/msword')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function exportPdfReport(iterable $logs, int $totalCount, string $schoolYearLabel, string $monthLabel, string $programName, string $deptName)
    {
        $counter  = 1;
        $rowsHtml = '';
        foreach ($logs as $log) {
            $student = $log->student;
            $name   = htmlspecialchars($student ? $student->full_name : $log->student_id);
            $dept   = htmlspecialchars($student?->academicDepartment?->name ?? '—');
            $prog   = htmlspecialchars($student?->academicProgram?->name ?? '—');
            $action = $log->action === 'check_in'
                ? '<span style="color:#15803d;font-weight:bold;background:#dcfce7;padding:2px 8px;border-radius:12px;">Entered</span>'
                : '<span style="color:#b91c1c;font-weight:bold;background:#fee2e2;padding:2px 8px;border-radius:12px;">Exited</span>';

            $rowsHtml .= "
                <tr>
                    <td style='padding:8px;border-bottom:1px solid #e2e8f0;text-align:center;'>{$counter}</td>
                    <td style='padding:8px;border-bottom:1px solid #e2e8f0;'>{$log->logged_at->format('Y-m-d h:i A')}</td>
                    <td style='padding:8px;border-bottom:1px solid #e2e8f0;font-weight:600;'>{$log->student_id}</td>
                    <td style='padding:8px;border-bottom:1px solid #e2e8f0;font-weight:600;'>{$name}</td>
                    <td style='padding:8px;border-bottom:1px solid #e2e8f0;'>{$dept}</td>
                    <td style='padding:8px;border-bottom:1px solid #e2e8f0;'>{$prog}</td>
                    <td style='padding:8px;border-bottom:1px solid #e2e8f0;text-align:center;'>{$action}</td>
                </tr>
            ";
            $counter++;
        }

        $html = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <title>Attendance Report - {$schoolYearLabel}</title>
                <style>
                    body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 24px; }
                    .header { background: #ffffff; padding: 24px; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
                    .brand { color: #c41e3a; font-weight: 800; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; }
                    h1 { color: #0f2744; margin: 6px 0 12px 0; font-size: 22px; font-weight: 900; }
                    .meta-grid { display: flex; gap: 24px; font-size: 13px; color: #475569; border-top: 1px solid #f1f5f9; padding-top: 12px; }
                    table { width: 100%; border-collapse: collapse; background: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; font-size: 13px; }
                    th { background-color: #0f2744; color: #ffffff; font-weight: 700; padding: 12px 8px; text-align: left; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
                    .summary-card { background: #0f2744; color: #ffffff; padding: 16px 24px; border-radius: 12px; margin-top: 24px; display: flex; justify-content: space-between; align-items: center; }
                    @media print {
                        body { background: white; padding: 0; }
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class='no-print' style='margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;'>
                    <div style='display: flex; gap: 12px; align-items: center;'>
                        <a href='javascript:history.back()' style='background: #ffffff; color: #475569; border: 1px solid #e2e8f0; padding: 10px 16px; border-radius: 8px; font-weight: bold; cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);'>
                            <svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'><path d='M19 12H5M12 19l-7-7 7-7'/></svg>
                            Back
                        </a>
                        <button onclick='window.print()' style='background: #c41e3a; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(196,30,58,0.2);'>
                            🖨️ Print / Save as PDF
                        </button>
                    </div>
                    <span style='color: #64748b; font-size: 13px;'>Press Ctrl + P to save as PDF</span>
                </div>
                <div class='header'>
                    <div class='brand'>Cor Jesu College — Library & Information Resource Center</div>
                    <h1>Official Patron Attendance Report</h1>
                    <div class='meta-grid'>
                        <div><strong>School Year:</strong> {$schoolYearLabel}</div>
                        <div><strong>Month:</strong> {$monthLabel}</div>
                        <div><strong>Program:</strong> {$programName}</div>
                        <div><strong>Total Logs:</strong> {$totalCount}</div>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th style='text-align:center;'>#</th>
                            <th>Date & Time</th>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Department</th>
                            <th>Program</th>
                            <th style='text-align:center;'>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$rowsHtml}
                    </tbody>
                </table>
                <div class='summary-card'>
                    <span>Cor Jesu College Library System</span>
                    <span>Total Attendance Entries: <strong>{$totalCount}</strong></span>
                </div>
            </body>
            </html>
        ";

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Database-agnostic analytics query logic.
     */
    private function buildAnalyticsData(string $period, ?string $termId): array
    {
        $query = AttendanceLog::query()->where('action', 'check_in');

        // Apply Date Filters
        if ($termId) {
            $term = AcademicTerm::find($termId, ['*']);
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

        // Fallback to active student department ratio if no attendance logs match current filter
        if (empty($deptLabels)) {
            $studentDepts = Student::query()
                ->leftJoin('academic_departments', 'students.department_id', '=', 'academic_departments.id')
                ->selectRaw("COALESCE(academic_departments.name, 'College of Computing Studies') as department, COUNT(*) as aggregate", [])
                ->groupBy('department')
                ->get();

            foreach ($studentDepts as $row) {
                $deptLabels[] = $row->department;
                $deptValues[] = (int) $row->aggregate;
            }
        }

        $totalPatrons = Student::count();
        $todayTraffic = AttendanceLog::where('action', 'check_in')->whereDate('logged_at', now()->toDateString())->count();
        $monthTraffic = AttendanceLog::where('action', 'check_in')->whereMonth('logged_at', now()->month)->whereYear('logged_at', now()->year)->count();
        $mostActiveDept = !empty($deptLabels) ? $deptLabels[0] : 'N/A';

        // Top Patron calculation
        $topPatronData = clone $query;
        $topPatron = $topPatronData->join('students', 'attendance_logs.student_id', '=', 'students.id')
            ->selectRaw('students.full_name as name, COUNT(*) as aggregate')
            ->groupBy('name')
            ->orderByDesc('aggregate')
            ->first();
        $topPatronName = $topPatron ? $topPatron->name : 'N/A';

        return [
            'summary' => [
                'total_patrons' => $totalPatrons,
                'today_traffic' => $todayTraffic,
                'month_traffic' => $monthTraffic,
                'active_dept' => $mostActiveDept,
                'top_patron' => $topPatronName
            ],
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
