<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\MyadsApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    public function create()
    {
        // Check if user already has valid session from API/SSO
        $accessToken = session('myads.access_token', '');
        $gatewayToken = session('myads.gw_token', '');

        if (trim($accessToken) !== '' && trim($gatewayToken) !== '') {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function store(Request $request, MyadsApiClient $client): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        try {
            $loginResp = $client->login($validated['email'], $validated['password']);
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

        // Fetch user profile from database to get the up-to-date role, name, merchant etc.
        $accessToken = (string) data_get($loginResp, 'data.access_token');
        try {
            $me = $client->getMe($accessToken);
            $apiUser = data_get($me, 'data.user', []);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'email' => 'Gagal mengambil informasi profil akun.',
            ]);
        }

        $userEmail = (string) ($apiUser['email'] ?? $validated['email']);
        $userName = (string) ($apiUser['name'] ?? $userEmail);
        $userRole = (string) ($apiUser['role'] ?? 'user');

        $request->session()->regenerate();

        $request->session()->put([
            'myads.access_token' => $accessToken,
            'myads.refresh_token' => data_get($loginResp, 'data.refresh_token'),
            'myads.user' => [
                'id' => (string) ($apiUser['id'] ?? $userEmail),
                'name' => $userName,
                'email' => $userEmail,
                'role' => $userRole,
                'merchant_id' => $apiUser['merchant_id'] ?? null,
                'partner_id' => $apiUser['partner_id'] ?? null,
            ],
        ]);

        try {
            $gwResp = $client->getGatewayToken($accessToken);
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

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        // Clear all myads session data
        $request->session()->forget([
            'myads.access_token',
            'myads.refresh_token',
            'myads.gw_token',
            'myads.merchant_id',
            'myads.partner_id',
            'myads.user',
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/');
    }

    private function cleanApiMessage(string $message): string
    {
        $message = trim(strip_tags($message));

        return $message !== '' ? $message : 'Request tidak dapat diproses.';
    }
}
