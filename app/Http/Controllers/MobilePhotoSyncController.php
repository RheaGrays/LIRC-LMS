<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MobilePhotoSyncController extends Controller
{
    /**
     * Create a new 15-minute photo capture pairing session.
     */
    public function createSession(Request $request): JsonResponse
    {
        $sessionId = 'REG-' . strtoupper(Str::random(6));

        Cache::put("reg_photo_{$sessionId}", [
            'status'         => 'pending',
            'photoDataUrl'   => null,
            'created_at'     => now()->toIso8601String(),
        ], 900); // 15 minutes TTL

        $hostIp = $this->getHostLanAddress($request);
        $scheme = $request->getScheme();
        $mobileUrl = "{$scheme}://{$hostIp}/register/mobile-camera?session={$sessionId}";

        return response()->json([
            'session_id' => $sessionId,
            'mobile_url' => $mobileUrl,
        ]);
    }

    /**
     * Upload photo from mobile phone or mobile app for a specific session.
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id'   => 'required|string|max:50',
            'photoDataUrl' => 'required|string',
        ]);

        $sessionId = strtoupper(trim($validated['session_id']));
        $cacheKey = "reg_photo_{$sessionId}";

        $photoDataUrl = $validated['photoDataUrl'];
        if (!str_starts_with($photoDataUrl, 'data:image')) {
            return response()->json(['message' => 'Invalid image format.'], 422);
        }

        // Store photo in cache for 15 minutes, marking status as completed
        Cache::put($cacheKey, [
            'status'       => 'completed',
            'photoDataUrl' => $photoDataUrl,
            'uploaded_at'  => now()->toIso8601String(),
        ], 900);

        return response()->json([
            'success' => true,
            'message' => 'Photo uploaded and synced successfully!',
        ]);
    }

    /**
     * Check if a photo has been captured and uploaded for the given session.
     */
    public function checkSession(string $sessionId): JsonResponse
    {
        $sessionId = strtoupper(trim($sessionId));
        $cacheKey = "reg_photo_{$sessionId}";

        $session = Cache::get($cacheKey);

        if (!$session) {
            return response()->json(['status' => 'expired', 'message' => 'Session expired.']);
        }

        return response()->json([
            'status'       => $session['status'],
            'photoDataUrl' => $session['photoDataUrl'] ?? null,
        ]);
    }

    /**
     * Render the standalone mobile camera capture web page.
     */
    public function showMobileCamera(Request $request)
    {
        $sessionId = $request->query('session', '');
        return view('register.mobile_camera', compact('sessionId'));
    }

    /**
     * Resolve the LAN IP address of the server when accessed via localhost/127.0.0.1
     * so that mobile devices scanning the QR code receive a valid LAN IP URL.
     */
    private function getHostLanAddress(Request $request): string
    {
        $host = $request->getHttpHost();

        if (str_contains($host, 'localhost') || str_contains($host, '127.0.0.1')) {
            $localIp = gethostbyname(gethostname());
            if ($localIp && $localIp !== '127.0.0.1') {
                $port = $request->getPort();
                $portStr = $port && $port != 80 && $port != 443 ? ":{$port}" : '';
                return $localIp . $portStr;
            }
        }

        return $host;
    }
}
