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

        // 🔄 FETCH user data dari API (untuk mendapatkan nama, email, role terbaru)
        // Jika API tidak dapat diakses, gunakan data fallback dari parameter URL
        $apiUser = null;
        try {
            $apiUserData = $client->getMe($accessToken);
            $apiUser = $apiUserData['data']['user'] ?? null;
        } catch (Throwable) {
            // API tidak dapat diakses — lanjutkan dengan data fallback dari URL params
            // Session tetap dibuat agar user bisa masuk
        }

        // Store session
        $request->session()->regenerate();

        $request->session()->put([
            'myads.access_token' => $accessToken,
            'myads.refresh_token' => $refreshToken,
            'myads.merchant_id' => $merchantId,
            'myads.partner_id' => $partnerId,
            'myads.user' => [
                'id'          => ($apiUser['id'] ?? null) ?: $merchantId,
                'name'        => ($apiUser['name'] ?? null) ?: 'Merchant GoTo',
                'email'       => ($apiUser['email'] ?? null) ?: "merchant-{$merchantId}@sso.prod",
                'role'        => ($apiUser['role'] ?? null) ?: 'user',
                'merchant_id' => ($apiUser['merchant_id'] ?? null) ?: $merchantId,
                'partner_id'  => ($apiUser['partner_id'] ?? null) ?: $partnerId,
            ],
        ]);

        try {
            $gwResp = $client->getGatewayToken($accessToken);
            $gwToken = $client->extractGatewayToken($gwResp);

            if ($gwToken !== '') {
                $request->session()->put('myads.gw_token', $gwToken);
            }
        } catch (Throwable) {
            // Gateway token gagal — tetap lanjutkan, middleware akan retry
        }

        return redirect()->route('dashboard');


    }

    private function accessDenied(): Response
    {
        return response()->view('errors.401', [], 401);
    }
}
