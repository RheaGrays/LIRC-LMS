<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KioskController extends Controller
{
    public function index()
    {
        $settings = \App\Models\SystemSetting::allSettings();
        
        // Facility images for the cinematic full-screen slider
        $slideshowImages = [
            [
                'src' => '/discussion_room.jpg',
                'badge' => 'Collaborative Space',
                'title' => 'Discussion Room',
                'description' => 'A vibrant environment for group study, brainstorming sessions, and team collaborations.'
            ],
            [
                'src' => '/quiet_zone.jpg',
                'badge' => 'Silent Area',
                'title' => 'Quiet Zone',
                'description' => 'A distraction-free zone dedicated to deep focus, reading, and individual study.'
            ],
            [
                'src' => '/bg.jpg',
                'badge' => 'Main Campus',
                'title' => 'Cor Jesu College',
                'description' => 'Excellence in education, rooted in faith and dedicated to the community.'
            ]
        ];
        
        return view('kiosk.index', compact('settings', 'slideshowImages'));
    }
}
