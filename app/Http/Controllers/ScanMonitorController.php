<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

class ScanMonitorController extends Controller
{
    public function last(Request $request)
    {
        // Validate ?device=...
        $request->validate([
            'device' => 'required|string',
        ]);

        $device = trim($request->query('device'));
        $uid = Cache::get("rfid:last:$device"); // may be null if nothing cached

        return Response::json(['uid' => $uid])->header('Cache-Control', 'no-store');
    }
}
