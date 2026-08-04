<?php

namespace App\Http\Controllers;

use App\Models\PendingAdminApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminSignupController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email'     => ['required', 'email', 'unique:admins,email', 'unique:pending_admin_approvals,email'],
            'password'  => ['required', 'min:8', 'confirmed'],
            'full_name' => ['required', 'string', 'max:255'],
            'role'      => ['required', 'in:Super Admin,Staff,Librarian'],
        ]);

        PendingAdminApproval::create([
            'email'         => strtolower($data['email']),
            'full_name'     => $data['full_name'],
            'role'          => $data['role'],
            'password_hash' => Hash::make($data['password']), // bcrypt — safe!
            'status'        => 'pending',
        ]);

        return back()->with('success',
            'Registration submitted! A Super Admin must approve your account before you can log in.'
        );
    }
}
