<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KioskController extends Controller
{
    public function index()
    {
        $settings = \App\Models\SystemSetting::allSettings();
        return view('kiosk.index', compact('settings'));
    }
}
