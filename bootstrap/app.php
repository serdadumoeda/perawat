<?php

use App\Http\Middleware\ApiKeyAuth;
use App\Http\Middleware\ApiIdempotency;
use App\Http\Middleware\ApiRequestLogger;
use App\Http\Middleware\EnsureUserRole;
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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserRole::class,
            'api_key' => ApiKeyAuth::class,
            'api_idempotency' => ApiIdempotency::class,
            'api_request_log' => ApiRequestLogger::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
