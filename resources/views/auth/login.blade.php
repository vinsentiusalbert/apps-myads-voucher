<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Masuk - ShopAds</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('assets/logo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('assets/logo.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:500,700|plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/auth-portal.css') }}">
    </head>
    <body>
        <main class="auth-shell">
            <section class="auth-frame">
                <div class="auth-hero">
                    <div class="auth-hero-inner">
                        <div>
                            <div class="brand-lockup">
                                <div class="brand-mark">
                                    <img src="{{ asset('assets/logo.png') }}" alt="Shopee" class="brand-logo">
                                </div>
                                <div>
                                    <p class="brand-subtitle">ShopAds Portal</p>
                                </div>
                            </div>

                            <div class="hero-copy">
                                <p class="eyebrow">Selamat Datang</p>
                                <h1 class="auth-title">Tingkatkan penjualan toko Anda dengan periklanan tertarget ShopAds.</h1>
                            </div>
                        </div>

                        <div class="hero-footer">
                            <p>Senang berjumpa kembali. Masuk ke akun Anda untuk mulai mengelola iklan.</p>
                            <span>&copy; ShopAds, {{ date('Y') }}</span>
                        </div>
                    </div>
                </div>

                <div class="auth-panel">
                    <div class="panel-card">
                        <div class="panel-topbar">
                            <span class="panel-register">Belum punya akun?</span>
                            <a href="{{ route('register') }}" class="panel-link">Daftar sekarang</a>
                        </div>
                        <h2 class="panel-title">Masuk akun ShopAds</h2>
                        <p class="panel-copy">Masukkan Merchant ID dan password Anda untuk melanjutkan.</p>

                        @if (session('status'))
                            <div class="status-box status-box--success">{{ session('status') }}</div>
                        @endif

                        <form method="POST" action="{{ route('login.store') }}" class="login-form">
                            @csrf

                            <div class="field-group">
                                <label for="merchant_id" class="field-label">Merchant ID</label>
                                <input id="merchant_id" name="merchant_id" type="text" value="{{ old('merchant_id') }}" placeholder="Contoh: SHOP12345" required autofocus class="text-input">
                                @error('merchant_id')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field-group">
                                <div class="field-label-row">
                                    <label for="password" class="field-label">Password</label>
                                    <a href="{{ route('password.request') }}" class="field-link">Lupa password?</a>
                                </div>
                                <input id="password" name="password" type="password" placeholder="Masukkan password" required class="text-input">
                                @error('password')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field-actions">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="remember" value="1">
                                    <span>Ingat saya</span>
                                </label>
                            </div>

                            <button type="submit" class="submit-btn">Masuk</button>

                            <div class="register-prompt" style="margin-top: 1.25rem; text-align: center; font-size: 0.875rem; color: #64748b;">
                                Belum memiliki akun merchant? <a href="{{ route('register') }}" style="color: #ee4d2d; font-weight: 600; text-decoration: none;">Daftar di sini</a>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
