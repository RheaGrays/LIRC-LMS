<?php

namespace App\Http\Controllers;

use App\Models\LibraryCollection;
use Illuminate\Http\Request;

class KioskController extends Controller
{
    public function index()
    {
        $settings    = \App\Models\SystemSetting::allSettings();
        $collections = LibraryCollection::active();

        $defaultFacilities = [
            [
                'badge'       => 'E-Library',
                'badge_color' => '#c41e2a',
                'title'       => 'E-Library & Digital Station',
                'description' => 'High-speed terminals with access to online research databases.',
                'image'       => '/images/facility1.jpg',
            ],
            [
                'badge'       => 'Study Hub',
                'badge_color' => '#0f2744',
                'title'       => 'Quiet Study & Discussion Area',
                'description' => 'Comfortable study spaces for collaborative learning and reading.',
                'image'       => '/images/facility2.jpg',
            ],
        ];

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

        return view('kiosk.index', compact('settings', 'collections', 'defaultFacilities', 'slideshowImages'));
    }
}
