<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'api.auth' => \App\Http\Middleware\ApiAuthMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // 🔹 Handle Unauthenticated (auth:api)
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            return \App\Http\Resources\ApiResponse::unauthorized('Unauthorized');
        });

        // 🔹 Handle Validation Errors (optional but recommended)
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            return \App\Http\Resources\ApiResponse::validation($e->validator);
        });

        // 🔹 Handle General Exceptions
        $exceptions->render(function (\Throwable $e, $request) {
            return \App\Http\Resources\ApiResponse::exception($e);
        });

    })->create();
