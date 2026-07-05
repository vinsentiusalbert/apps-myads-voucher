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
        $middleware->alias([
            'api-session-guest' => \App\Http\Middleware\RedirectIfApiSessionAuthenticated::class,
            'api-session-auth' => \App\Http\Middleware\ApiSessionAuthenticate::class,
            'admin-session-auth' => \App\Http\Middleware\EnsureAdminSession::class,
        ]);
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureMyadsTokens::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
