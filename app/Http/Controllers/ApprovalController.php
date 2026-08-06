<?php

namespace App\Http\Controllers;

use App\Models\PendingAdminApproval;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ApprovalController extends Controller
{
    public function index()
    {
        $admin    = Auth::guard('admin')->user();
        if (!$admin->isSuperAdmin()) abort(403);

        $pending = PendingAdminApproval::where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.approvals.index', compact('admin', 'pending'));
    }

    public function approve(string $id)
    {
        if (!Auth::guard('admin')->user()->isSuperAdmin()) abort(403);

        $approval = PendingAdminApproval::findOrFail($id);

        if ($approval->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        // Create admin account
        $initials = collect(explode(' ', $approval->full_name))
            ->map(fn($n) => strtoupper($n[0] ?? ''))
            ->take(2)
            ->implode('');

        Admin::create([
            'email'           => $approval->email,
            'password'        => $approval->password_hash, // already bcrypt-hashed
            'full_name'       => $approval->full_name,
            'role'            => $approval->role,
            'avatar_initials' => $initials,
            'is_active'       => true,
        ]);

        $approval->update(['status' => 'approved']);

        \Illuminate\Support\Facades\Log::info('Admin Account Approved', [
            'admin_id' => Auth::guard('admin')->id(),
            'approved_email' => $approval->email,
        ]);

        return back()->with('success', "Account approved for {$approval->full_name}.");
    }

    public function reject(string $id)
    {
        if (!Auth::guard('admin')->user()->isSuperAdmin()) abort(403);

        $approval = PendingAdminApproval::findOrFail($id);

        if ($approval->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $approval->update(['status' => 'rejected']);

        \Illuminate\Support\Facades\Log::info('Admin Account Rejected', [
            'admin_id' => Auth::guard('admin')->id(),
            'rejected_email' => $approval->email,
        ]);

        return back()->with('success', "Request from {$approval->full_name} rejected.");
    }
}
