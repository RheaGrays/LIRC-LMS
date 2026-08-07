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
        $slideshowImages = collect([
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
        ])->filter(function ($slide) {
            return file_exists(public_path($slide['src']));
        })->values()->toArray();

        // Fallback if no images exist to prevent broken UI
        if (empty($slideshowImages)) {
            $slideshowImages = [
                [
                    'src' => 'https://ui-avatars.com/api/?name=LIRC&background=0f2744&color=fff&size=1024',
                    'badge' => 'Welcome',
                    'title' => 'Welcome to LIRC',
                    'description' => 'Your gateway to knowledge and innovation.'
                ]
            ];
        }

        $splashShown = session('splash_shown', false);
        session(['splash_shown' => true]);

        return view('kiosk.index', compact('settings', 'collections', 'defaultFacilities', 'slideshowImages', 'splashShown'));
    }
}
