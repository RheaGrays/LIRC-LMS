<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test root route redirects to admin login.
     */
    public function test_root_redirects_to_admin_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/kiosk');
    }

    /**
     * Test admin login page loads successfully.
     */
    public function test_admin_login_page_is_accessible(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
    }

    /**
     * Test kiosk display page loads successfully.
     */
    public function test_kiosk_page_is_accessible(): void
    {
        $response = $this->get('/kiosk');
        $response->assertStatus(200);
    }

    /**
     * Test registration page loads successfully.
     */
    public function test_registration_page_is_accessible(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }
}
