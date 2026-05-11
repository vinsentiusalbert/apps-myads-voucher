<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MyadsApiClient;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    public function create(Request $request, MyadsApiClient $client): ViewContract
    {
        $merchantId = trim((string) $request->query('merchant_id', ''));
        $partnerId = trim((string) ($request->query('partner_id') ?? $request->query('parent_id', '')));

        if ($merchantId === '' || $partnerId === '') {
            abort(403, 'Merchant ID dan Partner ID wajib disertakan untuk membuka halaman register.');
        }

        if (mb_strlen($merchantId) > 128 || mb_strlen($partnerId) > 128) {
            abort(422, 'Merchant ID atau Partner ID terlalu panjang.');
        }

        try {
            $contextResp = $client->registrationContext(
                $merchantId,
                $partnerId
            );
        } catch (\Throwable $e) {
            abort(422, $e->getMessage());
        }

        if (! (bool) data_get($contextResp, 'success', false)) {
            abort(422, (string) (data_get($contextResp, 'message') ?: 'Konteks register tidak valid.'));
        }

        return view('auth.register', [
            'merchantId' => (string) data_get($contextResp, 'data.merchant_id', $merchantId),
            'partnerId' => (string) data_get($contextResp, 'data.partner_id', $partnerId),
        ]);
    }

    public function store(Request $request, MyadsApiClient $client): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
            'merchant_id' => ['required', 'string', 'max:128'],
            'partner_id' => ['required', 'string', 'max:128'],
        ]);

        try {
            $contextResp = $client->registrationContext(
                $validated['merchant_id'],
                $validated['partner_id']
            );
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'merchant_id' => $e->getMessage(),
            ]);
        }

        if (! (bool) data_get($contextResp, 'success', false)) {
            throw ValidationException::withMessages([
                'merchant_id' => (string) (data_get($contextResp, 'message') ?: 'Konteks register tidak valid.'),
            ]);
        }

        try {
            $registerResp = $client->register(
                $validated['email'],
                $validated['password'],
                $validated['name'],
                $validated['merchant_id'],
                $validated['partner_id']
            );
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'email' => $e->getMessage(),
            ]);
        }

        if (! (bool) data_get($registerResp, 'success', false)) {
            $message = (string) (data_get($registerResp, 'message') ?: 'Register gagal.');

            throw ValidationException::withMessages([
                'email' => $message,
            ]);
        }

        try {
            $loginResp = $client->login($validated['email'], $validated['password']);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'email' => $e->getMessage(),
            ]);
        }

        if (! (bool) data_get($loginResp, 'success', false)) {
            $message = (string) (data_get($loginResp, 'message') ?: 'Email atau password belum sesuai.');

            throw ValidationException::withMessages([
                'email' => $message,
            ]);
        }

        $apiUser = (array) data_get($loginResp, 'data.user', []);
        $userEmail = (string) ($apiUser['email'] ?? $validated['email']);
        $userName = (string) ($apiUser['name'] ?? $validated['name'] ?? $userEmail);

        $user = User::query()->updateOrCreate(
            ['email' => $userEmail],
            [
                'name' => $userName,
                'merchant_id' => $validated['merchant_id'],
                'partner_id' => $validated['partner_id'],
                // Password lokal tidak dipakai untuk login (login via API), tapi kolom wajib ada.
                'password' => Str::random(32),
            ]
        );

        Auth::login($user);
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
                'email' => $e->getMessage(),
            ]);
        }

        return redirect()->route('dashboard');
    }
}
