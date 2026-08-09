<?php

namespace Tests\Feature;

use App\Models\AcademicDepartment;
use App\Models\AttendanceLog;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $dept = AcademicDepartment::create([
            'level' => 'college',
            'name' => 'College of Computing Studies',
        ]);

        $this->student = Student::create([
            'id' => '2026-0001',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'department_id' => $dept->id,
            'year_level' => '3rd Year',
            'patron_category' => 'Student',
            'status' => 'active',
        ]);
    }

    #[Test]
    public function it_can_lookup_student_by_id()
    {
        $response = $this->postJson('/kiosk/lookup', [
            'student_id' => '2026-0001',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'found' => true,
                'denied' => false,
                'student' => [
                    'id' => '2026-0001',
                    'name' => 'Juan Dela Cruz',
                ],
            ]);
    }

    #[Test]
    public function it_returns_not_found_for_unregistered_student_lookup()
    {
        $response = $this->postJson('/kiosk/lookup', [
            'student_id' => '9999-9999',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'found' => false,
            ]);
    }

    #[Test]
    public function it_processes_checkin_scan_successfully()
    {
        $response = $this->postJson('/kiosk/process', [
            'student_id' => '2026-0001',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'action' => 'check_in',
                'message' => 'Successfully checked in.',
            ]);

        $this->assertDatabaseHas('attendance_logs', [
            'student_id' => '2026-0001',
            'action' => 'check_in',
        ]);
    }

    #[Test]
    public function it_enforces_cooldown_buffer_on_duplicate_scans()
    {
        // First scan
        $this->postJson('/kiosk/process', ['student_id' => '2026-0001']);
        $this->assertEquals(1, AttendanceLog::where('student_id', '=', '2026-0001', 'and')->count('*'));

        // Duplicate scan within 5 minutes
        $response = $this->postJson('/kiosk/process', ['student_id' => '2026-0001']);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'action' => 'check_in',
            ]);

        // Verify only 1 log entry exists in database
        $this->assertEquals(1, AttendanceLog::where('student_id', '=', '2026-0001', 'and')->count('*'));
    }

    #[Test]
    public function it_supports_explicit_checkout_action()
    {
        $response = $this->postJson('/kiosk/process', [
            'student_id' => '2026-0001',
            'action' => 'check_out',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'action' => 'check_out',
                'message' => 'Successfully checked out.',
            ]);

        $this->assertDatabaseHas('attendance_logs', [
            'student_id' => '2026-0001',
            'action' => 'check_out',
        ]);
    }

    #[Test]
    public function it_denies_remote_kiosk_requests_when_token_configured_and_invalid()
    {
        config(['app.kiosk_api_token' => 'secret_token_123']);

        // Remote IP request without token
        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.100'])
            ->postJson('/kiosk/process', ['student_id' => '2026-0001']);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid or missing kiosk authentication token.',
            ]);

        // Remote IP request with valid header token
        $responseWithToken = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.100'])
            ->withHeaders(['X-Kiosk-Token' => 'secret_token_123'])
            ->postJson('/kiosk/process', ['student_id' => '2026-0001']);

        $responseWithToken->assertStatus(200);
    }
}
