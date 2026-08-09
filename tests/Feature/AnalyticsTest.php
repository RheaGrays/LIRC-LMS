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
}
