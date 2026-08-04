<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $admin    = Auth::guard('admin')->user();
        $settings = SystemSetting::allSettings();

        // Defaults
        $defaults = [
            'active_term'           => '2025-2026-2',
            'idle_timeout'          => 60,
            'max_occupancy'         => 200,
            'show_occupancy'        => true,
            'enable_webcam'         => false,
            'sound_on_checkin'      => false,
            'alert_capacity'        => true,
            'alert_daily_summary'   => false,
            'alert_repeated_denied' => true,
        ];

        $settings = array_merge($defaults, $settings);

        return view('admin.settings.index', compact('admin', 'settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'active_term'           => ['required', 'string'],
            'idle_timeout'          => ['required', 'integer', 'min:10', 'max:3600'],
            'max_occupancy'         => ['required', 'integer', 'min:1', 'max:10000'],
            'show_occupancy'        => ['boolean'],
            'enable_webcam'         => ['boolean'],
            'sound_on_checkin'      => ['boolean'],
            'alert_capacity'        => ['boolean'],
            'alert_daily_summary'   => ['boolean'],
            'alert_repeated_denied' => ['boolean'],
        ]);

        // Booleans default to false if unchecked
        foreach (['show_occupancy','enable_webcam','sound_on_checkin','alert_capacity','alert_daily_summary','alert_repeated_denied'] as $key) {
            $data[$key] = $request->boolean($key);
        }

        foreach ($data as $key => $value) {
            SystemSetting::set($key, $value);
        }

        return back()->with('success', 'Settings saved successfully.');
    }
}
