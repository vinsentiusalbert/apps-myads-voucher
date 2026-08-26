<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Error {{ $status }} - GOTO MyAds</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('assets/goto-icon.svg') }}">
        <link rel="apple-touch-icon" href="{{ asset('assets/goto-icon.svg') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:500,700|plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/auth-portal.css') }}">
        <link rel="stylesheet" href="{{ asset('css/error-401.css') }}">
        <style>
            .error-btn-container {
                margin-top: 32px;
                display: flex;
                justify-content: center;
            }
            .error-home-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 12px 24px;
                background: linear-gradient(135deg, var(--brand-teal) 0%, var(--brand-blue) 100%);
                color: #ffffff;
                text-decoration: none;
                border-radius: 12px;
                font-weight: 600;
                font-size: 15px;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(0, 166, 153, 0.3);
            }
            .error-home-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(0, 166, 153, 0.4);
                color: #ffffff;
            }
            .error-message-detail {
                margin-top: 12px;
                padding: 12px 16px;
                background: rgba(225, 29, 72, 0.05);
                border: 1px solid rgba(225, 29, 72, 0.15);
                border-radius: 8px;
                font-family: monospace;
                font-size: 13px;
                color: var(--danger);
                word-break: break-all;
                text-align: left;
            }
        </style>
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
                                @foreach(str_split((string)$status) as $digit)
                                    <span class="error-code">{{ $digit }}</span>
                                @endforeach
                            </div>
                        </div>
                        <h1 class="error-title">{{ $error }}</h1>
                        <p class="error-message">
                            Terjadi kendala saat memproses permintaan Anda.
                        </p>
                        @if(!empty($message))
                            <div class="error-message-detail">
                                {{ $message }}
                            </div>
                        @endif
                    </div>

                    <!-- Action Button -->
                    <div class="error-btn-container">
                        <a href="{{ route('login') }}" class="error-home-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                            Kembali ke Login
                        </a>
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
