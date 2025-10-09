<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            $target = $this->redirectTargetFor($user);

            return redirect()->intended($target);
        }

        return $next($request);
    }

    private function redirectTargetFor($user): string
    {
        // Priority: admin > faculty > student
        if ($user->hasRole('admin')) {
            return route('admin.dashboard');
        }
        if ($user->hasRole('faculty')) {
            return route('faculty.attendance');
        }
        if ($user->hasRole('student')) {
            return route('student.attendance');
        }

        // Fallback if no recognized role
        return route('login');
    }
}
