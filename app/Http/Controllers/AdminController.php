<?php

namespace App\Http\Controllers;

use App\Services\MyadsApiClient;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class AdminController extends Controller
{
    public function index(Request $request, MyadsApiClient $client)
    {
        $adminId = $request->query('admin_id');
        $pass = $request->query('pass');

        if ($adminId !== null && $pass !== null) {
            try {
                $loginResp = $client->login($adminId, $pass);

                if (! (bool) data_get($loginResp, 'success', false)) {
                    $msg = data_get($loginResp, 'message', 'Login gagal.');
                    abort(401, 'Admin login ditolak: ' . $msg);
                }

                $accessToken = (string) data_get($loginResp, 'data.access_token');
                $me = $client->getMe($accessToken);
                $apiUser = data_get($me, 'data.user', []);
                $userRole = (string) ($apiUser['role'] ?? 'user');

                if ($userRole !== 'admin') {
                    abort(403, 'Akun ini tidak memiliki hak akses admin.');
                }

                // Invalidate old session dan buat session ID baru yang bersih
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $request->session()->put([
                    'myads.access_token'  => $accessToken,
                    'myads.refresh_token' => data_get($loginResp, 'data.refresh_token'),
                    'myads.user'          => [
                        'id'          => (string) ($apiUser['id'] ?? $adminId),
                        'name'        => (string) ($apiUser['name'] ?? 'Admin'),
                        'email'       => (string) ($apiUser['email'] ?? $adminId),
                        'role'        => 'admin',
                        'merchant_id' => $apiUser['merchant_id'] ?? null,
                        'partner_id'  => $apiUser['partner_id'] ?? null,
                    ],
                ]);



                // Redirect ke /admin tanpa query params (bersihkan URL dari password)
                return redirect()->route('admin.dashboard');

            } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
                throw $e; // Re-throw abort()
            } catch (\Throwable $e) {
                abort(503, 'Tidak dapat terhubung ke API backend. Pastikan API server sedang berjalan. Detail: ' . $e->getMessage());
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

        $reportingData = [];
        try {
            $repResp = $client->getAdminReportingEvents($accessToken);
            $reportingData = data_get($repResp, 'data', []);
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
            'reportingData' => $reportingData,
            'navMenus' => $navMenus,
            'activeNav' => 'admin',
        ]);
    }
}
