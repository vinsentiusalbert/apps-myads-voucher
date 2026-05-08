<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Reset Password Page</title>
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
                    <h1 class="simple-title">Reset password</h1>
                    <p class="simple-copy">Masukkan password baru untuk akun Anda.</p>
                </div>

                <form method="POST" action="{{ route('password.store') }}" class="login-form">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="field-group">
                        <label for="email" class="field-label">Alamat Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus class="text-input">
                        @error('email')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label for="password" class="field-label">Password Baru</label>
                        <input id="password" name="password" type="password" required class="text-input">
                        @error('password')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label for="password_confirmation" class="field-label">Konfirmasi Password Baru</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required class="text-input">
                    </div>

                    <button type="submit" class="submit-btn">Simpan password baru</button>
                </form>
            </section>
        </main>
    </body>
</html>
