<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Role
{
    /**
     * Enforce that the authenticated user has ANY of the given roles.
     *
     * Usage examples:
     *   ->middleware('role:admin')                 // must be admin
     *   ->middleware('role:faculty,student')       // faculty OR student
     *   ->middleware('role:admin|faculty')         // also supported
     *   ->middleware('role')                       // just requires auth (no role check)
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Must be authenticated
        $user = Auth::user();
        if (!$user) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Normalize roles: support comma- or pipe-separated strings and variadic args
        $required = collect($roles)
            ->flatMap(function ($r) {
                return preg_split('/[|,]/', (string)$r, -1, PREG_SPLIT_NO_EMPTY);
            })
            ->map(fn($r) => trim($r))
            ->filter()
            ->values();

        // If no roles specified, just pass (auth-only)
        if ($required->isEmpty()) {
            return $next($request);
        }

        // Allow if user has ANY of the required roles
        $allowed = $required->some(fn($role) => $user->hasRole($role));

        if (!$allowed) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
            abort(403);
        }

        return $next($request);
    }
}
