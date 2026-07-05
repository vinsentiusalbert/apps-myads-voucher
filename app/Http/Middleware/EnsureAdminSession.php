<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->session();

        // Jika sedang melakukan login (memiliki parameter admin_id dan pass), izinkan lewat ke controller
        if ($request->has('admin_id') && $request->has('pass')) {
            return $next($request);
        }

        $user = $session->get('myads.user');
        $userRole = is_array($user) ? (string) ($user['role'] ?? 'user') : 'user';
        $accessToken = (string) $session->get('myads.access_token', '');

        if ($userRole !== 'admin' || trim($accessToken) === '') {
            return response()->view('errors.401', [], 401);
        }

        return $next($request);
    }
}
