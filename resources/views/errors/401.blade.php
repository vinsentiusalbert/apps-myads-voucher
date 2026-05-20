<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Unauthorized - GOTO MyAds</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('assets/goto-icon.svg') }}">
        <link rel="apple-touch-icon" href="{{ asset('assets/goto-icon.svg') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:500,700|plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/auth-portal.css') }}">
        <link rel="stylesheet" href="{{ asset('css/error-401.css') }}">
    </head>
    <body class="error-body">
        <main class="error-container">
            <div class="error-content">
                <!-- Animated Background Elements -->
                <div class="error-bg-element error-bg-element--1"></div>
                <div class="error-bg-element error-bg-element--2"></div>
                <div class="error-bg-element error-bg-element--3"></div>

                <!-- Main Error Card -->
                <div class="error-card">
                    <!-- Header Section -->
                    <div class="error-header">
                        <div class="error-logo-wrapper">
                            <img src="{{ asset('assets/logo.png') }}" alt="MyAds Logo" class="error-logo">
                        </div>
                        <p class="error-subtitle">GOTO Ads Portal</p>
                    </div>

                    <!-- Error Code & Status -->
                    <div class="error-status-section">
                        <div class="error-code-container">
                            <div class="error-code-wrapper">
                                <span class="error-code">4</span>
                                <div class="error-code-center">
                                    <div class="lock-icon">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor"/>
                                        </svg>
                                    </div>
                                </div>
                                <span class="error-code">1</span>
                            </div>
                        </div>
                        <h1 class="error-title">Akses Ditolak</h1>
                        <p class="error-message">
                            Maaf, Anda tidak memiliki akses ke halaman ini.
                            <span class="error-message-hint">Silakan login atau hubungi administrator.</span>
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="error-actions">
                        @if(auth()->check())
                            <a href="{{ route('dashboard') }}" class="error-btn error-btn--primary">
                                <span>Kembali ke Dashboard</span>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="error-btn error-btn--primary">
                                <span>Login Sekarang</span>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </a>
                        @endif
                        
                        <button class="error-btn error-btn--secondary" onclick="history.back()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 12H5M12 19l-7-7 7-7"/>
                            </svg>
                            <span>Kembali</span>
                        </button>
                    </div>

                    <!-- Decorative Element -->
                    <div class="error-decoration">
                        <div class="deco-dot deco-dot--1"></div>
                        <div class="deco-dot deco-dot--2"></div>
                        <div class="deco-dot deco-dot--3"></div>
                    </div>
                </div>

                <!-- Bottom Info -->
                <div class="error-footer">
                    <p>© PT GOTO Digital Nusantara, 2026</p>
                </div>
            </div>
        </main>
    </body>
</html>
