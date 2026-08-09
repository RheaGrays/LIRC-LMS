<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'full_name' => 'Super Admin',
            'email' => 'admin@cjc.edu.ph',
            'password' => bcrypt('password123'),
            'role' => 'Super Admin',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function it_supports_soft_deletes_on_students()
    {
        $student = Student::create([
            'id' => '2026-0002',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'patron_category' => 'Student',
            'status' => 'active',
        ]);

        $student->delete();

        $this->assertSoftDeleted('students', ['id' => '2026-0002']);
        $this->assertNull(Student::find('2026-0002', ['*']));
        $this->assertNotNull(Student::withTrashed()->find('2026-0002', ['*']));
    }

    #[Test]
    public function it_sanitizes_dangerous_formula_characters_in_excel_export()
    {
        Student::create([
            'id' => '2026-0003',
            'first_name' => '=SUM(A1:A10)',
            'last_name' => '@MaliciousUser',
            'patron_category' => 'Student',
            'status' => 'active',
        ]);

        $reportService = new ReportService();
        $reflection = new \ReflectionClass($reportService);
        $method = $reflection->getMethod('sanitizeForExcel');
        $method->setAccessible(true);

        $sanitizedFirstName = $method->invoke($reportService, '=SUM(A1:A10)');
        $sanitizedLastName = $method->invoke($reportService, '@MaliciousUser');

        $this->assertEquals("'=SUM(A1:A10)", $sanitizedFirstName);
        $this->assertEquals("'@MaliciousUser", $sanitizedLastName);
    }

    #[Test]
    public function it_imports_all_students_without_silent_truncation()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['ID', 'Last Name', 'First Name', 'Middle Name', 'Category', 'Department', 'Program', 'Year', 'Email'], null, 'A1');

        for ($i = 1; $i <= 20; $i++) {
            $sheet->fromArray([
                "2026-{$i}",
                "LastName{$i}",
                "FirstName{$i}",
                'M',
                'Student',
                'CCS',
                'BSIT',
                '1st Year',
                "student{$i}@cjc.edu.ph"
            ], null, 'A' . ($i + 1));
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'import_test') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $file = new UploadedFile($tempPath, 'students.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($this->admin, 'admin')
            ->post('/admin/students/import', ['file' => $file]);

        $response->assertRedirect();
        $this->assertEquals(20, Student::count('*'));

        @unlink($tempPath);
    }
}
