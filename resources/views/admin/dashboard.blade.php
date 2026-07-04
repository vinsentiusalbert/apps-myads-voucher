@extends('layouts.portal')

@section('content')
    <section class="portal-hero" style="margin-bottom: 2rem;">
        <div class="portal-welcome">
            <p class="portal-welcome__lead">Monitoring System,</p>
            <h1 class="portal-welcome__name">Admin Control Panel</h1>
            <p class="portal-welcome__meta">Pantau status pengguna, status API, latensi dan log secara real-time.</p>
        </div>
    </section>

    <!-- Stats Grid -->
    <section class="portal-card" style="margin-bottom: 2.5rem; padding: 2rem;">
        <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem; font-weight: 700; color: #1f2937;">Statistik Sistem & API</h2>
        <div class="portal-stats" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.5rem;">
            <div class="portal-stats__item">
                <p class="portal-stats__value" style="color: #4f46e5;">{{ $stats['total_users'] ?? 0 }}</p>
                <p class="portal-stats__label">Total Pengguna</p>
            </div>
            <div class="portal-stats__item">
                <p class="portal-stats__value" style="color: #10b981;">{{ $stats['success_calls'] ?? 0 }}</p>
                <p class="portal-stats__label">API Hits Sukses (2xx)</p>
            </div>
            <div class="portal-stats__item">
                <p class="portal-stats__value" style="color: #ef4444;">{{ $stats['error_calls'] ?? 0 }}</p>
                <p class="portal-stats__label">API Hits Error (4xx/5xx)</p>
            </div>
            <div class="portal-stats__item">
                <p class="portal-stats__value" style="color: #f59e0b;">{{ $stats['avg_latency_ms'] ?? 0 }} ms</p>
                <p class="portal-stats__label">Rata-rata Latensi</p>
            </div>
        </div>
    </section>

    <!-- Tab Section / Grid Split -->
    <div style="display: grid; grid-template-columns: 1fr; gap: 2.5rem;">
        <!-- Users Monitoring -->
        <section class="portal-card" style="padding: 2rem; overflow-x: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin: 0;">Monitoring Pengguna & Merchant</h2>
                <span style="font-size: 0.875rem; color: #6b7280; font-weight: 500;">Menampilkan {{ count($users) }} user terdaftar</span>
            </div>
            
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #e5e7eb; color: #374151; font-weight: 600;">
                        <th style="padding: 0.75rem 1rem;">Nama</th>
                        <th style="padding: 0.75rem 1rem;">Email</th>
                        <th style="padding: 0.75rem 1rem;">Merchant ID</th>
                        <th style="padding: 0.75rem 1rem;">Role</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">Saldo Refund</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">Terdaftar Pada</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr style="border-bottom: 1px solid #f3f4f6; color: #4b5563;">
                            <td style="padding: 1rem; font-weight: 600; color: #111827;">{{ $user['name'] ?: '-' }}</td>
                            <td style="padding: 1rem;">{{ $user['email'] }}</td>
                            <td style="padding: 1rem;"><code style="background: #f3f4f6; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.75rem;">{{ $user['merchant_id'] ?: 'None' }}</code></td>
                            <td style="padding: 1rem;">
                                <span style="background-color: {{ $user['role'] === 'admin' ? '#fee2e2' : '#e0f2fe' }}; color: {{ $user['role'] === 'admin' ? '#991b1b' : '#0369a1' }}; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                                    {{ $user['role'] }}
                                </span>
                            </td>
                            <td style="padding: 1rem; text-align: right; font-weight: 600; color: #10b981;">
                                Rp {{ number_format((float) ($user['refund_balance'] ?? 0), 0, ',', '.') }}
                            </td>
                            <td style="padding: 1rem; text-align: right; color: #9ca3af;">
                                {{ $user['created_at'] ? \Carbon\Carbon::parse($user['created_at'])->format('d M Y H:i') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 2rem; text-align: center; color: #9ca3af;">Belum ada user yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <!-- API Logs Monitoring -->
        <section class="portal-card" style="padding: 2rem; overflow-x: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin: 0;">Log Aktivitas API (100 Request Terakhir)</h2>
                <span style="font-size: 0.875rem; color: #6b7280; font-weight: 500;">Status Terkini</span>
            </div>

            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #e5e7eb; color: #374151; font-weight: 600;">
                        <th style="padding: 0.75rem 1rem;">Method</th>
                        <th style="padding: 0.75rem 1rem;">Endpoint</th>
                        <th style="padding: 0.75rem 1rem;">Status</th>
                        <th style="padding: 0.75rem 1rem;">IP Address</th>
                        <th style="padding: 0.75rem 1rem;">User Email</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">Durasi (ms)</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $status = (int) $log['status'];
                            $statusColor = '#10b981'; // Green for 2xx
                            if ($status >= 400) $statusColor = '#ef4444'; // Red for errors
                            elseif ($status >= 300) $statusColor = '#f59e0b'; // Amber for redirects
                        @endphp
                        <tr style="border-bottom: 1px solid #f3f4f6; color: #4b5563;">
                            <td style="padding: 1rem; font-weight: 700; color: #374151;">
                                <span style="font-family: monospace;">{{ $log['method'] }}</span>
                            </td>
                            <td style="padding: 1rem; font-family: monospace; font-size: 0.75rem; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $log['url'] }}
                            </td>
                            <td style="padding: 1rem; font-weight: 700; color: {{ $statusColor }};">
                                {{ $status }}
                            </td>
                            <td style="padding: 1rem; color: #6b7280; font-size: 0.75rem;">{{ $log['ip'] ?: '-' }}</td>
                            <td style="padding: 1rem;">{{ $log['user_email'] ?: 'Guest' }}</td>
                            <td style="padding: 1rem; text-align: right; font-weight: 500; font-family: monospace;">
                                {{ $log['duration_ms'] }} ms
                            </td>
                            <td style="padding: 1rem; text-align: right; color: #9ca3af; font-size: 0.75rem;">
                                {{ $log['created_at'] ? \Carbon\Carbon::parse($log['created_at'])->timezone(config('app.timezone'))->format('H:i:s d M Y') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 2rem; text-align: center; color: #9ca3af;">Belum ada aktivitas API yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
@endsection
