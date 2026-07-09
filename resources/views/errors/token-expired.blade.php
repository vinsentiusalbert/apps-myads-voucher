<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sesi Berakhir - GOTO MyAds</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('assets/goto-icon.svg') }}">
        <link rel="apple-touch-icon" href="{{ asset('assets/goto-icon.svg') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:500,700|plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/auth-portal.css') }}">
        <link rel="stylesheet" href="{{ asset('css/error-token-expired.css') }}">
    </head>
    <body class="token-expired-body">
        <main class="token-expired-shell">
            <div class="token-expired-orb token-expired-orb--1"></div>
            <div class="token-expired-orb token-expired-orb--2"></div>
            <div class="token-expired-orb token-expired-orb--3"></div>

            <section class="token-expired-card">
                <div class="token-expired-brand">
                    <img src="{{ asset('assets/logo.png') }}" alt="MyAds Logo" class="token-expired-logo">
                    <span class="token-expired-kicker">GOTO Ads Portal</span>
                </div>

                <div class="token-expired-badge">Session Notice</div>

                <div class="token-expired-illustration" aria-hidden="true">
                    <div class="token-expired-ring"></div>
                    <div class="token-expired-clock">
                        <span class="token-expired-clock-hand token-expired-clock-hand--short"></span>
                        <span class="token-expired-clock-hand token-expired-clock-hand--long"></span>
                    </div>
                </div>

                <h1 class="token-expired-title">Sesi Anda Sudah Berakhir</h1>
                <p class="token-expired-text">
                    Token akses ke layanan MyAds sudah tidak aktif, jadi proses sebelumnya tidak bisa dilanjutkan.
                </p>
                <p class="token-expired-text token-expired-text--muted">
                    Silakan login ulang melalui Gofood Merchant agar token baru bisa dibuat, lalu buka kembali halaman campaign Anda.
                </p>

                @if (! empty($message))
                    <div class="token-expired-alert" role="status">
                        <span class="token-expired-alert-label">Detail sistem</span>
                        <strong>{{ $message }}</strong>
                    </div>
                @endif

                <div class="token-expired-actions">
                    <a href="{{ route('login') }}" class="token-expired-btn token-expired-btn--primary">
                        Kembali ke Portal
                    </a>
                    <button type="button" class="token-expired-btn token-expired-btn--ghost" onclick="window.history.back()">
                        Kembali ke Halaman Sebelumnya
                    </button>
                </div>

                <p class="token-expired-help">
                    Jika halaman ini terus muncul setelah login ulang, hubungi tim administrator.
                </p>
            </section>
        </main>
    </body>
</html>
