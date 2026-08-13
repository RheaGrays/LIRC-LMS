<?php

namespace Tests\Unit;

use App\Models\Student;
use App\Models\AttendanceLog;
use App\Models\SystemSetting;
use App\Services\OccupancyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OccupancyServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        
        SystemSetting::set('max_occupancy', 100);
    }

    public function test_returns_0_when_no_logs_exist_today()
    {
        $result = OccupancyService::today();
        
        $this->assertEquals(0, $result['inside']);
        $this->assertEquals(100, $result['max']);
    }

    public function test_counts_only_students_whose_last_action_is_check_in()
    {
        $student1 = Student::create(['id' => '1', 'first_name' => 'A', 'last_name' => 'A']);
        $student2 = Student::create(['id' => '2', 'first_name' => 'B', 'last_name' => 'B']);

        AttendanceLog::create([
            'student_id' => $student1->id,
            'action' => 'check_in',
            'logged_at' => now()
        ]);
        
        AttendanceLog::create([
            'student_id' => $student2->id,
            'action' => 'check_in',
            'logged_at' => now()
        ]);

        $result = OccupancyService::today();
        $this->assertEquals(2, $result['inside']);
    }

    public function test_student_who_checked_out_is_not_counted_as_inside()
    {
        $student1 = Student::create(['id' => '1', 'first_name' => 'A', 'last_name' => 'A']);

        // Checked in
        AttendanceLog::create([
            'student_id' => $student1->id,
            'action' => 'check_in',
            'logged_at' => now()->subMinutes(10)
        ]);
        
        // Checked out
        AttendanceLog::create([
            'student_id' => $student1->id,
            'action' => 'check_out',
            'logged_at' => now()
        ]);

        $result = OccupancyService::today();
        $this->assertEquals(0, $result['inside']);
    }
}
