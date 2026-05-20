<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiSessionAuthenticate
{
    public function handle(Request $request, Closure $next, mixed ...$guards): Response
    {
        $user = $request->session()->get('myads.user');
        $accessToken = (string) $request->session()->get('myads.access_token', '');

        if (! is_array($user) || trim($accessToken) === '') {
            return redirect()->route('login');
        }

        Auth::setUser(new GenericUser([
            'id' => $this->sessionAuthId($user),
            'api_user_id' => (string) ($user['id'] ?? ''),
            'name' => (string) ($user['name'] ?? $user['email'] ?? 'MyAds User'),
            'email' => (string) ($user['email'] ?? ''),
            'merchant_id' => (string) ($user['merchant_id'] ?? ''),
            'partner_id' => (string) ($user['partner_id'] ?? ''),
        ]));

        return $next($request);
    }

    private function sessionAuthId(array $user): int
    {
        $source = (string) ($user['id'] ?? $user['email'] ?? 'api-user');

        return ((int) sprintf('%u', crc32($source)) % 2147483647) + 1;
    }
}
