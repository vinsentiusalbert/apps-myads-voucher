<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MyadsApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

        $remember = (bool) ($credentials['remember'] ?? false);
        unset($credentials['remember']);

        $merchantId = trim((string) ($credentials['merchant_id'] ?? ''));
        $partnerId = trim((string) ($credentials['partner_id'] ?? ''));
        unset($credentials['merchant_id'], $credentials['partner_id']);

        if (($merchantId !== '' || $partnerId !== '') && ($merchantId === '' || $partnerId === '')) {
            throw ValidationException::withMessages([
                'email' => 'Merchant ID dan Partner ID wajib lengkap untuk login SSO.',
            ]);
        }

        if ($merchantId !== '' && $partnerId !== '') {
            $hasExistingSsoUser = User::query()
                ->where('merchant_id', $merchantId)
                ->where('partner_id', $partnerId)
                ->exists();

            if (! $hasExistingSsoUser) {
                throw ValidationException::withMessages([
                    'email' => 'Akun SSO belum terdaftar. Silakan lengkapi registrasi terlebih dahulu.',
                ]);
            }
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

        if ($merchantId !== '' && $partnerId !== '') {
            $user = User::query()
                ->where('email', $userEmail)
                ->where('merchant_id', $merchantId)
                ->where('partner_id', $partnerId)
                ->first();

            if (! $user) {
                throw ValidationException::withMessages([
                    'email' => 'ID tidak sesuai dengan akun SSO yang sudah terdaftar.',
                ]);
            }

            $user->forceFill([
                'name' => $userName,
            ])->save();
        } else {
            $user = User::query()->updateOrCreate(
                ['email' => $userEmail],
                [
                    'name' => $userName,
                    'password' => Str::random(32),
                ]
            );
        }

        Auth::login($user, $remember);

        $request->session()->regenerate();

        $request->session()->put([
            'myads.access_token' => data_get($loginResp, 'data.access_token'),
            'myads.refresh_token' => data_get($loginResp, 'data.refresh_token'),
        ]);

        try {
            $gwResp = $client->getGatewayToken((string) data_get($loginResp, 'data.access_token'));
            $gwToken = $client->extractGatewayToken($gwResp);

            if ($gwToken === '') {
                throw new \RuntimeException('Gagal ambil token gateway (format response tidak sesuai).');
            }

            $request->session()->put('myads.gw_token', $gwToken);
        } catch (\Throwable $e) {
            Auth::logout();
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
        Auth::logout();

        $request->session()->forget([
            'myads.access_token',
            'myads.refresh_token',
            'myads.gw_token',
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
