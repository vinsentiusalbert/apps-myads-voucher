<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Goto x MyAds | Login Page</title>
        <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
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
                                    <img src="{{ asset('assets/logo.png') }}" alt="MyAds Logo" class="brand-logo">
                                </div>
                                <div>
                                    {{-- <p class="brand-name">myAds</p> --}}
                                    <p class="brand-subtitle">GOTO Ads Portal</p>
                                </div>
                            </div>

                            <div class="hero-copy">
                                <p class="eyebrow">Selamat Datang</p>
                                {{-- <h1 class="auth-title">Kelola campaign digital Anda dalam satu workspace yang rapi dan fokus.</h1> --}}
                                {{-- <p class="auth-copy">
                                    Senang berjumpa kembali. Ikuti langkah berikut untuk mulai beriklan dengan myAds.
                                </p> --}}
                            </div>
                        </div>

                        <div class="hero-footer">
                            <p>Senang berjumpa kembali. Ikuti langkah berikut untuk mulai beriklan dengan myAds.</p>
                            <span>&copy;PT GOTO Digital Nusantara, 2026</span>
                        </div>
                    </div>
                        </div>

                <div class="auth-panel">
                    <div class="panel-card">
                        <div class="panel-topbar">
                            <p class="panel-register">Belum memiliki akun? <a href="{{ route('register') }}" class="field-link">Daftar</a></p>
                        </div>
                        <h2 class="panel-title">Masuk ke MyAds</h2>
                        <p class="panel-copy">Isi form di bawah untuk masuk ke akun Anda</p>

                        @if (session('status'))
                            <div class="status-box status-box--success">{{ session('status') }}</div>
                        @endif

                    

                        <form method="POST" action="{{ route('login.store') }}" class="login-form">
                            @csrf

                            <div class="field-group">
                                <label for="email" class="field-label">Alamat Email / Nomor Telepon</label>
                                <p class="field-helper">Masukkan alamat email / nomor telepon yang sudah terdaftar</p>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus placeholder="nama@company.com" class="text-input">
                                @error('email')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field-group">
                                <div class="field-label-row">
                                    <label for="password" class="field-label">Password</label>
                                </div>
                                <p class="field-helper">Masukkan password Anda</p>
                                <input id="password" name="password" type="password" required placeholder="Masukkan password" class="text-input">
                                @error('password')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field-actions">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                                    Ingat saya
                                </label>
                                <a href="{{ route('password.request') }}" class="field-link">Lupa password</a>
                            </div>

                            <button type="submit" class="submit-btn">Masuk</button>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
