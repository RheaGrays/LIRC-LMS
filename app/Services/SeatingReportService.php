<?php

namespace App\Services;

use App\Models\SectionLog;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SeatingReportService
{
    /**
     * Generate and stream an Excel report matrix containing:
     * - Rows: Days of the period (e.g. Day 1 to Day 31)
     * - Columns: 1-hour intervals from 7:00 AM - 8:00 AM through 6:00 PM - 7:00 PM
     * - Multiple sheets: Overall summary ("All Sections") plus one sheet per section.
     *
     * @param string|null $month Month in 'Y-m' format (e.g. '2026-09')
     * @param string|null $startDate 'Y-m-d'
     * @param string|null $endDate 'Y-m-d'
     * @param string|null $sectionCode Optional section code, or 'all'
     * @return StreamedResponse
     */
    public function exportHourlyMatrix(?string $month = null, ?string $startDate = null, ?string $endDate = null, ?string $sectionCode = 'all'): StreamedResponse
    {
        // 1. Determine date boundaries
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end   = Carbon::parse($endDate)->endOfDay();
            $periodLabel = $start->format('M d, Y') . ' — ' . $end->format('M d, Y');
            $filePeriod  = $start->format('Ymd') . '_' . $end->format('Ymd');
        } else {
            $month = $month ?: now()->format('Y-m');
            $start = Carbon::parse($month . '-01')->startOfMonth();
            $end   = Carbon::parse($month . '-01')->endOfMonth();
            $periodLabel = $start->format('F Y');
            $filePeriod  = $start->format('Y_m');
        }

        // 2. Define the 12 one-hour time slots (7 AM - 7 PM)
        $hours = [];
        for ($h = 7; $h <= 18; $h++) {
            $hStart = Carbon::createFromTime($h, 0)->format('g:i A');
            $hEnd   = Carbon::createFromTime($h + 1, 0)->format('g:i A');
            $hours[$h] = "{$hStart} - {$hEnd}";
        }

        // 3. Resolve sections
        $allSections = $this->getAllSections();

        // 4. Query logs for the date range
        $query = SectionLog::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()], 'and', false)
            ->whereBetween('hour', [7, 18], 'and', false);

        if ($sectionCode && $sectionCode !== 'all') {
            $query->where('section_code', '=', $sectionCode, 'and');
        }

        $logs = $query->get(['section_code', 'section_name', 'date', 'hour', 'occupied']);

        // Organize matrices: total and per-section
        $totalMatrix = [];
        $sectionMatrices = [];

        foreach ($logs as $log) {
            $d = Carbon::parse($log->date)->toDateString();
            $h = (int) $log->hour;
            $occ = (int) $log->occupied;
            $sec = $log->section_code;

            $totalMatrix[$d][$h] = ($totalMatrix[$d][$h] ?? 0) + $occ;
            $sectionMatrices[$sec][$d][$h] = ($sectionMatrices[$sec][$d][$h] ?? 0) + $occ;

            if (!isset($allSections[$sec]) && !empty($log->section_name)) {
                $allSections[$sec] = $log->section_name;
            }
        }

        // 5. Build Spreadsheet
        $spreadsheet = new Spreadsheet();
        $existingSheetTitles = [];

        if ($sectionCode && $sectionCode !== 'all') {
            // Single section export
            $secName = $allSections[$sectionCode] ?? $sectionCode;
            $sheet = $spreadsheet->getActiveSheet();
            $sheetTitle = $this->sanitizeSheetTitle($secName, $existingSheetTitles);
            $sheet->setTitle($sheetTitle);

            $sectionLabel = "{$secName} ({$sectionCode})";
            $matrix = $sectionMatrices[$sectionCode] ?? [];

            $this->populateSheet($sheet, $sectionLabel, $matrix, $start, $end, $hours, $periodLabel);
        } else {
            // Multi-sheet export: Overall Total + Sheet per section
            // Sheet 1: All Sections
            $sheet = $spreadsheet->getActiveSheet();
            $sheetTitle = $this->sanitizeSheetTitle('All Sections', $existingSheetTitles);
            $sheet->setTitle($sheetTitle);

            $this->populateSheet(
                $sheet,
                'All Library Sections (Total Seating)',
                $totalMatrix,
                $start,
                $end,
                $hours,
                $periodLabel
            );

            // Subsequent sheets: One sheet per section
            foreach ($allSections as $secCode => $secName) {
                $sheet = $spreadsheet->createSheet();
                $sheetTitle = $this->sanitizeSheetTitle($secName, $existingSheetTitles);
                $sheet->setTitle($sheetTitle);

                $sectionLabel = "{$secName} ({$secCode})";
                $matrix = $sectionMatrices[$secCode] ?? [];

                $this->populateSheet(
                    $sheet,
                    $sectionLabel,
                    $matrix,
                    $start,
                    $end,
                    $hours,
                    $periodLabel
                );
            }

            // Default focus to the first sheet
            $spreadsheet->setActiveSheetIndex(0);
        }

        // Output streaming
        $safeSection = preg_replace('/[^a-zA-Z0-9_-]/', '_', $sectionCode ?? 'all');
        $filename = "Seating_Report_{$safeSection}_{$filePeriod}.xlsx";
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer, $spreadsheet) {
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Populate a worksheet with the standard CJC hourly seating report layout.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param string $sectionLabel
     * @param array $matrix [$dateStr][$hour] = count
     * @param Carbon $start
     * @param Carbon $end
     * @param array $hours [$hour => '7:00 AM - 8:00 AM']
     * @param string $periodLabel
     */
    protected function populateSheet(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        string $sectionLabel,
        array $matrix,
        Carbon $start,
        Carbon $end,
        array $hours,
        string $periodLabel
    ): void {
        // Document Title & Metadata Header
        $sheet->setCellValue('A1', 'COR JESU COLLEGE — LIBRARY & INFORMATION RESOURCE CENTER');
        $sheet->setCellValue('A2', 'HOURLY SEATING HEADCOUNT REPORT MATRIX');
        $sheet->setCellValue('A3', "Section: {$sectionLabel}  |  Period: {$periodLabel}  |  Generated: " . now()->format('M d, Y h:i A'));

        $sheet->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0F2744'));
        $sheet->getStyle('A2')->getFont()->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('C41E2A'));
        $sheet->getStyle('A3')->getFont()->setSize(10)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        // Table Column Headers (Row 5)
        $headerRow = 5;
        $sheet->setCellValue('A' . $headerRow, 'Date');
        $sheet->setCellValue('B' . $headerRow, 'Day');

        $colLetter = 'C';
        foreach ($hours as $h => $label) {
            $sheet->setCellValue($colLetter . $headerRow, $label);
            $colLetter++;
        }
        $sheet->setCellValue($colLetter . $headerRow, 'Daily Total');
        $lastCol = $colLetter;

        // Header Styling (Navy background with white bold text)
        $headerRange = "A{$headerRow}:{$lastCol}{$headerRow}";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F2744');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);

        // Fill Data Rows (One row per day in range)
        $currentRow = 6;
        $curr = $start->copy();

        $hourTotals = array_fill_keys(array_keys($hours), 0);
        $grandTotal = 0;
        $dayCount   = 0;

        while ($curr->lte($end)) {
            $dateStr = $curr->toDateString();
            $dayName = $curr->format('D');
            $isWeekend = $curr->isWeekend();

            $sheet->setCellValue("A{$currentRow}", $curr->format('M d, Y'));
            $sheet->setCellValue("B{$currentRow}", $dayName);

            $colLetter = 'C';
            $dailySum = 0;

            foreach ($hours as $h => $label) {
                $val = $matrix[$dateStr][$h] ?? 0;
                $sheet->setCellValue("{$colLetter}{$currentRow}", $val);
                $dailySum += $val;
                $hourTotals[$h] += $val;
                $colLetter++;
            }

            // Daily Total
            $sheet->setCellValue("{$colLetter}{$currentRow}", $dailySum);
            $sheet->getStyle("{$colLetter}{$currentRow}")->getFont()->setBold(true);

            // Row styling
            $rowRange = "A{$currentRow}:{$lastCol}{$currentRow}";
            $sheet->getStyle("A{$currentRow}:B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$currentRow}:{$lastCol}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Weekend or alternating row background
            if ($isWeekend) {
                $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
            } elseif ($currentRow % 2 === 0) {
                $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
            }

            $grandTotal += $dailySum;
            $dayCount++;
            $currentRow++;
            $curr->addDay();
        }

        // Summary Rows: Total and Average
        // Total Row
        $sheet->setCellValue("A{$currentRow}", 'TOTAL');
        $sheet->setCellValue("B{$currentRow}", "{$dayCount} days");
        $colLetter = 'C';
        foreach ($hours as $h => $label) {
            $sheet->setCellValue("{$colLetter}{$currentRow}", $hourTotals[$h]);
            $colLetter++;
        }
        $sheet->setCellValue("{$colLetter}{$currentRow}", $grandTotal);

        $totalRange = "A{$currentRow}:{$lastCol}{$currentRow}";
        $sheet->getStyle($totalRange)->getFont()->setBold(true);
        $sheet->getStyle($totalRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle("A{$currentRow}:B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C{$currentRow}:{$lastCol}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $currentRow++;

        // Average Row
        $sheet->setCellValue("A{$currentRow}", 'AVERAGE');
        $sheet->setCellValue("B{$currentRow}", 'Per Day');
        $colLetter = 'C';
        foreach ($hours as $h => $label) {
            $avg = $dayCount > 0 ? round($hourTotals[$h] / $dayCount, 1) : 0;
            $sheet->setCellValue("{$colLetter}{$currentRow}", $avg);
            $colLetter++;
        }
        $overallAvg = $dayCount > 0 ? round($grandTotal / $dayCount, 1) : 0;
        $sheet->setCellValue("{$colLetter}{$currentRow}", $overallAvg);

        $avgRange = "A{$currentRow}:{$lastCol}{$currentRow}";
        $sheet->getStyle($avgRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0F2744'));
        $sheet->getStyle($avgRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE9D5DA');
        $sheet->getStyle("A{$currentRow}:B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C{$currentRow}:{$lastCol}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Thin borders for the whole table
        $fullTableRange = "A5:{$lastCol}{$currentRow}";
        $sheet->getStyle($fullTableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('CBD5E1');

        // Auto-fit column widths
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(8);
        $colLetter = 'C';
        foreach ($hours as $h => $label) {
            $sheet->getColumnDimension($colLetter)->setWidth(17);
            $colLetter++;
        }
        $sheet->getColumnDimension($lastCol)->setWidth(15);
    }

    /**
     * Retrieve all available library sections from the database and settings.
     *
     * @return array<string, string> Array mapping section_code => section_name
     */
    public function getAllSections(): array
    {
        $sections = [];

        // 1. From SectionLog: get latest recorded section_name for each section_code
        $latestIds = SectionLog::query()
            ->selectRaw('MAX(id) as id', [])
            ->groupBy('section_code')
            ->pluck('id');

        if ($latestIds->isNotEmpty()) {
            $rows = SectionLog::query()
                ->whereIn('id', $latestIds, 'and', false)
                ->orderBy('section_code')
                ->get();

            foreach ($rows as $r) {
                $code = trim($r->section_code);
                $name = trim($r->section_name);
                if ($code !== '') {
                    $sections[$code] = $name ?: $code;
                }
            }
        }

        // 2. From SystemSetting library_sections (fallback/supplement)
        $configured = \App\Models\SystemSetting::get('library_sections', [
            'General Reading', 'Discussion Room', 'Internet Section', 'Periodicals'
        ]);

        if (is_array($configured)) {
            foreach ($configured as $name) {
                $name = trim($name);
                if ($name === '') {
                    continue;
                }
                $code = strtoupper(substr($name, 0, 3));
                if (!isset($sections[$code])) {
                    $sections[$code] = $name;
                }
            }
        }

        return $sections;
    }

    /**
     * Sanitize a sheet title to conform to Excel constraints:
     * - Must not exceed 31 characters
     * - Must not contain characters: \ / ? * : [ ]
     * - Must not be blank
     * - Must be unique within the workbook (case-insensitive)
     *
     * @param string $title
     * @param array $existingTitles Lowercase list of already assigned sheet titles
     * @return string
     */
    protected function sanitizeSheetTitle(string $title, array &$existingTitles): string
    {
        $clean = preg_replace('/[\\\\\/:\*\?\[\]]/', ' ', $title);
        $clean = trim(preg_replace('/\s+/', ' ', $clean));
        if ($clean === '') {
            $clean = 'Section';
        }

        $base = mb_substr($clean, 0, 31);
        $finalTitle = $base;
        $counter = 2;

        while (in_array(mb_strtolower($finalTitle), $existingTitles, true)) {
            $suffix = " ({$counter})";
            $maxBaseLen = 31 - mb_strlen($suffix);
            $finalTitle = mb_substr($base, 0, $maxBaseLen) . $suffix;
            $counter++;
        }

        $existingTitles[] = mb_strtolower($finalTitle);
        return $finalTitle;
    }
}
