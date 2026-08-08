<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.admin' => \App\Http\Middleware\EnsureAdminAuthenticated::class,
            'admin.role' => \App\Http\Middleware\EnsureAdminRole::class,
        ]);
        
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Database\QueryException $e, \Illuminate\Http\Request $request) {
            // Check for connection-related errors
            if (str_contains($e->getMessage(), 'SQLSTATE[HY000] [2002]') || str_contains($e->getMessage(), 'Connection refused') || str_contains($e->getMessage(), 'No such host is known')) {
                return response()->view('errors.db_offline', [], 500);
            }
        });
        $exceptions->render(function (\PDOException $e, \Illuminate\Http\Request $request) {
            if (str_contains($e->getMessage(), 'SQLSTATE[HY000] [2002]') || str_contains($e->getMessage(), 'Connection refused') || str_contains($e->getMessage(), 'No such host is known')) {
                return response()->view('errors.db_offline', [], 500);
            }
        });
    })->create();

$app->useStoragePath(env('LARAVEL_STORAGE_PATH', $app->basePath('storage')));

return $app;
