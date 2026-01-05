<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function () {
            Route::middleware('web')
                ->namespace('App\\Http\\Controllers')
                ->group(base_path('routes/web.php'));

            Route::middleware('api')
                ->prefix('api')
                ->namespace('App\\Http\\Controllers')
                ->group(base_path('routes/api.php'));
        },
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global Middleware (from app/Http/Kernel.php)
        $middleware->use([
            \App\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \App\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        // Web Middleware Group
        $middleware->web(append: [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // API Middleware Group
        $middleware->api(prepend: [
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':60,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Route Middleware Aliases (from $routeMiddleware)
        $middleware->alias([
            'auth'          => \App\Http\Middleware\Authenticate::class,
            'auth.basic'    => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can'           => \Illuminate\Auth\Middleware\Authorize::class,
            'guest'         => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'signed'        => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'throttle'      => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified'      => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);

        // Middleware Priority (from $middlewarePriority)
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Hide SQL queries in error pages for security
        $exceptions->dontReport([
            // Add exceptions to not report here
        ]);

        // Remove sensitive data from exceptions
        $exceptions->reportable(function (Throwable $e) {
            // Custom logging without queries if needed
        });
    })
    ->withSchedule(function ($schedule) {
        // Migrated from app/Console/Kernel.php schedule() method
        // Add your scheduled tasks here
        // Example: $schedule->command('inspire')->hourly();
    })
    ->create();
