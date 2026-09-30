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
        // Check if user already has valid session from API
        $accessToken = session('myads.access_token', '');
        $gatewayToken = session('myads.gw_token', '');
        $role = session('myads.user.role', 'merchant');

        if (trim($accessToken) !== '') {
            if ($role === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            if (trim($gatewayToken) !== '') {
                return redirect()->route('dashboard');
            }
        }

        return view('auth.login');
    }

    public function store(Request $request, MyadsApiClient $client): RedirectResponse
    {
        $validated = $request->validate([
            'merchant_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'merchant_id.required' => 'Merchant ID wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $merchantId = trim($validated['merchant_id']);
        $password = $validated['password'];

        try {
            $loginResp = $client->login($merchantId, $password);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'merchant_id' => $this->cleanApiMessage($e->getMessage()),
            ]);
        }

        if (! (bool) data_get($loginResp, 'success', false)) {
            $message = $this->cleanApiMessage((string) (data_get($loginResp, 'message') ?: 'Merchant ID atau password belum sesuai.'));
            throw ValidationException::withMessages([
                'merchant_id' => $message,
            ]);
        }

        $accessToken = (string) data_get($loginResp, 'data.access_token');
        if (trim($accessToken) === '') {
            throw ValidationException::withMessages([
                'merchant_id' => 'Sesi login tidak dapat dibuat. Silakan coba kembali.',
            ]);
        }
        $refreshToken = (string) data_get($loginResp, 'data.refresh_token');
        $apiUser = (array) data_get($loginResp, 'data.user', []);

        $userRole = (string) ($apiUser['role'] ?? 'merchant');
        $userName = (string) ($apiUser['name'] ?? $merchantId);
        $userEmail = (string) ($apiUser['email'] ?? '');
        $resolvedMerchantId = (string) ($apiUser['merchant_id'] ?? $merchantId);
        $partnerId = (string) ($apiUser['partner_id'] ?? 'shopee');

        $request->session()->regenerate();

        $request->session()->put([
            'myads.access_token' => $accessToken,
            'myads.refresh_token' => $refreshToken,
            'myads.merchant_id' => $resolvedMerchantId,
            'myads.partner_id' => $partnerId,
            'myads.user' => [
                'id' => (string) ($apiUser['id'] ?? $resolvedMerchantId),
                'name' => $userName,
                'email' => $userEmail,
                'role' => $userRole,
                'merchant_id' => $resolvedMerchantId,
                'partner_id' => $partnerId,
            ],
        ]);

        // If admin, redirect to admin dashboard
        if ($userRole === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // For merchant, get gateway token
        try {
            $gwResp = $client->getGatewayToken($accessToken);
            $gwToken = $client->extractGatewayToken($gwResp);
            if ($gwToken !== '') {
                $request->session()->put('myads.gw_token', $gwToken);
            }
        } catch (\Throwable) {
            // EnsureMyadsTokens will retry if needed
        }

        return redirect()->intended(route('dashboard'));
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
            'myads.membership',
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
