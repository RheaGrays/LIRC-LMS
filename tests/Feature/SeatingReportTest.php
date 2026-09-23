<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SectionLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeatingReportTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'full_name' => 'Seating Admin',
            'email' => 'seating_admin@cjc.edu.ph',
            'password' => bcrypt('password123'),
            'role' => 'Super Admin',
            'is_active' => true,
        ]);

        // Create sample section logs for multiple sections
        SectionLog::create([
            'section_code' => 'GEN',
            'section_name' => 'General Reading Area',
            'date' => '2026-09-10',
            'hour' => 8,
            'occupied' => 25,
            'reserved' => 0,
            'available' => 25,
            'total_capacity' => 50,
        ]);

        SectionLog::create([
            'section_code' => 'GEN',
            'section_name' => 'General Reading Area',
            'date' => '2026-09-10',
            'hour' => 14,
            'occupied' => 42,
            'reserved' => 0,
            'available' => 8,
            'total_capacity' => 50,
        ]);

        SectionLog::create([
            'section_code' => 'DIS',
            'section_name' => 'Discussion Room',
            'date' => '2026-09-10',
            'hour' => 8,
            'occupied' => 15,
            'reserved' => 0,
            'available' => 5,
            'total_capacity' => 20,
        ]);
    }

    #[Test]
    public function it_exports_hourly_seating_matrix_for_single_section_to_excel()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/statistics/export-hourly?month=2026-09&section_code=GEN');

        $response->assertStatus(200);
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('content-type')
        );
        $this->assertStringContainsString('Seating_Report_GEN_2026_09.xlsx', $response->headers->get('content-disposition'));

        // Load spreadsheet and verify single sheet
        $content = $response->streamedContent();
        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx_single');
        file_put_contents($tempPath, $content);

        $reader = new Xlsx();
        $spreadsheet = $reader->load($tempPath);

        $this->assertEquals(1, $spreadsheet->getSheetCount());
        $this->assertEquals('General Reading Area', $spreadsheet->getSheet(0)->getTitle());

        unlink($tempPath);
    }

    #[Test]
    public function it_exports_multi_sheet_hourly_seating_matrix_per_section_when_all_is_selected()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/statistics/export-hourly?month=2026-09&section_code=all');

        $response->assertStatus(200);
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('content-type')
        );
        $this->assertStringContainsString('Seating_Report_all_2026_09.xlsx', $response->headers->get('content-disposition'));

        // Load spreadsheet and verify multiple sheets
        $content = $response->streamedContent();
        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx_multi');
        file_put_contents($tempPath, $content);

        $reader = new Xlsx();
        $spreadsheet = $reader->load($tempPath);

        $sheetNames = $spreadsheet->getSheetNames();

        // Must have at least "All Sections" + individual sections
        $this->assertGreaterThan(1, count($sheetNames));
        $this->assertContains('All Sections', $sheetNames);
        $this->assertContains('General Reading Area', $sheetNames);
        $this->assertContains('Discussion Room', $sheetNames);

        // Verify "All Sections" sheet is first and active
        $this->assertEquals('All Sections', $sheetNames[0]);
        $this->assertEquals(0, $spreadsheet->getActiveSheetIndex());

        // Check content on "All Sections": row 1 title, and row 3 label
        $allSheet = $spreadsheet->getSheetByName('All Sections');
        $this->assertEquals('COR JESU COLLEGE — LIBRARY & INFORMATION RESOURCE CENTER', $allSheet->getCell('A1')->getValue());
        $this->assertEquals('HOURLY SEATING HEADCOUNT REPORT MATRIX', $allSheet->getCell('A2')->getValue());
        $this->assertStringContainsString('All Library Sections (Total Seating)', $allSheet->getCell('A3')->getValue());

        // Check content on "General Reading Area" sheet:
        $genSheet = $spreadsheet->getSheetByName('General Reading Area');
        $this->assertStringContainsString('General Reading Area (GEN)', $genSheet->getCell('A3')->getValue());

        // Check content on "Discussion Room" sheet:
        $disSheet = $spreadsheet->getSheetByName('Discussion Room');
        $this->assertStringContainsString('Discussion Room (DIS)', $disSheet->getCell('A3')->getValue());

        unlink($tempPath);
    }

    #[Test]
    public function it_exports_hourly_seating_matrix_with_custom_date_range()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/statistics/export-hourly?start_date=2026-09-01&end_date=2026-09-15&section_code=all');

        $response->assertStatus(200);
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('content-type')
        );
        $this->assertStringContainsString('Seating_Report_all_20260901_20260915.xlsx', $response->headers->get('content-disposition'));

        $content = $response->streamedContent();
        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx_custom');
        file_put_contents($tempPath, $content);

        $reader = new Xlsx();
        $spreadsheet = $reader->load($tempPath);

        $this->assertGreaterThan(1, $spreadsheet->getSheetCount());
        $this->assertEquals('All Sections', $spreadsheet->getSheet(0)->getTitle());

        unlink($tempPath);
    }
}
