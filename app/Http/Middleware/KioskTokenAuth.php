<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Kiosk Token Authentication Middleware.
 *
 * Protects kiosk API endpoints from unauthorized remote internet access.
 * Requests from localhost (127.0.0.1, ::1) and local LAN networks (192.168.x.x, 10.x.x.x, 172.16-31.x.x)
 * are permitted to support local kiosk devices and mobile scanners on the same Wi-Fi network.
 *
 * External remote access requires a valid KIOSK_API_TOKEN header or parameter.
 */
class KioskTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();

        // Always allow requests from localhost and local LAN networks (same Wi-Fi/subnet)
        if ($this->isLocalNetwork($ip)) {
            return $next($request);
        }

        // For external remote IPs outside the local network, check for kiosk API token
        $configuredToken = config('app.kiosk_api_token');

        if (!$configuredToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kiosk API token not configured. External remote access denied.',
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
     * Check if an IP address belongs to localhost or a private local network (LAN).
     */
    private function isLocalNetwork(string $ip): bool
    {
        if (in_array($ip, ['127.0.0.1', '::1'], true)) {
            return true;
        }

        // Returns true if IP is in private range (192.168.x.x, 10.x.x.x, 172.16.x.x - 172.31.x.x)
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
