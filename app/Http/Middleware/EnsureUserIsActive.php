<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
    /**
     * Usage:
     *  ->middleware('active')            // defaults to 'web' guard
     *  ->middleware('active:api')        // check a specific guard
     */
    public function handle(Request $request, Closure $next, string $guard = 'web')
    {
        // Allow public/auth endpoints to pass through (avoid redirect loops)
        if ($this->isPublicAuthRoute($request)) {
            return $next($request);
        }

        $user = $request->user($guard);

        // Not authenticated
        if (!$user) {
            return $this->unauthenticatedResponse($request);
        }

        // Inactive account
        if ($user->status !== 'active') {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $this->inactiveResponse($request);
        }

        return $next($request);
    }

    private function isPublicAuthRoute(Request $request): bool
    {
        // Add/adjust patterns as needed for your app
        return $request->is('login', 'register', 'forgot-password', 'reset-password', 'password/reset*');
    }

    private function unauthenticatedResponse(Request $request)
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        return redirect()->route('login')->withErrors(['login' => 'Please log in first.']);
    }

    private function inactiveResponse(Request $request)
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['message' => 'Account inactive.'], 403);
        }
        return redirect()->route('login')->withErrors(['email' => 'Your account is inactive.']);
    }
}
