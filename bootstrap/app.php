<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add global middleware to apply database config overrides
        $middleware->web(prepend: [
            \App\Http\Middleware\ApplyDatabaseConfig::class,
        ]);

        // Add cart middleware and suspended user check to web group
        $middleware->web(append: [
            \App\Http\Middleware\CartMiddleware::class,
            \App\Http\Middleware\CheckSuspendedUser::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'ewallet.security' => \App\Http\Middleware\EWalletSecurityMiddleware::class,
            'conditional.verified' => \App\Http\Middleware\ConditionalEmailVerification::class,
            'enforce.2fa' => \App\Http\Middleware\Enforce2FA::class,
            'cart' => \App\Http\Middleware\CartMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
