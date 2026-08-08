<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScannerController extends Controller
{
    /**
     * Display the mobile staff scanner UI.
     */
    public function mobile(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        return view('admin.scanner.mobile', compact('admin'));
    }
}
