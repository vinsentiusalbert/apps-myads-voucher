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

        // Hanya cek access_token dan user — gw_token tidak wajib saat login
        // (endpoint yang butuh gw_token akan error sendiri jika dipanggil tanpa token)
        if (! is_array($user) || trim($accessToken) === '') {
            return response()->view('errors.401', [], 401);
        }

        return $next($request);
    }
}

