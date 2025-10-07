<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\AdminAjaxController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\FacultyProfileController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminCardController;

// Guest-only auth pages
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Auth-only logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');


Route::middleware(['auth', 'nocache'])->group(function () {
Route::get('/profile', [ProfileController::class, 'show'])
    ->name('profile.show')
    ->middleware('auth');

Route::post('/profile/password', [ProfileController::class, 'updatePassword'])
    ->name('profile.password.update')
    ->middleware('auth');


Route::middleware(['auth','role:admin'])->group(function () {
    Route::get('/admin/profile',  [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::post('/admin/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/management', [AdminController::class, 'management'])->name('admin.management');
    Route::get('/admin/attendance', [AdminController::class, 'attendance'])->name('admin.attendance');
    Route::get('/admin/reports', [AdminController::class, 'reports'])->name('admin.reports');

    // Users
    Route::post('/admin/users',          [AdminUserController::class, 'store'])->name('admin.users.store');      // add student/faculty
    Route::put('/admin/users/{user}',    [AdminUserController::class, 'update'])->name('admin.users.update');    // edit user and link cards
    Route::patch('/admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');  // delete user

    // Cards
    Route::post('/admin/cards/check',  [AdminCardController::class, 'check'])->name('admin.cards.check');        // check if card UID exists / owner
    Route::post('/admin/cards/link',   [AdminCardController::class, 'link'])->name('admin.cards.link');          // link card to user (current SY)
    Route::put('/admin/cards/{card}',  [AdminCardController::class, 'update'])->name('admin.cards.update');      // toggle status, edit meta


        // Polling endpoint used by the view to auto-fill UID
    Route::get('/admin/scans/last', function (Request $request) {
        $request->validate(['device' => 'required|string']);
        $device = trim($request->query('device'));
        $uid    = Cache::get("rfid:last:$device"); // null if no recent scan
        return response()->json(['uid' => $uid])->header('Cache-Control', 'no-store');
    })->name('admin.scans.last');
});

// Student Profile
Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/student/profile', [StudentProfileController::class, 'edit'])->name('student.profile.edit');
    Route::post('/student/profile', [StudentProfileController::class, 'update'])->name('student.profile.update');
    Route::get('/student/attendance', [StudentController::class, 'attendance'])->name('student.attendance');
    Route::get('/student/schedule', [StudentController::class, 'schedule'])->name('student.schedule');
});

// Faculty Profile
Route::middleware(['auth', 'role:faculty'])->group(function () {
    Route::get('/faculty/profile', [FacultyProfileController::class, 'edit'])->name('faculty.profile.edit');
    Route::post('/faculty/profile', [FacultyProfileController::class, 'update'])->name('faculty.profile.update');
    Route::get('/faculty/attendance', [FacultyController::class, 'attendance'])->name('faculty.attendance');
    Route::get('/faculty/schedule', [FacultyController::class, 'schedule'])->name('faculty.schedule');
    Route::get('/faculty/personal', [FacultyController::class, 'personal'])->name('faculty.personal');
});


// Protected area
Route::middleware(['auth', 'active'])->group(function () {
    // Admin dashboard
    Route::get('/admin', [AdminController::class, 'dashboard'])
        ->middleware('role:admin')
        ->name('admin.dashboard');
    
    
    });
    });

