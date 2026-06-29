<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\MyadsApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class SsoController extends Controller
{
    public function complete(Request $request, MyadsApiClient $client): RedirectResponse|Response
    {
        $accessToken = (string) ($request->query('access_token') ?? $request->query('token') ?? '');
        $refreshToken = (string) ($request->query('refresh_token') ?? '');
        $merchantId = (string) ($request->query('merchant_id') ?? $request->query('client_id') ?? '');
        $partnerId = (string) ($request->query('partner_id') ?? '');

        if (trim($accessToken) === '') {
            return $this->accessDenied();
        }

        if (trim($merchantId) === '') {
            return $this->accessDenied();
        }

        // 🔄 FETCH user data dari API (source of truth)
        $apiUser = null;
        try {
            $apiUserData = $client->getMe($accessToken);
            $apiUser = $apiUserData['data']['user'] ?? null;
        } catch (Throwable) {
            return $this->accessDenied();
        }

        // Store session tanpa DB
        $request->session()->regenerate();

        $request->session()->put([
            'myads.access_token' => $accessToken,
            'myads.refresh_token' => $refreshToken,
            'myads.merchant_id' => $merchantId,
            'myads.partner_id' => $partnerId,
            'myads.user' => [
                'id' => $apiUser['id'] ?? $merchantId,
                'name' => $apiUser['name'] ?? 'Merchant GoTo',
                'email' => $apiUser['email'] ?? "merchant-{$merchantId}@sso.prod",
            ],
        ]);

        try {
            $gwResp = $client->getGatewayToken($accessToken);
            $gwToken = $client->extractGatewayToken($gwResp);

            if ($gwToken === '') {
                throw new \RuntimeException('Gagal ambil token gateway (format response tidak sesuai).');
            }

            $request->session()->put('myads.gw_token', $gwToken);
        } catch (Throwable) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $this->accessDenied();
        }

        return redirect()->route('dashboard');
    }

    private function accessDenied(): Response
    {
        return response()->view('errors.401', [], 401);
    }
}
