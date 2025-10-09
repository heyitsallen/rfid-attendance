<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\FacultyProfileController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/**
 * Root → if logged in, send to role-based home; else to login.
 */
Route::get('/', function () {
    $user = Auth::user();
    if (!$user) {
        return redirect()->route('login');
    }

    // Prefer many-to-many roles; fall back to computed role_name
    if ($user->hasRole('admin') || $user->role_name === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    if ($user->hasRole('faculty') || $user->role_name === 'faculty') {
        return redirect()->route('faculty.schedule');
    }
    if ($user->hasRole('student') || $user->role_name === 'student') {
        return redirect()->route('student.schedule');
    }

    // Default: generic profile
    return redirect()->route('profile.show');
})->name('home');

// ----------------------
// Guest-only (auth)
// ----------------------
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // Password reset flow (guest)
    Route::get('/forgot-password',        [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password',       [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password',        [AuthController::class, 'resetPassword'])->name('password.update');
});

// ----------------------
// Auth-only
// ----------------------
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Everything below: must be authenticated & active; prevent back cache.
// NOTE: ensure these middleware are aliased in bootstrap/app.php:
//   'active'  => \App\Http\Middleware\Active::class
//   'nocache' => \App\Http\Middleware\NoCache::class
//   'role'    => \App\Http\Middleware\Role::class
Route::middleware(['auth', 'active', 'nocache'])->group(function () {

    // ----- Generic Profile -----
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',            [ProfileController::class, 'show'])->name('show');
        Route::post('/password',   [ProfileController::class, 'updatePassword'])->name('password.update');
        // Add other generic profile endpoints here
    });

    // ==========================
    // Admin Area
    // ==========================
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // Profile
        Route::get('/profile',  [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profile', [AdminProfileController::class, 'update'])->name('profile.update');

        // App pages
        Route::get('/dashboard',  [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/management', [AdminController::class, 'management'])->name('management');
        Route::get('/attendance', [AdminController::class, 'attendance'])->name('attendance');
        Route::get('/reports',    [AdminController::class, 'reports'])->name('reports');

        // If you have admin actions that mutate state, prefer POST/PUT/PATCH/DELETE here.
        // Example:
        // Route::post('/users/{user}/deactivate', [AdminUserController::class, 'deactivate'])->name('users.deactivate');
    });

    // ==========================
    // Student Area
    // ==========================
    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        // Profile
        Route::get('/profile',  [StudentProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profile', [StudentProfileController::class, 'update'])->name('profile.update');

        // Views
        Route::get('/attendance', [StudentController::class, 'attendance'])->name('attendance');
        Route::get('/schedule',   [StudentController::class, 'schedule'])->name('schedule');
    });

    // ==========================
    // Faculty Area
    // ==========================
    Route::middleware('role:faculty')->prefix('faculty')->name('faculty.')->group(function () {
        // Profile
        Route::get('/profile',  [FacultyProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profile', [FacultyProfileController::class, 'update'])->name('profile.update');

        // Views
        Route::get('/attendance', [FacultyController::class, 'attendance'])->name('attendance');
        Route::get('/schedule',   [FacultyController::class, 'schedule'])->name('schedule');
        Route::get('/personal',   [FacultyController::class, 'personal'])->name('personal');
    });
});

// ----------------------
// Fallback (404)
// ----------------------
Route::fallback(function () {
    abort(404);
});
