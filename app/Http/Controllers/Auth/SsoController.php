<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\MyadsApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class SsoController extends Controller
{
    public function complete(Request $request, MyadsApiClient $client): RedirectResponse
    {
        $accessToken = (string) ($request->query('access_token') ?? $request->query('token') ?? '');
        $refreshToken = (string) ($request->query('refresh_token') ?? '');
        $merchantId = (string) ($request->query('merchant_id') ?? $request->query('client_id') ?? '');
        $partnerId = (string) ($request->query('partner_id') ?? '');

        if (trim($accessToken) === '') {
            abort(401, 'Unauthorized. Access must be via SSO.');
        }

        if (trim($merchantId) === '') {
            abort(422, 'Merchant ID tidak ditemukan.');
        }

        // 🔄 FETCH user data dari API (source of truth)
        $apiUser = null;
        try {
            $apiUserData = $client->getMe($accessToken);
            $apiUser = $apiUserData['data']['user'] ?? null;
        } catch (Throwable) {
            // Fallback: API user fetch optional
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

            if ($gwToken !== '') {
                $request->session()->put('myads.gw_token', $gwToken);
            }
        } catch (Throwable) {
            // Gateway token is optional; will be refreshed later if needed.
        }

        return redirect()->route('dashboard');
    }
}
