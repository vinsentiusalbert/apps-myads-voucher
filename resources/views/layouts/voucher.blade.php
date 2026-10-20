<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title')</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('assets/shopads-icon.svg') }}">
        <link rel="stylesheet" href="{{ asset('css/auth-portal.css') }}?v={{ filemtime(public_path('css/auth-portal.css')) }}">
        <link rel="stylesheet" href="{{ asset('css/voucher.css') }}?v={{ filemtime(public_path('css/voucher.css')) }}">
        @stack('styles')
    </head>
    <body class="@yield('body-class')">
        <main class="auth-shell">
            <section class="auth-frame">
                <div class="auth-hero">
                    <div class="auth-hero-inner">
                        <div>
                            <div class="brand-lockup">
                                <div class="brand-mark">
                                    <img src="{{ asset('assets/shopads-icon.svg') }}" alt="MyAds" class="brand-logo">
                                </div>
                                <p class="brand-subtitle">Bundling MyAds</p>
                            </div>
                            <div class="hero-copy">
                                <p class="eyebrow">Satu kode, akses paket Anda</p>
                                <h1 class="auth-title">Jangkau pelanggan dengan SMS LBA.</h1>
                                <p>Pilih paket bundling untuk kebutuhan promosi bisnis Anda.</p>
                            </div>
                            <div class="voucher-packages" aria-label="Paket Bundling MyAds">
                                @foreach(config('vouchers') as $package)
                                    @continue($package['report_demo'] ?? false)
                                    <div class="voucher-package">
                                        <strong>Rp {{ number_format($package['price'], 0, ',', '.') }}</strong>
                                        <span>Report {{ $package['report_days'] }} hari</span>
                                        <span>SMS LBA {{ number_format($package['sms_quota'], 0, ',', '.') }} pesan</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="hero-footer"><span>&copy; MyAds, {{ date('Y') }}</span></div>
                    </div>
                </div>
                <div class="auth-panel">
                    <div class="panel-card">@yield('content')</div>
                </div>
            </section>
        </main>
        @yield('after_shell')
        @stack('scripts')
    </body>
</html>
