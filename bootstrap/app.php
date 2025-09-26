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
            'guest'  => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'role'   => \App\Http\Middleware\Role::class,
            'device' => \App\Http\Middleware\DeviceAuth::class, // optional
            'nocache' => \App\Http\Middleware\PreventBackHistory::class,
        ]);

        // Optionally append a global middleware (runs on every request)
        // $middleware->append(\App\Http\Middleware\EnsureUserIsActive::class);

        // Or add to groups:
        // $middleware->group('web', [
        //     \App\Http\Middleware\EnsureUserIsActive::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
