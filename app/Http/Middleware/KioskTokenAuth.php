<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Kiosk Token Authentication Middleware.
 *
 * Protects kiosk API endpoints from remote abuse. The kiosk device must present
 * a shared secret token (configured in .env as KIOSK_API_TOKEN) via either:
 *   - X-Kiosk-Token header, OR
 *   - ?kiosk_token= query parameter (for simple GET endpoints)
 *
 * Requests from localhost (127.0.0.1, ::1) are always allowed to support
 * the Electron kiosk app that runs a local PHP server.
 */
class KioskTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Always allow requests from localhost (Electron kiosk app)
        $ip = $request->ip();
        if (in_array($ip, ['127.0.0.1', '::1'], true)) {
            return $next($request);
        }

        // Check for kiosk token
        $configuredToken = config('app.kiosk_api_token');

        // If no token is configured, deny ALL remote requests to kiosk endpoints
        if (!$configuredToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kiosk API token not configured. Remote access denied.',
            ], 403);
        }

        $providedToken = $request->header('X-Kiosk-Token')
            ?? $request->query('kiosk_token');

        if (!$providedToken || !hash_equals($configuredToken, $providedToken)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or missing kiosk authentication token.',
            ], 403);
        }

        return $next($request);
    }
}
