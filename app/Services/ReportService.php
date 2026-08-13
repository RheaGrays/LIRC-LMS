<?php

namespace App\Services;

use App\Models\Student;
use App\Models\AttendanceLog;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Response;

class ReportService
{
    /**
     * Sanitize a string value to prevent CSV/Excel formula injection.
     * Prefixes cells starting with formula-triggering characters with a single quote.
     */
    private function sanitizeForExcel(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        // Characters that can trigger formula execution in Excel/LibreOffice
        $dangerousChars = ['=', '+', '-', '@', "\t", "\r"];

        if (in_array($value[0], $dangerousChars, true)) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * Export Students List to Excel
     */
    public function exportStudentsExcel()
    {
        // PERF-A02 FIX: Use lazy(200) instead of get() so students are streamed in
        // chunks of 200 rows rather than loading the entire table into PHP memory.
        // lazy() returns a LazyCollection — the foreach loop below works identically.
        $students = Student::query()->with(['academicDepartment'])->withCount('violations')->lazy(200);

        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header
        $sheet->setCellValue('A1', 'ID Number');
        $sheet->setCellValue('B1', 'Full Name');
        $sheet->setCellValue('C1', 'Department');
        $sheet->setCellValue('D1', 'Year Level');
        $sheet->setCellValue('E1', 'Violations');
        
        // Style Header
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('A1:E1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF3F4F6');
        
        $row = 2;
        foreach ($students as $student) {
            // Use setCellValueExplicit with STRING type for user-provided data
            // to prevent formula injection (e.g. names starting with =, +, -, @)
            $sheet->setCellValueExplicit('A' . $row, $this->sanitizeForExcel($student->id), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B' . $row, $this->sanitizeForExcel($student->full_name), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $row, $this->sanitizeForExcel($student->academicDepartment?->name ?? '—'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $row, $this->sanitizeForExcel($student->year_level), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $row, $student->violations_count);
            $row++;
        }
        
        // Auto size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = new Xlsx($spreadsheet);
        
        // Save to temp file
        $fileName = 'Students_Export_' . date('Y-m-d') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);
        
        return Response::download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
    
    /**
     * Export Audit Log to PDF (Example)
     */
    public function exportAuditPdf($date)
    {
        $logs = AttendanceLog::query()->with('student')
            ->whereDate('logged_at', '=', $date, 'and')
            ->orderBy('logged_at', 'desc')
            ->get();
            
        // Assuming we create a basic blade view for the PDF at resources/views/reports/audit.blade.php
        $pdf = Pdf::loadView('reports.audit', [
            'logs' => $logs,
            'date' => $date
        ]);
        
        return $pdf->download('Audit_Log_' . $date . '.pdf');
    }
}
