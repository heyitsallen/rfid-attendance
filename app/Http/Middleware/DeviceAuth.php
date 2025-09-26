<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Device;

class DeviceAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Accept either JSON body or headers
        $serial = $request->input('serial_no') ?? $request->header('X-Device-Serial');
        $token  = $request->input('device_token') ?? $request->header('X-Device-Token');

        if (!$serial || !$token) {
            return response()->json([
                'ok' => false,
                'message' => 'Missing device credentials.',
            ], 401);
        }

        $device = Device::where('serial_no', $serial)->where('token', $token)->first();
        if (!$device) {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid device.',
            ], 401);
        }

        // make device available downstream if you want
        $request->attributes->set('device', $device);

        return $next($request);
    }
}