<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\SystemSetting;
use App\Models\AttendanceLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_successful_check_in_via_process_with_valid_student_id()
    {
        $student = Student::create([
            'id' => '2024-00001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'status' => 'active'
        ]);

        SystemSetting::set('kiosk_mode', 'check_in_only');

        $response = $this->withoutMiddleware()->postJson('/kiosk/process', [
            'student_id' => '2024-00001'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'action' => 'check_in',
                 ]);

        $this->assertDatabaseHas('attendance_logs', [
            'student_id' => '2024-00001',
            'action' => 'check_in'
        ]);
    }

    public function test_scan_with_non_existent_student_returns_error()
    {
        $response = $this->withoutMiddleware()->postJson('/kiosk/process', [
            'student_id' => '9999-99999'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'error',
                     'action' => 'unregistered'
                 ]);
    }

    public function test_scan_with_inactive_student_returns_error()
    {
        $student = Student::create([
            'id' => '2024-00002',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'status' => 'inactive'
        ]);

        $response = $this->withoutMiddleware()->postJson('/kiosk/process', [
            'student_id' => '2024-00002'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'error',
                     'action' => 'inactive'
                 ]);
    }

    public function test_cooldown_prevents_rapid_duplicate_scans()
    {
        $student = Student::create([
            'id' => '2024-00003',
            'first_name' => 'Jim',
            'last_name' => 'Beam',
            'status' => 'active'
        ]);

        SystemSetting::set('checkin_cooldown_minutes', 5);
        SystemSetting::set('kiosk_mode', 'check_in_only');

        $this->withoutMiddleware()->postJson('/kiosk/process', [
            'student_id' => '2024-00003'
        ]);

        $response = $this->withoutMiddleware()->postJson('/kiosk/process', [
            'student_id' => '2024-00003'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'cooldown'
                 ]);
    }

    public function test_toggle_mode_second_scan_after_cooldown_creates_check_out()
    {
        $student = Student::create([
            'id' => '2024-00004',
            'first_name' => 'Jack',
            'last_name' => 'Daniels',
            'status' => 'active'
        ]);

        SystemSetting::set('kiosk_mode', 'toggle');
        SystemSetting::set('checkin_cooldown_minutes', 5);

        AttendanceLog::create([
            'student_id' => $student->id,
            'action' => 'check_in',
            'logged_at' => now()->subMinutes(10)
        ]);

        $response = $this->withoutMiddleware()->postJson('/kiosk/process', [
            'student_id' => '2024-00004'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'action' => 'check_out'
                 ]);
    }

    public function test_kiosk_occupancy_endpoint_returns_expected_json_structure()
    {
        SystemSetting::set('max_occupancy', 150);
        
        $response = $this->withoutMiddleware()->getJson('/kiosk/occupancy');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'inside',
                     'max'
                 ])
                 ->assertJson([
                     'max' => 150
                 ]);
    }
}
