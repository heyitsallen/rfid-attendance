<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next, ?string $role = null)
    {
        $user = Auth::user();

        // 1. If user is logged in and trying to access login/register pages
        if ($user && $request->is('login', 'register')) {
            switch ($user->role) {
                case 'admin':
                    return redirect()->route('admin.home');
                case 'faculty':
                    return redirect()->route('faculty.home');
                case 'student':
                    return redirect()->route('student.home');
                default:
                    Auth::logout();
                    return redirect()->route('login');
            }
        }

        // 2. If route requires authentication and user is not logged in
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'Please log in first.']);
        }

        // 3. If role is required, check role
        if ($role && $user->role !== $role) {
            abort(403); // Forbidden
        }

        return $next($request);
    }
}


