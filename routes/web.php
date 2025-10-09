<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\AdminAjaxController; // keep if used elsewhere
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\FacultyProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ----------------------
// Guest-only auth pages
// ----------------------
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // Forgot/reset password flows are generally guest pages
    Route::get('/forgot-password',  [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// ----------------------
// Auth-only
// ----------------------
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Everything below requires an authenticated, active user and disables back-cache
Route::middleware(['auth', 'active', 'nocache'])->group(function () {

    // ----- Profile (generic) -----
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // ----- Admin -----
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/profile',  [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
        Route::post('/admin/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');

        Route::get('/admin/dashboard',  [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/admin/management', [AdminController::class, 'management'])->name('admin.management');
        Route::get('/admin/attendance', [AdminController::class, 'attendance'])->name('admin.attendance');
        Route::get('/admin/reports',    [AdminController::class, 'reports'])->name('admin.reports');
    });

    // ----- Student -----
    Route::middleware('role:student')->group(function () {
        Route::get('/student/profile',  [StudentProfileController::class, 'edit'])->name('student.profile.edit');
        Route::post('/student/profile', [StudentProfileController::class, 'update'])->name('student.profile.update');

        Route::get('/student/attendance', [StudentController::class, 'attendance'])->name('student.attendance');
        Route::get('/student/schedule',   [StudentController::class, 'schedule'])->name('student.schedule');
    });

    // ----- Faculty -----
    Route::middleware('role:faculty')->group(function () {
        Route::get('/faculty/profile',  [FacultyProfileController::class, 'edit'])->name('faculty.profile.edit');
        Route::post('/faculty/profile', [FacultyProfileController::class, 'update'])->name('faculty.profile.update');

        Route::get('/faculty/attendance', [FacultyController::class, 'attendance'])->name('faculty.attendance');
        Route::get('/faculty/schedule',   [FacultyController::class, 'schedule'])->name('faculty.schedule');
        Route::get('/faculty/personal',   [FacultyController::class, 'personal'])->name('faculty.personal');
    });
});
