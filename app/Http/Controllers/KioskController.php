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

        // Map specific images to their correct labels
        $facilityMap = [
            'Picture1' => [
                'title' => 'Baggage Counter',
                'badge' => 'Service Area',
                'description' => 'Secure storage for your personal belongings before entering the library premises.'
            ]
        ];

        // Dynamically load all facility images from public/Facilities
        $slideshowImages = [];
        $facilitiesPath = public_path('Facilities');
        
        if (is_dir($facilitiesPath)) {
            $files = \Illuminate\Support\Facades\File::files($facilitiesPath);
            foreach ($files as $file) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $filename = $file->getFilename();
                    $name = $file->getFilenameWithoutExtension();
                    
                    if (isset($facilityMap[$name])) {
                        $title = $facilityMap[$name]['title'];
                        $badge = $facilityMap[$name]['badge'];
                        $description = $facilityMap[$name]['description'];
                    } else {
                        $title = 'LIRC Facility';
                        $badge = 'Learning Space';
                        $description = 'Experience our state-of-the-art library facilities designed to foster academic excellence and collaborative learning.';
                        
                        if (stripos($name, 'LIRC') !== false) {
                            $title = 'LIRC Main Area';
                            $badge = 'General Space';
                        } else {
                            $num = preg_replace('/[^0-9]/', '', $name);
                            if ($num) {
                                $title = 'Learning Zone ' . $num;
                            }
                        }
                    }

                    $slideshowImages[] = [
                        'src' => '/Facilities/' . $filename,
                        'badge' => $badge,
                        'title' => $title,
                        'description' => $description
                    ];
                }
            }
        }

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

        return view('kiosk.index', compact('settings', 'collections', 'defaultFacilities', 'slideshowImages'));
    }
}
