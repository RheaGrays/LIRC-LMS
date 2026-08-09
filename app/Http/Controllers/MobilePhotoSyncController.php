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
     *
     * SEC-02 FIX: Session IDs are now 16 hex characters (64-bit entropy, ~1.8×10¹⁹ combinations)
     * instead of 6 alphanumeric chars (~2.1 billion — brute-forceable).
     *
     * An owner_token is also generated and returned ONLY to the desktop that created the session.
     * The desktop must present this token to consume (retrieve + delete) the uploaded photo,
     * so even if someone guesses the session ID they cannot access the image.
     */
    public function createSession(Request $request): JsonResponse
    {
        // 16 hex chars = 8 random bytes = 64 bits of entropy
        $sessionId  = 'REG-' . strtoupper(bin2hex(random_bytes(8)));
        $ownerToken = bin2hex(random_bytes(16)); // 32 hex chars — returned once, stored hashed

        Cache::put("reg_photo_{$sessionId}", [
            'status'            => 'pending',
            'photoDataUrl'      => null,
            'owner_token_hash'  => hash('sha256', $ownerToken), // store hash, never plaintext
            'created_at'        => now()->toIso8601String(),
        ], 900); // 15 minutes TTL

        $hostIp = $this->getHostLanAddress($request);
        $scheme = $request->getScheme();
        $mobileUrl = "{$scheme}://{$hostIp}/register/mobile-camera?session={$sessionId}";

        return response()->json([
            'session_id'  => $sessionId,
            'owner_token' => $ownerToken,   // returned once — desktop stores it locally
            'mobile_url'  => $mobileUrl,
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
        $cacheKey  = "reg_photo_{$sessionId}";

        // Ensure the session actually exists before accepting a photo
        $session = Cache::get($cacheKey);
        if (!$session) {
            return response()->json(['message' => 'Session not found or expired.'], 404);
        }

        $photoDataUrl = $validated['photoDataUrl'];
        if (!str_starts_with($photoDataUrl, 'data:image')) {
            return response()->json(['message' => 'Invalid image format.'], 422);
        }

        // Store photo in cache — preserve owner_token_hash from creation
        Cache::put($cacheKey, [
            'status'           => 'completed',
            'photoDataUrl'     => $photoDataUrl,
            'owner_token_hash' => $session['owner_token_hash'] ?? null,
            'uploaded_at'      => now()->toIso8601String(),
        ], 900);

        return response()->json([
            'success' => true,
            'message' => 'Photo uploaded and synced successfully!',
        ]);
    }

    /**
     * Poll whether a photo has been uploaded for the given session.
     *
     * SEC-02 FIX: Returns status only — never the photo data.
     * The photo is retrieved separately via consumeSession(), which requires the owner token.
     */
    public function checkSession(string $sessionId): JsonResponse
    {
        $sessionId = strtoupper(trim($sessionId));
        $cacheKey  = "reg_photo_{$sessionId}";

        $session = Cache::get($cacheKey);

        if (!$session) {
            return response()->json(['status' => 'expired', 'message' => 'Session expired.']);
        }

        // SEC-02 FIX: Only return status — never the photo payload from a poll endpoint.
        // The desktop fetches the actual photo via consumeSession() with its owner token.
        return response()->json([
            'status' => $session['status'],
        ]);
    }

    /**
     * Consume (retrieve and delete) the uploaded photo.
     *
     * SEC-02 FIX: Requires the owner_token issued at createSession().
     * The photo is deleted from cache immediately after retrieval (one-time read).
     */
    public function consumeSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id'  => 'required|string|max:60',
            'owner_token' => 'required|string|max:64',
        ]);

        $sessionId = strtoupper(trim($validated['session_id']));
        $cacheKey  = "reg_photo_{$sessionId}";

        $session = Cache::get($cacheKey);

        if (!$session) {
            return response()->json(['status' => 'expired', 'message' => 'Session expired or already consumed.'], 404);
        }

        if ($session['status'] !== 'completed') {
            return response()->json(['status' => 'pending', 'message' => 'Photo not yet uploaded.']);
        }

        // Verify the owner token using constant-time comparison against the stored hash
        $providedHash = hash('sha256', $validated['owner_token']);
        if (!hash_equals($session['owner_token_hash'] ?? '', $providedHash)) {
            return response()->json(['message' => 'Invalid owner token.'], 403);
        }

        $photoDataUrl = $session['photoDataUrl'];

        // Delete from cache immediately — photo can only be consumed once
        Cache::forget($cacheKey);

        return response()->json([
            'status'       => 'completed',
            'photoDataUrl' => $photoDataUrl,
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
