<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MobilePhotoSyncTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_photo_pairing_session()
    {
        $response = $this->postJson('/api/register/photo-session/create');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'session_id',
                'mobile_url',
            ]);
    }

    #[Test]
    public function it_uploads_and_checks_photo_for_session()
    {
        $createRes = $this->postJson('/api/register/photo-session/create');
        $sessionId = $createRes->json('session_id');

        $samplePhoto = 'data:image/jpeg;base64,' . base64_encode('fake_image_bytes');

        $uploadRes = $this->postJson('/api/register/photo-session/upload', [
            'session_id'   => $sessionId,
            'photoDataUrl' => $samplePhoto,
        ]);

        $uploadRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $checkRes = $this->getJson("/api/register/photo-session/check/{$sessionId}");

        $checkRes->assertStatus(200)
            ->assertJson([
                'status'       => 'completed',
                'photoDataUrl' => $samplePhoto,
            ]);
    }
}
