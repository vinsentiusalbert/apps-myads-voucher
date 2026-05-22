<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfApiSessionAuthenticated
{
    public function handle(Request $request, Closure $next, mixed ...$guards): Response
    {
        $user = $request->session()->get('myads.user');
        $accessToken = (string) $request->session()->get('myads.access_token', '');

        if (is_array($user) && trim($accessToken) !== '') {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }

}
