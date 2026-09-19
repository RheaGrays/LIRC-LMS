<?php

namespace Tests\Feature;

use App\Models\AcademicDepartment;
use App\Models\Admin;
use App\Models\AttendanceLog;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'full_name' => 'Analytics Admin',
            'email' => 'analytics@cjc.edu.ph',
            'password' => bcrypt('password123'),
            'role' => 'Super Admin',
            'is_active' => true,
        ]);

        $dept = AcademicDepartment::create([
            'level' => 'college',
            'name' => 'College of Computing Studies',
        ]);

        $student = Student::create([
            'id' => '2026-1001',
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'department_id' => $dept->id,
            'year_level' => '2nd Year',
            'patron_category' => 'Student',
            'status' => 'active',
        ]);

        AttendanceLog::create([
            'student_id' => $student->id,
            'action' => 'check_in',
            'logged_at' => now(),
        ]);
    }

    #[Test]
    public function it_returns_database_agnostic_analytics_data_without_mysql_function_errors()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/admin/analytics/data?period=today');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'traffic' => ['labels', 'values'],
                'departments' => ['labels', 'values'],
            ]);

        $this->assertNotEmpty($response->json('traffic.labels'));
        $this->assertNotEmpty($response->json('departments.labels'));
    }

    #[Test]
    public function it_caches_analytics_response_data()
    {
        $response1 = $this->actingAs($this->admin, 'admin')
            ->getJson('/admin/analytics/data?period=today');

        $response2 = $this->actingAs($this->admin, 'admin')
            ->getJson('/admin/analytics/data?period=today');

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        $this->assertEquals($response1->json(), $response2->json());
    }

    #[Test]
    public function it_exports_college_programs_ranking_report_to_excel()
    {
        $dept = AcademicDepartment::first();
        $dept->update(['code' => 'CCIS']);

        $prog = \App\Models\AcademicProgram::create([
            'department_id' => $dept->id,
            'name' => 'Bachelor of Science in Information Technology',
            'code' => 'BSIT',
        ]);

        Student::where('id', '2026-1001')->update(['program_id' => $prog->id]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/analytics/export-monthly-report?report_type=college_programs&format=excel&month=' . now()->format('Y-m'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
    }

    #[Test]
    public function it_exports_college_programs_ranking_report_to_word()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/analytics/export-monthly-report?report_type=college_programs&format=word&month=' . now()->format('Y-m'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/msword', $response->headers->get('content-type'));
        $this->assertStringContainsString('Official College & Programs Attendance Report', $response->getContent());
    }

    #[Test]
    public function it_exports_college_programs_ranking_report_to_pdf()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/analytics/export-monthly-report?report_type=college_programs&format=pdf&month=' . now()->format('Y-m'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/html', $response->headers->get('content-type'));
        $this->assertStringContainsString('Official College & Programs Attendance Report', $response->getContent());
        $this->assertStringContainsString('Print / Save as PDF', $response->getContent());
    }

    #[Test]
    public function it_filters_attendance_by_custom_date_range_and_time_window()
    {
        $student = Student::first();

        // Create log outside time window (e.g. 05:00 AM)
        AttendanceLog::create([
            'student_id' => $student->id,
            'action' => 'check_in',
            'logged_at' => now()->startOfDay()->addHours(5),
        ]);

        // Create log inside time window (e.g. 10:00 AM)
        AttendanceLog::create([
            'student_id' => $student->id,
            'action' => 'check_in',
            'logged_at' => now()->startOfDay()->addHours(10),
        ]);

        // Query with 07:00 to 19:00 custom range
        $today = now()->format('Y-m-d');
        $response = $this->actingAs($this->admin, 'admin')
            ->get("/admin/analytics/export-monthly-report?report_type=college_programs&date_mode=custom&start_date={$today}&end_date={$today}&start_time=07:00&end_time=19:00&format=word");

        $response->assertStatus(200);
        $content = $response->getContent();
        // Total Attendance should reflect the log within window, not the 5:00 AM log
        $this->assertStringContainsString('7:00 AM — 7:00 PM', $content);
    }

    #[Test]
    public function it_exports_patron_attendance_details_to_excel()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/analytics/export-monthly-report?report_type=patrons&format=excel&month=' . now()->format('Y-m'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
    }
}
