<?php

namespace App\Providers;

use App\Auth\ApiSessionUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('api-session', fn (): ApiSessionUserProvider => new ApiSessionUserProvider());

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Http\Client\Events\ResponseReceived::class,
            function (\Illuminate\Http\Client\Events\ResponseReceived $event) {
                $request = $event->request;
                $response = $event->response;
                $url = $request->url();
                
                if (str_contains($url, '/api/')) {
                    $payload = $request->isJson() ? json_decode($request->body(), true) : $request->body();
                    $responseBody = null;
                    $contentType = $response->header('Content-Type');
                    if (str_contains($contentType, 'application/json')) {
                        $responseBody = $response->json();
                    } else if (str_contains($contentType, 'application/pdf')) {
                        $responseBody = '[PDF Binary Data - ' . strlen($response->body()) . ' bytes]';
                    } else {
                        $responseBody = $response->body();
                    }

                    \App\Services\ApiLogCollector::add(
                        $request->method(),
                        $url,
                        $payload,
                        $response->status(),
                        $responseBody
                    );
                }
            }
        );

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Http\Client\Events\ConnectionFailed::class,
            function (\Illuminate\Http\Client\Events\ConnectionFailed $event) {
                $request = $event->request;
                $url = $request->url();
                if (str_contains($url, '/api/')) {
                    \App\Services\ApiLogCollector::add(
                        $request->method(),
                        $url,
                        $request->isJson() ? json_decode($request->body(), true) : $request->body(),
                        500,
                        'Connection Failed / Network Error'
                    );
                }
            }
        );
    }
}
