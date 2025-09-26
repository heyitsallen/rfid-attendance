<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $req)
    {
        $this->ensureIsNotRateLimited($req);

        $cred = $req->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
            // no boolean rule for remember
        ]);

        $attempt = Auth::attempt(
            ['email' => $cred['email'], 'password' => $cred['password'], 'status' => 'active'],
            $req->boolean('remember')
        );

        if (!$attempt) {
            RateLimiter::hit($this->throttleKey($req));
            throw ValidationException::withMessages([
                'email' => __('Invalid credentials.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($req));
        $req->session()->regenerate();

        $user = Auth::user();

        $defaults = [
            'admin'   => route('admin.dashboard'),
            'faculty' => route('faculty.attendance'),
            'student' => route('student.attendance'),
        ];

        if (!isset($defaults[$user->role])) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => __('Unauthorized role.'),
            ]);
        }

        return redirect()->intended($defaults[$user->role]);
    }

    public function logout(Request $req)
    {
        Auth::logout();
        $req->session()->invalidate();
        $req->session()->regenerateToken();
        return redirect()->route('login');
    }

    // =====================
    // Password Reset Methods
    // =====================

    // Show "forgot password" form
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    // Handle reset link request
    public function sendResetLink(Request $request)
    {
            $request->validate(
        [
            'email' => ['required', 'email', 'exists:users,email'],
        ],
        [
            'email.exists' => 'We couldn’t find that email in our records.',
        ]
    );

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    // Show reset form (from email link)
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    // Handle actual reset
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required','email'],
            'password' => ['required','confirmed','min:6'],
        ]);

        $status = Password::reset(
            $request->only('email','password','password_confirmation','token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    // =====================
    // Helpers
    // =====================

    private function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email')).'|'.$request->ip();
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }
        $seconds = RateLimiter::availableIn($this->throttleKey($request));
        throw ValidationException::withMessages([
            'email' => __('Too many attempts. Try again in :seconds seconds.', ['seconds' => $seconds]),
        ]);
    }
}
