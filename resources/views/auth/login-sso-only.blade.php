<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login - ShopAds</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('assets/shopads-icon.svg') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/shopads-touch-icon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:500,700|plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/auth-portal.css') }}?v={{ filemtime(public_path('css/auth-portal.css')) }}">
        <style>
            .sso-container {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            .sso-card {
                background: rgba(255, 255, 255, 0.85);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(225, 0, 12, 0.2);
                border-radius: 40px;
                padding: 60px 40px;
                max-width: 500px;
                box-shadow: 0 8px 32px rgba(225, 0, 12, 0.15);
                text-align: center;
            }

            .sso-logo {
                width: 100px;
                height: auto;
                margin: 0 auto 30px;
                display: block;
                animation: float 3s ease-in-out infinite;
            }

            .sso-title {
                font-family: 'Space Grotesk', sans-serif;
                font-size: 28px;
                font-weight: 700;
                margin: 0 0 20px;
                color: var(--text);
            }

            .sso-subtitle {
                color: var(--muted);
                font-size: 16px;
                line-height: 1.6;
                margin: 0 0 40px;
            }

            .sso-info {
                background: rgba(225, 0, 12, 0.05);
                border: 1px solid rgba(225, 0, 12, 0.2);
                border-radius: 16px;
                padding: 20px;
                margin: 30px 0;
            }

            .sso-info p {
                margin: 0;
                color: var(--brand-primary);
                font-size: 14px;
                font-weight: 500;
            }

            .sso-footer {
                margin-top: 40px;
                padding-top: 30px;
                border-top: 1px solid rgba(0, 0, 0, 0.1);
                font-size: 13px;
                color: var(--muted);
            }

            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }
        </style>
    </head>
    <body>
        <div class="sso-container">
            <div class="sso-card">
                <img src="{{ asset('assets/shopads-icon.svg') }}" alt="ShopAds" class="sso-logo">
                
                <h1 class="sso-title">ShopAds Portal</h1>
                
                <p class="sso-subtitle">
                    Silakan login melalui SSO untuk mengakses portal
                </p>

                <div class="sso-info">
                    <p>🔐 Portal ini hanya dapat diakses melalui Single Sign-On (SSO)</p>
                </div>

                <p class="sso-subtitle">
                    Hubungi administrator untuk mendapatkan akses SSO
                </p>

                <div class="sso-footer">
                    <p>&copy; ShopAds, {{ date('Y') }}</p>
                </div>
            </div>
        </div>
    </body>
</html>
