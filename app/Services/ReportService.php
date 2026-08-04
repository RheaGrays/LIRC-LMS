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
     * Export Students List to Excel
     */
    public function exportStudentsExcel()
    {
        $students = Student::withCount('violations')->get();
        
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
            $sheet->setCellValue('A' . $row, $student->id);
            $sheet->setCellValue('B' . $row, $student->name);
            $sheet->setCellValue('C' . $row, $student->dept);
            $sheet->setCellValue('D' . $row, $student->year);
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
        $logs = AttendanceLog::with('student')
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Assuming we create a basic blade view for the PDF at resources/views/reports/audit.blade.php
        $pdf = Pdf::loadView('reports.audit', [
            'logs' => $logs,
            'date' => $date
        ]);
        
        return $pdf->download('Audit_Log_' . $date . '.pdf');
    }
}
