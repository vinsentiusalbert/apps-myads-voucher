<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Halaman Tidak Ditemukan - ShopAds</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('assets/shopads-icon.svg') }}">
        <link rel="stylesheet" href="{{ asset('css/auth-portal.css') }}?v={{ filemtime(public_path('css/auth-portal.css')) }}">
    </head>
    <body>
        <main class="auth-simple-shell">
            <section class="auth-simple-card">
                <div class="portal-brand">
                    <img src="{{ asset('assets/shopads-icon.svg') }}" alt="" class="portal-brand__logo">
                    <p class="portal-brand__text">ShopAds</p>
                </div>
                <h1 class="simple-title">Halaman tidak ditemukan</h1>
                <p class="simple-copy">Data yang Anda cari tidak tersedia atau sudah tidak dapat diakses.</p>
                <a class="submit-btn" href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
            </section>
        </main>
    </body>
</html>
