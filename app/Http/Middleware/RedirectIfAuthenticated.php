<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            $targets = [
                'admin'   => route('admin.dashboard'),
                'faculty' => route('faculty.attendance'),
                'student' => route('student.attendance'),
            ];

            return redirect()->intended($targets[$user->role] ?? route('login'));
        }

        return $next($request);
    }
}