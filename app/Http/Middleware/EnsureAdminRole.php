<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdminRole
{
    /**
     * Restrict access to specific admin roles.
     *
     * Usage: middleware('admin.role:Super Admin,Librarian')
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin || !in_array($admin->role, $roles)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
