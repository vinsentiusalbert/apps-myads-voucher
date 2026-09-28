<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\MyadsApiClient;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    public function create(Request $request): ViewContract|RedirectResponse
    {
        $merchantId = trim((string) $request->query('merchant_id', ''));
        $partnerId = trim((string) ($request->query('partner_id') ?? $request->query('parent_id', 'shopee')));

        return view('auth.register', [
            'merchantId' => $merchantId,
            'partnerId' => $partnerId !== '' ? $partnerId : 'shopee',
        ]);
    }

    public function store(Request $request, MyadsApiClient $client): RedirectResponse
    {
        $validated = $request->validate([
            'merchant_id' => ['required', 'string', 'max:128'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', 'min:6'],
            'partner_id' => ['nullable', 'string', 'max:128'],
        ], [
            'merchant_id.required' => 'Merchant ID wajib diisi.',
            'name.required' => 'Nama lengkap / nama toko wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        $partnerId = ! empty($validated['partner_id']) ? $validated['partner_id'] : 'shopee';

        try {
            $registerResp = $client->register(
                $validated['email'],
                $validated['password'],
                $validated['name'],
                $validated['merchant_id'],
                $partnerId
            );
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'merchant_id' => $this->cleanApiMessage($e->getMessage()),
            ]);
        }

        if (! (bool) data_get($registerResp, 'success', false)) {
            $message = $this->cleanApiMessage((string) (data_get($registerResp, 'message') ?: 'Registrasi gagal.'));

            throw ValidationException::withMessages([
                'merchant_id' => $message,
            ]);
        }

        // Auto login after registration
        try {
            $loginResp = $client->login($validated['merchant_id'], $validated['password']);
            if ((bool) data_get($loginResp, 'success', false)) {
                $accessToken = (string) data_get($loginResp, 'data.access_token');
                $refreshToken = (string) data_get($loginResp, 'data.refresh_token');
                $apiUser = (array) data_get($loginResp, 'data.user', []);

                $request->session()->regenerate();
                $request->session()->put([
                    'myads.access_token' => $accessToken,
                    'myads.refresh_token' => $refreshToken,
                    'myads.merchant_id' => $validated['merchant_id'],
                    'myads.partner_id' => $partnerId,
                    'myads.user' => [
                        'id' => (string) ($apiUser['id'] ?? $validated['merchant_id']),
                        'name' => (string) ($apiUser['name'] ?? $validated['name']),
                        'email' => (string) ($apiUser['email'] ?? $validated['email']),
                        'role' => (string) ($apiUser['role'] ?? 'merchant'),
                        'merchant_id' => $validated['merchant_id'],
                        'partner_id' => $partnerId,
                    ],
                ]);

                try {
                    $gwResp = $client->getGatewayToken($accessToken);
                    $gwToken = $client->extractGatewayToken($gwResp);
                    if ($gwToken !== '') {
                        $request->session()->put('myads.gw_token', $gwToken);
                    }
                } catch (\Throwable) {
                }

                return redirect()->route('dashboard');
            }
        } catch (\Throwable) {
            // Auto login failed, redirect to login page with success flash
        }

        return redirect()->route('login')->with('status', 'Registrasi berhasil! Silakan masuk dengan Merchant ID dan password Anda.');
    }

    private function cleanApiMessage(string $message): string
    {
        $message = trim(strip_tags($message));

        return $message !== '' ? $message : 'Request tidak dapat diproses.';
    }
}
