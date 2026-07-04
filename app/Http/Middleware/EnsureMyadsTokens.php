<?php

namespace App\Http\Middleware;

use App\Services\MyadsApiClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsureMyadsTokens
{
    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->session();
        
        // Check session-based auth (SSO flow) - NO Laravel Auth needed
        $accessToken = (string) $session->get('myads.access_token', '');

        if ($this->isPublicRoute($request)) {
            return $next($request);
        }

        if (trim($accessToken) === '') {
            return response()->view('errors.401', [], 401);
        }

        $client = app(MyadsApiClient::class);
        $refreshToken = (string) $session->get('myads.refresh_token', '');

        // Refresh access token kalau expired
        if ($refreshToken !== '' && ($accessToken === '' || $this->isAccessTokenExpired($accessToken))) {
            try {
                $resp = $client->refreshAccessToken($refreshToken);
                $newAccessToken = (string) data_get($resp, 'data.access_token', '');
                if (trim($newAccessToken) !== '') {
                    $session->put('myads.access_token', $newAccessToken);
                    $accessToken = $newAccessToken;
                }
            } catch (Throwable) {
                // Leave the session intact; refresh will be retried later.
            }
        }

        if (trim((string) $session->get('myads.access_token', $accessToken)) === '') {
            return response()->view('errors.401', [], 401);
        }

        // Fetch gateway token kalau belum ada
        $gwToken = (string) $session->get('myads.gw_token', '');
        if (trim($gwToken) === '') {
            try {
                $freshAccessToken = (string) $session->get('myads.access_token', '');
                if (trim($freshAccessToken) !== '') {
                    $gwResp = $client->getGatewayToken($freshAccessToken);
                    $freshGwToken = $client->extractGatewayToken($gwResp);
                    if ($freshGwToken !== '') {
                        $session->put('myads.gw_token', $freshGwToken);
                        $gwToken = $freshGwToken;
                    }
                }
            } catch (Throwable) {
                // Optional token refresh; ignore failures.
            }
        }

        // // Admin tidak perlu gw_token — mereka tidak menggunakan fitur campaign API Gateway.
        // // Non-admin (merchant) wajib punya gw_token untuk mengakses halaman campaign.
        // $user = $session->get('myads.user');
        // $userRole = is_array($user) ? (string) ($user['role'] ?? 'user') : 'user';
        // $isAdmin = $userRole === 'admin';

        // if (! $isAdmin && trim($gwToken) === '') {
        //     return response()->view('errors.401', [], 401);
        // }

        return $next($request);

    }

    private function isPublicRoute(Request $request): bool
    {
        $routeName = $request->route()?->getName();
        if (is_string($routeName) && $routeName !== '') {
            return in_array($routeName, [
                'login',
                'login.store',
                'sso.complete',
                'register',
                'register.store',
                'password.request',
                'password.email',
                'password.reset',
                'password.store',
                'logout',
                'admin.dashboard',
            ], true);
        }

        $path = '/' . ltrim($request->path(), '/');

        return in_array($path, [
            '/',
            '/login',
            '/sso/complete',
            '/register',
            '/forgot-password',
        ], true) || str_starts_with($path, '/reset-password');
    }

    private function isAccessTokenExpired(string $token): bool
    {
        $payload = $this->decodeJwtPayload($token);
        if (! is_array($payload)) {
            return false;
        }

        $exp = $payload['exp'] ?? null;
        if (! is_numeric($exp)) {
            return false;
        }

        return time() >= ((int) $exp - 60);
    }

    private function decodeJwtPayload(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) < 2) {
            return null;
        }

        $payload = str_replace(['-', '_'], ['+', '/'], $parts[1]);
        $padding = strlen($payload) % 4;
        if ($padding > 0) {
            $payload .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($payload, true);
        if ($decoded === false) {
            return null;
        }

        $json = json_decode($decoded, true);
        return is_array($json) ? $json : null;
    }
}
