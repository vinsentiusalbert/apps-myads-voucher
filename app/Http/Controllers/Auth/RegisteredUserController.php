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

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request, MyadsApiClient $client): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        try {
            $registerResp = $client->register(
                $validated['email'],
                $validated['password'],
                $validated['name']
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

        return redirect()->route('dashboard');
    }
}
