<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Usage:
     *   ->middleware('role:admin')               // must be admin
     *   ->middleware('role:faculty,student')     // faculty OR student
     *   ->middleware('role:admin|faculty')       // also supported
     */
    public function handle(Request $request, Closure $next, string $roles)
    {
        $user = Auth::user();

        if (!$user) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Split by comma or pipe, trim whitespace
        $requiredRoles = collect(preg_split('/[|,]/', $roles, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn($r) => trim($r))
            ->filter()
            ->values();

        if ($requiredRoles->isNotEmpty()) {
            $allowed = $requiredRoles->some(fn($r) => $user->hasRole($r));

            if (!$allowed) {
                if ($request->expectsJson() || $request->wantsJson()) {
                    return response()->json(['message' => 'Forbidden.'], 403);
                }
                abort(403);
            }
        }

        return $next($request);
    }
}
