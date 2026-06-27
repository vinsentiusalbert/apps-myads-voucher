<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiSessionAuthenticate
{
    public function handle(Request $request, Closure $next, mixed ...$guards): Response
    {
        $user = $request->session()->get('myads.user');
        $accessToken = (string) $request->session()->get('myads.access_token', '');
        $gatewayToken = (string) $request->session()->get('myads.gw_token', '');

        if (! is_array($user) || trim($accessToken) === '' || trim($gatewayToken) === '') {
            return response()->view('errors.401', [], 401);
        }

        return $next($request);
    }
}
