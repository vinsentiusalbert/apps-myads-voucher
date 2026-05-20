<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function create()
    {
        // Check if user already has session from API/SSO
        if (session()->has('myads.access_token')) {
            return redirect()->route('dashboard');
        }

        abort(401, 'Unauthorized. Access must be via SSO.');
    }

    public function store(Request $request)
    {
        abort(401, 'Unauthorized. Access must be via SSO.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget([
            'myads.access_token',
            'myads.refresh_token',
            'myads.gw_token',
            'myads.merchant_id',
            'myads.partner_id',
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
