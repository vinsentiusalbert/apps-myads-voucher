<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Forgot Password Page</title>
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
                    <h1 class="simple-title">Lupa password</h1>
                    <p class="simple-copy">Masukkan email akun Anda. Laravel akan mengirimkan link reset password ke email tersebut.</p>
                </div>

                @if (session('status'))
                    <div class="status-box status-box--success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="login-form">
                    @csrf

                    <div class="field-group">
                        <label for="email" class="field-label">Alamat Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="text-input">
                        @error('email')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="submit-btn">Kirim link reset password</button>
                </form>
            </section>
        </main>
    </body>
</html>
