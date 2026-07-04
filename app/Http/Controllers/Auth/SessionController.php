<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        // Jika tidak ada session, tampilkan login page (SSO only)
        // Jangan abort karena bisa trigger redirect loop
        return response()->view('errors.401', [], 401);
    }

    public function store(Request $request)
    {
        abort(401, 'Unauthorized. Access must be via SSO.');
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
