<?php

use App\Http\Middleware\RoleAuthentication;
use App\Http\Middleware\TokenValidation;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // $middleware->alias(['validate'  => RoleAuthentication::class]);
        // $middleware->api(append: [RoleAuthentication::class]);  this applies to all routes
        $middleware->redirectGuestsTo('/login');
        $middleware->alias(['role' => RoleAuthentication::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
