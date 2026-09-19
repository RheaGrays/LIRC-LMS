<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SectionLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        // Create sample section logs
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
    }

    #[Test]
    public function it_exports_hourly_seating_matrix_to_excel()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/statistics/export-hourly?month=2026-09&section_code=GEN');

        $response->assertStatus(200);
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('content-type')
        );
        $this->assertStringContainsString('Seating_Report_GEN_2026_09.xlsx', $response->headers->get('content-disposition'));
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
    }
}
