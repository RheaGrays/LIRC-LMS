<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Student;
use App\Models\AcademicDepartment;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use App\Services\AnalyticsService;
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
            return AcademicTerm::query()->orderBy('start_date', 'desc')->get();
        });

        $departments = Cache::remember('academic_departments_all', 600, function () {
            return AcademicDepartment::query()->orderBy('name', 'asc')->get();
        });
        $programs = Cache::remember('academic_programs_all', 600, function () {
            return AcademicProgram::query()->orderBy('name', 'asc')->get();
        });

        return view('admin.analytics.index', compact('terms', 'departments', 'programs'));
    }

    public function data(Request $request, AnalyticsService $analyticsService): JsonResponse
    {
        $period = $request->input('period', 'today');
        $termId = $request->input('term_id');

        $data = $analyticsService->getAnalyticsData($period, $termId);

        return response()->json($data);
    }

    /**
     * Unified Multi-Filter Report Generator
     * Filters: School Year (Term), Month, Program, and Type (Excel, Word, PDF)
     */
    public function exportMonthlyReport(Request $request)
    {
        $termId         = $request->input('term_id');
        $schoolYear     = $request->input('school_year');
        $monthInput     = $request->input('month');
        $dateMode       = $request->input('date_mode', 'month');
        $startDateInput = $request->input('start_date');
        $endDateInput   = $request->input('end_date');
        $startTime      = $request->input('start_time');
        $endTime        = $request->input('end_time');
        $reportType     = $request->input('report_type', 'college_programs');
        $programId      = $request->input('program_id');
        $deptId         = $request->input('department_id');
        $patronId       = $request->input('patron_id');
        $format         = strtolower($request->input('format', 'excel'));

        $query = AttendanceLog::query()->where('attendance_logs.action', 'check_in');

        $schoolYearLabel = 'All School Years';
        $monthLabel      = 'All Dates';

        // 1. Date Filtering
        if ($dateMode === 'custom' && $startDateInput && $endDateInput) {
            $sDate = Carbon::parse($startDateInput)->startOfDay();
            $eDate = Carbon::parse($endDateInput)->endOfDay();
            $query->whereBetween('attendance_logs.logged_at', [$sDate, $eDate]);
            $monthLabel = $sDate->format('M d, Y') . ' — ' . $eDate->format('M d, Y');
            $schoolYearLabel = 'Custom Range';
        } elseif ($termId) {
            $term = AcademicTerm::query()->find($termId);
            if ($term) {
                $query->whereBetween('attendance_logs.logged_at', [$term->start_date->startOfDay(), $term->end_date->endOfDay()]);
                $schoolYearLabel = $term->name;
                $monthLabel = $term->start_date->format('M Y') . ' — ' . $term->end_date->format('M Y');
            }
        } elseif (!empty($monthInput)) {
            if (strlen($monthInput) === 7) {
                $startDate = Carbon::parse($monthInput)->startOfMonth();
                $endDate   = Carbon::parse($monthInput)->endOfMonth();
                $query->whereBetween('attendance_logs.logged_at', [$startDate, $endDate]);
                $monthLabel = $startDate->format('F Y');
            } else {
                $monthNum = (int) $monthInput;
                if ($monthNum >= 1 && $monthNum <= 12) {
                    $query->whereMonth('attendance_logs.logged_at', '=', $monthNum);
                    $monthLabel = Carbon::create()->month($monthNum)->format('F');
                }
            }
        } elseif ($schoolYear) {
            $yearNum = (int) preg_replace('/[^0-9]/', '', substr($schoolYear, 0, 8)) ?: now()->year;
            $query->whereYear('attendance_logs.logged_at', '=', $yearNum);
            $schoolYearLabel = "AY {$yearNum}-" . ($yearNum + 1);
        } else {
            $schoolYearLabel = "AY " . now()->format('Y') . "-" . (now()->year + 1);
        }

        // 2. Time Window Filtering (07:00 to 19:00, etc.)
        $timeLabel = 'All Hours';
        if (!empty($startTime) && !empty($endTime)) {
            $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
            if ($driver === 'sqlite') {
                $query->whereRaw("strftime('%H:%M', attendance_logs.logged_at) BETWEEN ? AND ?", [$startTime, $endTime]);
            } else {
                $query->whereRaw("TIME(attendance_logs.logged_at) BETWEEN ? AND ?", [$startTime . ':00', $endTime . ':59']);
            }
            $timeLabel = Carbon::parse($startTime)->format('g:i A') . ' — ' . Carbon::parse($endTime)->format('g:i A');
        }

        // 3. Program, Department & Patron Filters
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

        if ($patronId) {
            $query->where('student_id', $patronId);
        }

        $programName = $programId ? (AcademicProgram::query()->find($programId)?->name ?? 'All Programs') : 'All Programs';
        $deptName    = $deptId ? (AcademicDepartment::query()->find($deptId)?->name ?? 'All Departments') : 'All Departments';

        $totalCount = (clone $query)->count('*');

        // ── REPORT TYPE 1: College & Programs Ranking Report ──
        if ($reportType === 'college_programs') {
            $rankingQuery = clone $query;
            $rankingQuery->setEagerLoads([]);
            $rankingQuery->join('students', 'attendance_logs.student_id', '=', 'students.id', 'inner', false)
                ->leftJoin('academic_departments', 'students.department_id', '=', 'academic_departments.id', 'left', false)
                ->leftJoin('academic_programs', 'students.program_id', '=', 'academic_programs.id', 'left', false)
                ->selectRaw("
                    COALESCE(NULLIF(academic_departments.code, ''), academic_departments.name, 'Unassigned') as college,
                    COALESCE(NULLIF(academic_programs.code, ''), academic_programs.name, 'Unassigned') as program,
                    COUNT(attendance_logs.id) as attendance
                ", [])
                ->groupBy('college', 'program')
                ->orderByDesc('attendance')
                ->orderBy('college')
                ->orderBy('program');

            $rows = $rankingQuery->get();

            if ($format === 'word' || $format === 'doc') {
                return $this->exportCollegeProgramsWord($rows, $totalCount, $schoolYearLabel, $monthLabel, $timeLabel, $programName, $deptName);
            }

            if ($format === 'pdf') {
                return $this->exportCollegeProgramsPdf($rows, $totalCount, $schoolYearLabel, $monthLabel, $timeLabel, $programName, $deptName);
            }

            return $this->exportCollegeProgramsExcel($rows, $totalCount, $schoolYearLabel, $monthLabel, $timeLabel, $programName, $deptName);
        }

        // ── REPORT TYPE 2: Detailed Patron Attendance Summary ──
        $summaryQuery = clone $query;
        $summaryQuery->setEagerLoads([]);
        $summaryQuery->leftJoin('students', 'attendance_logs.student_id', '=', 'students.id', 'left', false)
            ->leftJoin('academic_departments', 'students.department_id', '=', 'academic_departments.id', 'left', false)
            ->leftJoin('academic_programs', 'students.program_id', '=', 'academic_programs.id', 'left', false)
            ->selectRaw('
                attendance_logs.student_id, 
                COALESCE((students.first_name || " " || students.last_name), attendance_logs.student_id) as student_name, 
                COALESCE(students.patron_category, "Student") as category, 
                COALESCE(academic_departments.name, "—") as department, 
                COALESCE(academic_programs.name, "—") as program, 
                COALESCE(students.year_level, "—") as year_level, 
                COUNT(attendance_logs.id) as total_visits
            ', [])
            ->groupBy(
                'attendance_logs.student_id', 
                'students.id',
                'students.first_name', 
                'students.last_name', 
                'students.patron_category', 
                'academic_departments.name', 
                'academic_programs.name', 
                'students.year_level'
            )
            ->orderByDesc('total_visits')
            ->orderBy('students.last_name')
            ->orderBy('students.first_name');

        $logs = $summaryQuery->lazy(500);

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

        $headers = ['#', 'Student ID', 'Student Full Name', 'Category', 'Department', 'Program / Course', 'Year Level', 'Total Entries'];
        $sheet->fromArray($headers, null, 'A5');
        $sheet->getStyle('A5:H5')->getFont()->setBold(true);

        $rowNum  = 6;
        $counter = 1;
        
        foreach ($logs as $log) {
            $sheet->fromArray([
                $counter++,
                $log->student_id,
                $log->student_name,
                $log->category,
                $log->department,
                $log->program,
                $log->year_level,
                $log->total_visits,
            ], null, 'A' . $rowNum);
            $rowNum++;
        }

        $sheet->setCellValue('A' . ($rowNum + 1), "Total Log Entries: {$totalCount}");
        $sheet->getStyle('A' . ($rowNum + 1))->getFont()->setBold(true);

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Attendance_Report_' . str_replace(' ', '_', $schoolYearLabel) . '_' . str_replace(' ', '_', $monthLabel) . '.xlsx';
        $writer   = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(function() use ($writer, $spreadsheet) {
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function exportWordReport(iterable $logs, int $totalCount, string $schoolYearLabel, string $monthLabel, string $programName, string $deptName)
    {
        $filename = 'Attendance_Report_' . str_replace(' ', '_', $schoolYearLabel) . '_' . str_replace(' ', '_', $monthLabel) . '.doc';

        $rowsHtml = '';
        $counter  = 1;
        foreach ($logs as $log) {
            $studentId = htmlspecialchars($log->student_id);
            $name = htmlspecialchars($log->student_name);
            $dept = htmlspecialchars($log->department);
            $prog = htmlspecialchars($log->program);

            $rowsHtml .= "
                <tr>
                    <td style='padding:6px;border:1px solid #cbd5e1;text-align:center;'>{$counter}</td>
                    <td style='padding:6px;border:1px solid #cbd5e1;'>{$studentId}</td>
                    <td style='padding:6px;border:1px solid #cbd5e1;'>{$name}</td>
                    <td style='padding:6px;border:1px solid #cbd5e1;'>{$dept}</td>
                    <td style='padding:6px;border:1px solid #cbd5e1;'>{$prog}</td>
                    <td style='padding:6px;border:1px solid #cbd5e1;text-align:center;'><strong>{$log->total_visits}</strong></td>
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
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Department</th>
                            <th>Program</th>
                            <th>Total Entries</th>
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
            $name = htmlspecialchars($log->student_name);
            $dept = htmlspecialchars($log->department);
            $prog = htmlspecialchars($log->program);

            $rowsHtml .= "
                <tr>
                    <td style='padding:8px;border-bottom:1px solid #e2e8f0;text-align:center;'>{$counter}</td>
                    <td style='padding:8px;border-bottom:1px solid #e2e8f0;font-weight:600;'>{$log->student_id}</td>
                    <td style='padding:8px;border-bottom:1px solid #e2e8f0;font-weight:600;'>{$name}</td>
                    <td style='padding:8px;border-bottom:1px solid #e2e8f0;'>{$dept}</td>
                    <td style='padding:8px;border-bottom:1px solid #e2e8f0;'>{$prog}</td>
                    <td style='padding:8px;border-bottom:1px solid #e2e8f0;text-align:center;font-weight:bold;color:#0f2744;'>{$log->total_visits}</td>
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
                            <svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 6 2 18 2 18 9'></polyline><path d='M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2'></path><rect x='6' y='14' width='12' height='8'></rect></svg>
                            Print / Save as PDF
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
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Department</th>
                            <th>Program</th>
                            <th style='text-align:center;'>Total Entries</th>
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
     * Export College & Programs Ranking Report to Excel (.xlsx)
     * Format matches user reference: # | College | Programs | Attendance
     */
    private function exportCollegeProgramsExcel(iterable $rows, int $totalCount, string $schoolYearLabel, string $monthLabel, string $timeLabel, string $programName, string $deptName)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Programs Attendance');

        // Document Title
        $sheet->setCellValue('A1', 'COR JESU COLLEGE — LIBRARY & INFORMATION RESOURCE CENTER');
        $sheet->setCellValue('A2', 'OFFICIAL ATTENDANCE REPORT — COLLEGE & PROGRAMS RANKING');
        $sheet->setCellValue('A3', "Period: {$monthLabel}  |  Time: {$timeLabel}  |  Generated: " . now()->format('M d, Y h:i A'));

        $sheet->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0F2744'));
        $sheet->getStyle('A2')->getFont()->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('C41E2A'));
        $sheet->getStyle('A3')->getFont()->setSize(10)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        // Table Header (Row 5)
        $headers = ['#', 'College', 'Programs', 'Attendance'];
        $sheet->fromArray($headers, null, 'A5');
        $sheet->getStyle('A5:D5')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A5:D5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F2744');
        $sheet->getStyle('A5:C5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('D5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getRowDimension(5)->setRowHeight(24);

        $rowNum  = 6;
        $counter = 1;
        foreach ($rows as $row) {
            $sheet->setCellValue('A' . $rowNum, $counter++);
            $sheet->setCellValue('B' . $rowNum, $row->college);
            $sheet->setCellValue('C' . $rowNum, $row->program);
            $sheet->setCellValue('D' . $rowNum, (int) $row->attendance);

            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            if ($rowNum % 2 === 0) {
                $sheet->getStyle("A{$rowNum}:D{$rowNum}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
            }
            $rowNum++;
        }

        // Summary row
        $sheet->setCellValue('A' . $rowNum, 'TOTAL ATTENDANCE');
        $sheet->setCellValue('D' . $rowNum, $totalCount);
        $sheet->getStyle("A{$rowNum}:D{$rowNum}")->getFont()->setBold(true);
        $sheet->getStyle("A{$rowNum}:D{$rowNum}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle('D' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        $fullRange = "A5:D{$rowNum}";
        $sheet->getStyle($fullRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB('CBD5E1');

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(18);

        $safePeriod = preg_replace('/[^a-zA-Z0-9_-]/', '_', $monthLabel);
        $filename   = "College_Programs_Attendance_{$safePeriod}.xlsx";
        $writer     = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(function () use ($writer, $spreadsheet) {
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export College & Programs Ranking Report to Word (.doc)
     */
    private function exportCollegeProgramsWord(iterable $rows, int $totalCount, string $schoolYearLabel, string $monthLabel, string $timeLabel, string $programName, string $deptName)
    {
        $safePeriod = preg_replace('/[^a-zA-Z0-9_-]/', '_', $monthLabel);
        $filename   = "College_Programs_Attendance_{$safePeriod}.doc";

        $rowsHtml = '';
        $counter  = 1;
        foreach ($rows as $row) {
            $college = htmlspecialchars($row->college);
            $program = htmlspecialchars($row->program);
            $rowsHtml .= "
                <tr>
                    <td style='padding:6px 12px;border-bottom:1px solid #e2e8f0;text-align:center;'>{$counter}</td>
                    <td style='padding:6px 12px;border-bottom:1px solid #e2e8f0;font-weight:600;'>{$college}</td>
                    <td style='padding:6px 12px;border-bottom:1px solid #e2e8f0;'>{$program}</td>
                    <td style='padding:6px 12px;border-bottom:1px solid #e2e8f0;text-align:right;font-weight:bold;color:#0f2744;'>{$row->attendance}</td>
                </tr>
            ";
            $counter++;
        }

        $html = "
            <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
            <head>
                <meta charset='utf-8'>
                <title>College & Programs Attendance Report</title>
                <style>
                    body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #0f172a; }
                    h1 { font-size: 16pt; color: #0f2744; margin-bottom: 4px; }
                    h2 { font-size: 12pt; color: #c41e3a; margin-top: 0; margin-bottom: 12px; }
                    .meta { font-size: 10pt; color: #475569; margin-bottom: 16px; border-bottom: 1px solid #cbd5e1; padding-bottom: 8px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10.5pt; }
                    th { border-top: 1.5pt solid #0f2744; border-bottom: 1.5pt solid #0f2744; padding: 8px 12px; text-align: left; font-weight: bold; color: #0f2744; }
                    th.text-right { text-align: right; }
                    th.text-center { text-align: center; }
                    .summary { margin-top: 20px; font-weight: bold; font-size: 11pt; color: #0f2744; border-top: 1.5pt solid #0f2744; padding-top: 8px; }
                </style>
            </head>
            <body>
                <h1>COR JESU COLLEGE</h1>
                <h2>Official College & Programs Attendance Report</h2>
                <div class='meta'>
                    <strong>Period:</strong> {$monthLabel}<br>
                    <strong>Time Range:</strong> {$timeLabel}<br>
                    <strong>Generated Date:</strong> " . now()->format('F d, Y h:i A') . "
                </div>
                <table>
                    <thead>
                        <tr>
                            <th class='text-center' style='width: 40px;'>#</th>
                            <th style='width: 140px;'>College</th>
                            <th>Programs</th>
                            <th class='text-right' style='width: 110px;'>Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$rowsHtml}
                    </tbody>
                </table>
                <div class='summary'>Total Attendance: {$totalCount}</div>
            </body>
            </html>
        ";

        return response($html)
            ->header('Content-Type', 'application/msword')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export College & Programs Ranking Report to Printable PDF/HTML view
     */
    private function exportCollegeProgramsPdf(iterable $rows, int $totalCount, string $schoolYearLabel, string $monthLabel, string $timeLabel, string $programName, string $deptName)
    {
        $counter  = 1;
        $rowsHtml = '';
        foreach ($rows as $row) {
            $college = htmlspecialchars($row->college);
            $program = htmlspecialchars($row->program);
            $rowsHtml .= "
                <tr>
                    <td style='padding:8px 14px;border-bottom:1px solid #e2e8f0;text-align:center;color:#64748b;'>{$counter}</td>
                    <td style='padding:8px 14px;border-bottom:1px solid #e2e8f0;font-weight:700;color:#0f2744;'>{$college}</td>
                    <td style='padding:8px 14px;border-bottom:1px solid #e2e8f0;font-weight:500;'>{$program}</td>
                    <td style='padding:8px 14px;border-bottom:1px solid #e2e8f0;text-align:right;font-weight:700;color:#0f2744;'>{$row->attendance}</td>
                </tr>
            ";
            $counter++;
        }

        $html = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <title>College & Programs Attendance Report - {$monthLabel}</title>
                <style>
                    body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 24px; }
                    .container { max-width: 820px; margin: 0 auto; }
                    .header { background: #ffffff; padding: 24px; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
                    .brand { color: #c41e3a; font-weight: 800; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; }
                    h1 { color: #0f2744; margin: 6px 0 12px 0; font-size: 22px; font-weight: 900; }
                    .meta-grid { display: flex; gap: 24px; font-size: 13px; color: #475569; border-top: 1px solid #f1f5f9; padding-top: 12px; }
                    table { width: 100%; border-collapse: collapse; background: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; font-size: 13.5px; }
                    th { border-bottom: 2px solid #0f2744; padding: 12px 14px; text-align: left; font-weight: 800; color: #0f2744; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
                    th.text-right { text-align: right; }
                    th.text-center { text-align: center; }
                    .summary-card { background: #0f2744; color: #ffffff; padding: 16px 24px; border-radius: 12px; margin-top: 24px; display: flex; justify-content: space-between; align-items: center; }
                    @media print {
                        body { background: white; padding: 0; }
                        .no-print { display: none; }
                        .container { max-width: 100%; }
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='no-print' style='margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;'>
                        <div style='display: flex; gap: 12px; align-items: center;'>
                            <a href='javascript:history.back()' style='background: #ffffff; color: #475569; border: 1px solid #e2e8f0; padding: 10px 16px; border-radius: 8px; font-weight: bold; cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);'>
                                <svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'><path d='M19 12H5M12 19l-7-7 7-7'/></svg>
                                Back
                            </a>
                            <button onclick='window.print()' style='background: #c41e3a; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(196,30,58,0.2);'>
                                <svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 6 2 18 2 18 9'></polyline><path d='M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2'></path><rect x='6' y='14' width='12' height='8'></rect></svg>
                                Print / Save as PDF
                            </button>
                        </div>
                        <span style='color: #64748b; font-size: 13px;'>Press Ctrl + P to save as PDF</span>
                    </div>
                    <div class='header'>
                        <div class='brand'>Cor Jesu College — Library & Information Resource Center</div>
                        <h1>Official College & Programs Attendance Report</h1>
                        <div class='meta-grid'>
                            <div><strong>Period:</strong> {$monthLabel}</div>
                            <div><strong>Time Range:</strong> {$timeLabel}</div>
                            <div><strong>Total Attendance:</strong> {$totalCount}</div>
                        </div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th class='text-center' style='width: 50px;'>#</th>
                                <th style='width: 160px;'>College</th>
                                <th>Programs</th>
                                <th class='text-right' style='width: 140px;'>Attendance</th>
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
                </div>
            </body>
            </html>
        ";

        return response($html)->header('Content-Type', 'text/html');
    }
}
