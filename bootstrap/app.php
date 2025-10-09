<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Aliases (use in routes)
        $middleware->alias([
            // Built-ins / project-specific
            'guest'   => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'active'  => \App\Http\Middleware\EnsureUserIsActive::class,   // if you keep an "active" check
            'device'  => \App\Http\Middleware\DeviceAuth::class,           // optional, for device-protected routes
            'nocache' => \App\Http\Middleware\PreventBackHistory::class,   // if you use it

            // New multi-role middleware (replaces old "Role" enum-based middleware)
            'role' => \App\Http\Middleware\AuthMiddleware::class,
        ]);

        // Optionally add global or group middleware:
        // $middleware->append(\App\Http\Middleware\EnsureUserIsActive::class);
        // $middleware->group('web', [
        //     \App\Http\Middleware\EnsureUserIsActive::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
