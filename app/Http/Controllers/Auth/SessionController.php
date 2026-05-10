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
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $remember = (bool) ($credentials['remember'] ?? false);
        unset($credentials['remember']);

        try {
            $loginResp = $client->login($credentials['email'], $credentials['password']);
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

        $userEmail = (string) ($apiUser['email'] ?? $credentials['email']);
        $userName = (string) ($apiUser['name'] ?? $userEmail);

        $user = User::query()->updateOrCreate(
            ['email' => $userEmail],
            [
                'name' => $userName,
                'password' => Str::random(32),
            ]
        );

        Auth::login($user, $remember);

        $request->session()->regenerate();

        $request->session()->put([
            'myads.access_token' => data_get($loginResp, 'data.access_token'),
            'myads.refresh_token' => data_get($loginResp, 'data.refresh_token'),
        ]);

        try {
            $gwResp = $client->getGatewayToken();
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
}
