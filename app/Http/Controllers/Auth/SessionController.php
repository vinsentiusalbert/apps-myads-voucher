<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\MyadsApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, MyadsApiClient $client): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
            'merchant_id' => ['nullable', 'string', 'max:128'],
            'partner_id' => ['nullable', 'string', 'max:128'],
        ]);

        unset($credentials['remember']);

        $merchantId = trim((string) ($credentials['merchant_id'] ?? ''));
        $partnerId = trim((string) ($credentials['partner_id'] ?? ''));
        unset($credentials['merchant_id'], $credentials['partner_id']);

        if (($merchantId !== '' || $partnerId !== '') && ($merchantId === '' || $partnerId === '')) {
            throw ValidationException::withMessages([
                'email' => 'Merchant ID dan Partner ID wajib lengkap untuk login SSO.',
            ]);
        }

        try {
            $loginResp = $client->login($credentials['email'], $credentials['password']);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'email' => $this->cleanApiMessage($e->getMessage()),
            ]);
        }

        if (! (bool) data_get($loginResp, 'success', false)) {
            $message = $this->cleanApiMessage((string) (data_get($loginResp, 'message') ?: 'Email atau password belum sesuai.'));

            throw ValidationException::withMessages([
                'email' => $message,
            ]);
        }

        $apiUser = (array) data_get($loginResp, 'data.user', []);

        $userEmail = (string) ($apiUser['email'] ?? $credentials['email']);
        $userName = (string) ($apiUser['name'] ?? $userEmail);

        $request->session()->regenerate();

        $request->session()->put([
            'myads.access_token' => data_get($loginResp, 'data.access_token'),
            'myads.refresh_token' => data_get($loginResp, 'data.refresh_token'),
            'myads.user' => [
                'id' => (string) ($apiUser['id'] ?? $apiUser['user_id'] ?? $userEmail),
                'name' => $userName,
                'email' => $userEmail,
                'merchant_id' => (string) ($apiUser['merchant_id'] ?? $merchantId),
                'partner_id' => (string) ($apiUser['partner_id'] ?? $partnerId),
            ],
        ]);

        try {
            $gwResp = $client->getGatewayToken((string) data_get($loginResp, 'data.access_token'));
            $gwToken = $client->extractGatewayToken($gwResp);

            if ($gwToken === '') {
                throw new \RuntimeException('Gagal ambil token gateway (format response tidak sesuai).');
            }

            $request->session()->put('myads.gw_token', $gwToken);
        } catch (\Throwable $e) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => $this->cleanApiMessage($e->getMessage()),
            ]);
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget([
            'myads.access_token',
            'myads.refresh_token',
            'myads.gw_token',
            'myads.user',
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function cleanApiMessage(string $message): string
    {
        $message = trim(strip_tags($message));

        return $message !== '' ? $message : 'Request tidak dapat diproses.';
    }
}
