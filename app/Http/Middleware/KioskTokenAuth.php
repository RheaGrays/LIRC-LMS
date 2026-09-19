<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Kiosk Token Authentication Middleware.
 *
 * Protects kiosk API endpoints from unauthorized access.
 *
 * Only requests from the local machine itself (127.0.0.1, ::1) are permitted
 * without a token — this covers the Electron desktop app and artisan serve.
 *
 * ALL other requests (including devices on the same LAN / school Wi-Fi) MUST
 * supply a valid KIOSK_API_TOKEN via the X-Kiosk-Token header or kiosk_token
 * query parameter. This prevents students on campus Wi-Fi from forging scans.
 *
 * To configure token-authenticated kiosk clients (e.g. a second kiosk PC):
 *   1. Set KIOSK_API_TOKEN=<secret> in the server's .env
 *   2. Configure the client to send X-Kiosk-Token: <secret> with each request
 */
class KioskTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();

        // Only allow unauthenticated access from the local machine itself.
        // This permits the Electron window (localhost) without a token while
        // requiring all LAN clients — including phones and other kiosk PCs —
        // to present a valid token.
        if ($this->isLocalhost($ip)) {
            return $next($request);
        }

        // All non-localhost requests (LAN, remote) require a valid kiosk token.
        $configuredToken = config('app.kiosk_api_token');

        if (!$configuredToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kiosk API token not configured. Access denied.',
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

    /**
     * Check if an IP address is the local machine itself (loopback only).
     * This intentionally does NOT include private LAN ranges (192.168.x.x etc.)
     * because school students share the same LAN and must not bypass auth.
     */
    private function isLocalhost(string $ip): bool
    {
        return in_array($ip, ['127.0.0.1', '::1'], true);
    }
}
