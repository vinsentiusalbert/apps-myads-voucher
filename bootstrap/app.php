<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        $exceptions->render(function (\RuntimeException $e, Request $request): ?Response {
            if ($request->expectsJson() || ! $request->isMethod('GET')) {
                return null;
            }

            $isTokenExpiredMessage = function (string $message): bool {
                $normalized = strtolower($message);

                foreach ([
                    'token expired',
                    'session expired',
                    'token sudah kadaluarsa',
                    'token akses tidak valid',
                    'silakan login ulang',
                    'unauthorized',
                ] as $needle) {
                    if (str_contains($normalized, $needle)) {
                        return true;
                    }
                }

                return false;
            };

            $message = trim($e->getMessage());
            if ($message === '' || ! $isTokenExpiredMessage($message)) {
                return null;
            }

            try {
                $request->session()->forget([
                    'myads.access_token',
                    'myads.refresh_token',
                    'myads.gw_token',
                    'myads.merchant_id',
                    'myads.partner_id',
                    'myads.user',
                ]);
                $request->session()->regenerateToken();
            } catch (\Throwable) {
                // Ignore session cleanup failure and keep rendering the custom page.
            }

            return response()->view('errors.token-expired', [
                'message' => $message,
            ], 401);
        });
    })->create();
