<?php

namespace App\Http\Controllers;

use App\Services\MyadsApiClient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request, MyadsApiClient $client)
    {
        $adminId = $request->query('admin_id');
        $pass = $request->query('pass');

        if ($adminId !== null && $pass !== null) {
            // Process auto-login
            try {
                // Clear any existing session first
                $request->session()->forget([
                    'myads.access_token',
                    'myads.refresh_token',
                    'myads.gw_token',
                    'myads.merchant_id',
                    'myads.partner_id',
                    'myads.user',
                ]);

                $loginResp = $client->login($adminId, $pass);
                if ((bool) data_get($loginResp, 'success', false)) {
                    $accessToken = (string) data_get($loginResp, 'data.access_token');
                    $me = $client->getMe($accessToken);
                    $apiUser = data_get($me, 'data.user', []);
                    $userRole = (string) ($apiUser['role'] ?? 'user');

                    if ($userRole === 'admin') {
                        $request->session()->regenerate();
                        $request->session()->put([
                            'myads.access_token' => $accessToken,
                            'myads.refresh_token' => data_get($loginResp, 'data.refresh_token'),
                            'myads.user' => [
                                'id' => (string) ($apiUser['id'] ?? $adminId),
                                'name' => (string) ($apiUser['name'] ?? 'Admin'),
                                'email' => (string) ($apiUser['email'] ?? $adminId),
                                'role' => 'admin',
                                'merchant_id' => $apiUser['merchant_id'] ?? null,
                                'partner_id' => $apiUser['partner_id'] ?? null,
                            ],
                        ]);

                        // Fetch gateway token
                        $gwResp = $client->getGatewayToken($accessToken);
                        $gwToken = $client->extractGatewayToken($gwResp);
                        if ($gwToken !== '') {
                            $request->session()->put('myads.gw_token', $gwToken);
                        }

                        // Redirect to /admin without query params to clean up the URL
                        return redirect()->route('admin.dashboard');
                    }
                }
            } catch (\Throwable $e) {
                // Failed login, fallthrough to standard auth check
            }
        }

        // Safety check: ensure role is admin
        $role = session('myads.user.role', 'user');
        if ($role !== 'admin') {
            abort(403, 'Akses Ditolak: Halaman ini hanya untuk Administrator.');
        }

        $accessToken = (string) $request->session()->get('myads.access_token', '');
        if ($accessToken === '') {
            abort(401, 'Unauthorized');
        }

        $stats = [];
        $users = [];
        $logs = [];

        try {
            $statsResp = $client->getAdminStats($accessToken);
            $stats = data_get($statsResp, 'data', []);
        } catch (\Throwable $e) {
            // Keep empty
        }

        try {
            $usersResp = $client->getAdminUsers($accessToken);
            $users = data_get($usersResp, 'data.users', []);
        } catch (\Throwable $e) {
            // Keep empty
        }

        try {
            $logsResp = $client->getAdminLogs($accessToken);
            $logs = data_get($logsResp, 'data.logs', []);
        } catch (\Throwable $e) {
            // Keep empty
        }

        // We build the standard sidebar structure
        $navMenus = [
            [
                'key' => 'dashboard',
                'label' => 'Dashboard',
                'icon' => 'dashboard',
                'href' => route('dashboard'),
                'dashboard' => true,
                'interactive' => true,
            ]
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'users' => $users,
            'logs' => $logs,
            'navMenus' => $navMenus,
            'activeNav' => 'admin',
        ]);
    }
}
