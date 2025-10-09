<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Usage examples:
     *  - ->middleware('auth.role')                 // just requires auth
     *  - ->middleware('auth.role:admin')           // requires admin
     *  - ->middleware('auth.role:faculty|student') // requires any of faculty OR student
     */
    public function handle(Request $request, Closure $next, ?string $roles = null)
    {
        $user = Auth::user();

        // 1) If user is already logged in and tries to access guest pages, send them home
        if ($user && $this->isGuestPage($request)) {
            return redirect()->to($this->defaultRouteFor($user));
        }

        // 2) If route requires authentication and user is not logged in
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'Please log in first.']);
        }

        // 3) If specific role(s) are required, enforce them
        if ($roles) {
            $allowed = collect(explode('|', $roles))
                ->map(fn($r) => trim($r))
                ->filter()
                ->some(fn($r) => $user->hasRole($r));

            if (!$allowed) {
                abort(403); // Forbidden
            }
        }

        return $next($request);
    }

    private function isGuestPage(Request $request): bool
    {
        // Adjust patterns as needed (e.g., password pages are allowed while logged in)
        return $request->is('login') || $request->is('register');
    }

    /**
     * Decide where to send a logged-in user trying to access guest-only pages.
     * Priority: admin > faculty > student
     */
    private function defaultRouteFor($user): string
    {
        if ($user->hasRole('admin')) {
            return route('admin.dashboard');
        }
        if ($user->hasRole('faculty')) {
            return route('faculty.attendance');
        }
        if ($user->hasRole('student')) {
            return route('student.attendance');
        }
        // Fallback if no known role: log out to be safe
        Auth::logout();
        return route('login');
    }
}
