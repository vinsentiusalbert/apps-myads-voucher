<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SessionController extends Controller
{
    public function create()
    {
        // Check if user already has valid session from API/SSO
        $accessToken = session('myads.access_token', '');
        
        if (trim($accessToken) !== '') {
            return redirect()->route('dashboard');
        }

        // Jika tidak ada session, tampilkan login page (SSO only)
        // Jangan abort karena bisa trigger redirect loop
        return view('errors.401');
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

        // Clear Laravel Auth state FIRST
        Auth::logout();

        // Then invalidate and regenerate session
        // This ensures session is completely cleared from database
        Session::flush();
        $request->session()->regenerate();

        return redirect()->route('login');
    }

    private function cleanApiMessage(string $message): string
    {
        $message = trim(strip_tags($message));

        return $message !== '' ? $message : 'Request tidak dapat diproses.';
    }
}
