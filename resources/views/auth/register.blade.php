<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Register Page</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('assets/goto-icon.svg') }}">
        <link rel="apple-touch-icon" href="{{ asset('assets/goto-icon.svg') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:500,700|plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/auth-portal.css') }}">
    </head>
    <body>
        <main class="auth-simple-shell">
            <section class="auth-simple-card">
                <a href="{{ route('login') }}" class="back-link">Kembali ke login</a>

                <div class="simple-header">
                    <h1 class="simple-title">Daftar akun MyAds</h1>
                    <p class="simple-copy">Buat akun baru dengan auth bawaan Laravel untuk mulai masuk ke dashboard iklan.</p>
                </div>

                <form method="POST" action="{{ route('register.store') }}" class="login-form">
                    @csrf

                    <div class="field-group">
                        <label for="merchant_id" class="field-label">Merchant ID</label>
                        <input id="merchant_id" name="merchant_id" type="text" value="{{ old('merchant_id', $merchantId) }}" readonly required class="text-input">
                        @error('merchant_id')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label for="partner_id" class="field-label">Partner ID</label>
                        <input id="partner_id" name="partner_id" type="text" value="{{ old('partner_id', $partnerId) }}" readonly required class="text-input">
                        @error('partner_id')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label for="name" class="field-label">Nama Lengkap</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus class="text-input">
                        @error('name')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label for="email" class="field-label">Alamat Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required class="text-input">
                        @error('email')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label for="password" class="field-label">Password</label>
                        <input id="password" name="password" type="password" required class="text-input">
                        @error('password')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label for="password_confirmation" class="field-label">Konfirmasi Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required class="text-input">
                    </div>

                    <button type="submit" class="submit-btn">Daftar</button>
                </form>
            </section>
        </main>
    </body>
</html>
