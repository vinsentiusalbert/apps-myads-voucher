<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfApiSessionAuthenticated
{
    public function handle(Request $request, Closure $next, mixed ...$guards): Response
    {
        if ($request->session()->has('voucher')) {
            return redirect()->route('dashboard');
        }

        $user = $request->session()->get('myads.user');
        $accessToken = (string) $request->session()->get('myads.access_token', '');
        $gatewayToken = (string) $request->session()->get('myads.gw_token', '');

        if (is_array($user) && trim($accessToken) !== '' && trim($gatewayToken) !== '') {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }

}
