<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt' => \App\Http\Middleware\JwtAuthenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (
            \App\Exceptions\Domain\InsufficientFundsException $e
        ) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        });

        $exceptions->render(function (
            \App\Exceptions\Domain\CurrencyMismatchException $e
        ) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        });

        $exceptions->render(function (
            \App\Exceptions\Domain\InactiveWalletException $e
        ) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        });

        $exceptions->render(function (
            \Illuminate\Database\Eloquent\ModelNotFoundException $e
        ) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        });

        // temporary debugging fallback
        $exceptions->render(function (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        });
    })
    ->create();
