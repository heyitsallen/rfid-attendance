<?php

// app/Http/Middleware/LoadUserRole.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;


class LoadUserRole
{
    public function handle($request, Closure $next)
    {
        if ($user = auth()->user()) {
            $user->loadMissing('roles');
        }
        return $next($request);
    }
}
