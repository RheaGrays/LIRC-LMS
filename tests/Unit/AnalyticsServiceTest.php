<?php

namespace Tests\Unit;

use App\Models\AcademicDepartment;
use App\Models\AttendanceLog;
use App\Models\Student;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_calculates_and_caches_analytics_data_for_different_periods()
    {
        $dept = AcademicDepartment::create([
            'level' => 'college',
            'name' => 'College of Computing Studies',
            'code' => 'CCS',
        ]);

        $student = Student::create([
            'id' => '2026-0001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'department_id' => $dept->id,
            'year_level' => '1st Year',
            'patron_category' => 'Student',
            'status' => 'active',
        ]);

        AttendanceLog::create([
            'student_id' => $student->id,
            'action' => 'check_in',
            'logged_at' => now(),
        ]);

        $service = new AnalyticsService();

        // 1. Test today
        $todayData = $service->getAnalyticsData('today');
        $this->assertArrayHasKey('traffic', $todayData);
        $this->assertArrayHasKey('departments', $todayData);
        $this->assertArrayHasKey('summary', $todayData);
        $this->assertEquals(1, $todayData['summary']['total_patrons']);
        $this->assertEquals(1, $todayData['summary']['today_traffic']);
        $this->assertEquals('John Doe', $todayData['summary']['top_patron']);
        $this->assertContains('CCS', $todayData['departments']['labels']);

        // 2. Test week
        $weekData = $service->getAnalyticsData('week');
        $this->assertNotEmpty($weekData['traffic']['labels']);
        $this->assertNotEmpty($weekData['traffic']['values']);

        // 3. Test month
        $monthData = $service->getAnalyticsData('month');
        $this->assertNotEmpty($monthData['traffic']['labels']);

        // 4. Test year
        $yearData = $service->getAnalyticsData('year');
        $this->assertNotEmpty($yearData['traffic']['labels']);
    }
}
