<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MyadsApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
        try {
            $apiUserData = $client->getMe($accessToken);
            $apiUser = $apiUserData['data']['user'] ?? null;
        } catch (Throwable) {
            $apiUser = null;
        }

        // Sync/create user di UI database dari API data
        $user = User::query()->where('merchant_id', $merchantId)->first();

        if (! $user && $apiUser) {
            // Create dari API data
            $user = User::query()->create([
                'name' => $apiUser['name'] ?? 'Merchant SSO',
                'email' => $apiUser['email'] ?? 'sso-' . Str::random(8) . '@goto.local',
                'password' => Str::random(32),
                'merchant_id' => $merchantId,
                'partner_id' => $partnerId !== '' ? $partnerId : null,
            ]);
        } elseif ($user) {
            // Update existing user jika ada data baru dari API
            $updateData = [];
            if ($apiUser && isset($apiUser['name'])) {
                $updateData['name'] = $apiUser['name'];
            }
            if ($partnerId !== '' && $user->partner_id !== $partnerId) {
                $updateData['partner_id'] = $partnerId;
            }
            if (!empty($updateData)) {
                $user->update($updateData);
            }
        }

        // Fallback: create dengan default identity kalau masih tidak ada
        if (! $user) {
            [$name, $email] = $this->nextDefaultIdentity();
            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => Str::random(32),
                'merchant_id' => $merchantId,
                'partner_id' => $partnerId !== '' ? $partnerId : null,
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        $request->session()->put([
            'myads.access_token' => $accessToken,
            'myads.refresh_token' => $refreshToken,
            'myads.merchant_id' => $merchantId,
            'myads.partner_id' => $partnerId,
        ]);

        try {
            $gwResp = $client->getGatewayToken();
            $gwToken = $client->extractGatewayToken($gwResp);

            if ($gwToken !== '') {
                $request->session()->put('myads.gw_token', $gwToken);
            }
        } catch (Throwable) {
            // Gateway token is optional; will be refreshed later if needed.
        }

        return redirect()->route('dashboard');
    }

    private function nextDefaultIdentity(): array
    {
        $emails = User::query()
            ->where('email', 'like', 'merchantgoto%mg@gmail.com')
            ->pluck('email')
            ->all();

        $max = 0;
        foreach ($emails as $email) {
            if (! is_string($email)) {
                continue;
            }

            if (preg_match('/^merchantgoto(\d+)mg@gmail\.com$/i', $email, $matches)) {
                $seq = (int) $matches[1];
                if ($seq > $max) {
                    $max = $seq;
                }
            }
        }

        $next = $max + 1;
        $suffix = str_pad((string) $next, 2, '0', STR_PAD_LEFT);

        return [
            'MerchantGoto' . $suffix . 'MG',
            'merchantgoto' . $suffix . 'mg@gmail.com',
        ];
    }
}
