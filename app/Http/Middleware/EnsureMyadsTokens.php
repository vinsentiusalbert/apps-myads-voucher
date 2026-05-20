<?php

namespace App\Http\Middleware;

use App\Services\MyadsApiClient;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsureMyadsTokens
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $session = $request->session();
        $client = app(MyadsApiClient::class);

        $accessToken = (string) $session->get('myads.access_token', '');
        $refreshToken = (string) $session->get('myads.refresh_token', '');

        if ($refreshToken !== '' && ($accessToken === '' || $this->isAccessTokenExpired($accessToken))) {
            try {
                $resp = $client->refreshAccessToken($refreshToken);
                $newAccessToken = (string) data_get($resp, 'data.access_token', '');
                if (trim($newAccessToken) !== '') {
                    $session->put('myads.access_token', $newAccessToken);
                }
            } catch (Throwable) {
                // Leave the session intact; refresh will be retried later.
            }
        }

        $gwToken = (string) $session->get('myads.gw_token', '');
        if ($gwToken === '') {
            try {
                $gwResp = $client->getGatewayToken();
                $freshGwToken = $client->extractGatewayToken($gwResp);
                if ($freshGwToken !== '') {
                    $session->put('myads.gw_token', $freshGwToken);
                }
            } catch (Throwable) {
                // Optional token refresh; ignore failures.
            }
        }

        return $next($request);
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
