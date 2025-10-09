<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Device;

class DeviceAuth
{
    /**
     * Accept credentials from:
     * - JSON/body: serial_no, device_token
     * - Query:     ?serial_no=...&device_token=...
     * - Headers:   X-Device-Serial, X-Device-Token
     * - Header:    Authorization: Device <token>
     */
    public function handle(Request $request, Closure $next)
    {
        $serial = $this->getSerial($request);
        $token  = $this->getToken($request);

        if (!$serial || !$token) {
            return response()->json([
                'ok' => false,
                'message' => 'Missing device credentials.',
            ], 401);
        }

        // Look up by serial first (indexed & unique in schema)
        $device = Device::where('serial_no', $serial)->first();

        if (!$device || ! $this->tokenMatches($device->token, $token)) {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid device.',
            ], 401);
        }

        // Make device available to controllers
        $request->attributes->set('device', $device);

        return $next($request);
    }

    private function getSerial(Request $request): ?string
    {
        $serial = $request->input('serial_no')
            ?? $request->query('serial_no')
            ?? $request->header('X-Device-Serial');

        return $serial ? trim((string)$serial) : null;
    }

    private function getToken(Request $request): ?string
    {
        // Prefer explicit fields/headers
        $token = $request->input('device_token')
            ?? $request->query('device_token')
            ?? $request->header('X-Device-Token');

        if ($token) {
            return trim((string)$token);
        }

        // Fallback: Authorization: Device <token>
        $auth = $request->header('Authorization');
        if ($auth && stripos($auth, 'Device ') === 0) {
            return trim(substr($auth, 7));
        }

        return null;
        }

    private function tokenMatches(string $stored, string $provided): bool
    {
        // If you later store hashed tokens, replace with Hash::check($provided, $stored).
        // For now, compare constant-time to avoid timing side-channels.
        return hash_equals($stored, $provided);
    }
}
