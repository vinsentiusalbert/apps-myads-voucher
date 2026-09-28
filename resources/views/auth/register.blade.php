<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Daftar Akun Merchant - ShopAds</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('assets/logo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('assets/logo.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:500,700|plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/auth-portal.css') }}">
    </head>
    <body>
        <main class="auth-simple-shell">
            <section class="auth-simple-card">
                <a href="{{ route('login') }}" class="back-link">&larr; Kembali ke login</a>

                <div class="simple-header">
                    <div class="brand-mark" style="margin-bottom: 1rem;">
                        <img src="{{ asset('assets/logo.png') }}" alt="Shopee" style="height: 36px;">
                    </div>
                    <h1 class="simple-title">Daftar Akun Merchant</h1>
                    <p class="simple-copy">
                        Lengkapi data berikut untuk mendaftarkan akun Shopee Merchant Anda di ShopAds.
                    </p>
                </div>

                @if (session('status'))
                    <div class="status-box status-box--success">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register.store') }}" class="login-form">
                    @csrf
                    <input type="hidden" name="partner_id" value="{{ old('partner_id', $partnerId ?? 'shopee') }}">

                    <div class="field-group">
                        <label for="merchant_id" class="field-label">Merchant ID</label>
                        <input id="merchant_id" name="merchant_id" type="text" value="{{ old('merchant_id', $merchantId ?? '') }}" placeholder="Bebas diisi, contoh: BJG78783" required autofocus class="text-input">
                        <p class="field-hint" style="font-size: 0.8rem; color: #64748b; margin-top: 0.25rem;">Bebas diisi (ID unik toko/merchant Anda).</p>
                        @error('merchant_id')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label for="name" class="field-label">Nama Toko / Nama Lengkap</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Contoh: Toko Berkah Jaya" required class="text-input">
                        @error('name')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label for="email" class="field-label">Alamat Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="email@toko.com" required class="text-input">
                        @error('email')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label for="password" class="field-label">Password</label>
                        <input id="password" name="password" type="password" placeholder="Minimal 6 karakter" required class="text-input">
                        @error('password')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label for="password_confirmation" class="field-label">Konfirmasi Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Ulangi password" required class="text-input">
                    </div>

                    <button type="submit" class="submit-btn">Daftar Akun Merchant</button>

                    <div class="register-prompt" style="margin-top: 1.25rem; text-align: center; font-size: 0.875rem; color: #64748b;">
                        Sudah memiliki akun? <a href="{{ route('login') }}" style="color: #ee4d2d; font-weight: 600; text-decoration: none;">Masuk di sini</a>
                    </div>
                </form>
            </section>
        </main>
    </body>
</html>
