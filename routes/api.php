<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceApiController;

Route::post('/attendance/scan', [AttendanceApiController::class, 'scan'])
    ->middleware('device');
