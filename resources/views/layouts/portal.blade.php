<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'GOTO Ads Portal' }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('assets/goto-icon.svg') }}">
        <link rel="apple-touch-icon" href="{{ asset('assets/goto-icon.svg') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:500,700|plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/auth-portal.css') }}?v={{ filemtime(public_path('css/auth-portal.css')) }}">
        @stack('styles')
    </head>
    <body class="{{ $bodyClass ?? '' }}">
        <div class="portal-page-loader" id="portalPageLoader" role="status" aria-live="polite">
            <div class="portal-page-loader__card">
                <span class="portal-page-loader__spinner" aria-hidden="true"></span>
                <strong>Memuat halaman...</strong>
            </div>
        </div>

        <main class="portal-shell">
            <button type="button" class="portal-overlay" data-menu-close aria-label="Tutup menu"></button>

            @php
                $processedNavMenus = $navMenus ?? [];
                if (session('myads.user.role') === 'admin') {
                    $hasAdmin = false;
                    foreach ($processedNavMenus as $menu) {
                        if ($menu['key'] === 'admin') {
                            $hasAdmin = true;
                            break;
                        }
                    }
                    if (!$hasAdmin) {
                        $processedNavMenus[] = [
                            'key' => 'admin',
                            'label' => 'Admin Panel',
                            'icon' => 'dashboard',
                            'href' => route('admin.dashboard'),
                            'interactive' => true,
                        ];
                    }
                }
            @endphp

            @include('partials.portal-sidebar', [
                'navMenus' => $processedNavMenus,
                'activeNav' => $activeNav ?? null,
                'activeSubnav' => $activeSubnav ?? null,
                'createButtonLabel' => $createButtonLabel ?? 'Buat Iklan',
                'createButtonHref' => $createButtonHref ?? null,
            ])

            <section class="portal-main {{ $mainClass ?? '' }}">
                @include('partials.portal-topbar')

                <div class="portal-content {{ $contentClass ?? '' }}">
                    @yield('content')
                </div>
            </section>
        </main>

        @yield('after_shell')

        @if (session('status') || session('success') || $errors->any() || session('error'))
            @php
                $alertType = ($errors->any() || session('error')) ? 'error' : 'success';
                $alertMessage = ($errors->any()) ? $errors->first() : (session('error') ?: (session('status') ?: session('success')));
            @endphp
            <div class="custom-portal-alert custom-portal-alert--{{ $alertType }}" id="customPortalAlert">
                <div class="custom-portal-alert__icon">
                    @if ($alertType === 'success')
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    @else
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    @endif
                </div>
                <div class="custom-portal-alert__content">
                    <p class="custom-portal-alert__title">{{ $alertType === 'success' ? 'Berhasil' : 'Gagal' }}</p>
                    <p class="custom-portal-alert__message">{{ $alertMessage }}</p>
                </div>
                <button type="button" class="custom-portal-alert__close" onclick="document.getElementById('customPortalAlert').remove()">✕</button>
            </div>
            <style>
                .custom-portal-alert {
                    position: fixed;
                    top: 24px;
                    right: 24px;
                    z-index: 9999;
                    display: flex;
                    align-items: center;
                    gap: 16px;
                    padding: 16px 20px;
                    border-radius: 12px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    border: 1px solid rgba(0,0,0,0.05);
                    max-width: 380px;
                    animation: slideInAlert 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                }
                .custom-portal-alert--success {
                    border-left: 4px solid #10b981;
                }
                .custom-portal-alert--error {
                    border-left: 4px solid #f43f5e;
                }
                .custom-portal-alert__icon {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    width: 32px;
                    height: 32px;
                    border-radius: 50%;
                }
                .custom-portal-alert--success .custom-portal-alert__icon {
                    background: rgba(16, 185, 129, 0.1);
                    color: #10b981;
                }
                .custom-portal-alert--error .custom-portal-alert__icon {
                    background: rgba(244, 63, 94, 0.1);
                    color: #f43f5e;
                }
                .custom-portal-alert__content {
                    flex: 1;
                }
                .custom-portal-alert__title {
                    margin: 0;
                    font-weight: 700;
                    font-size: 14px;
                    color: #1f2937;
                }
                .custom-portal-alert__message {
                    margin: 4px 0 0 0;
                    font-size: 13px;
                    color: #4b5563;
                    line-height: 1.4;
                }
                .custom-portal-alert__close {
                    background: none;
                    border: none;
                    font-size: 14px;
                    color: #9ca3af;
                    cursor: pointer;
                    padding: 4px;
                    transition: color 0.2s;
                }
                .custom-portal-alert__close:hover {
                    color: #4b5563;
                }
                @keyframes slideInAlert {
                    from {
                        transform: translateX(120%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
            </style>
            <script>
                setTimeout(() => {
                    const alertEl = document.getElementById('customPortalAlert');
                    if (alertEl) {
                        alertEl.style.transition = 'all 0.4s ease';
                        alertEl.style.opacity = '0';
                        alertEl.style.transform = 'translateX(120%)';
                        setTimeout(() => alertEl.remove(), 400);
                    }
                }, 7000);
            </script>
        @endif

        @include('partials.portal-campaign-category-modal')
        @include('partials.portal-menu-script')
        <script>
            (() => {
                const loader = document.getElementById('portalPageLoader');
                if (!loader) {
                    return;
                }

                const hideLoader = () => {
                    loader.classList.add('portal-page-loader--hidden');
                    window.setTimeout(() => loader.remove(), 250);
                };

                if (document.readyState === 'complete') {
                    hideLoader();
                } else {
                    window.addEventListener('load', hideLoader, { once: true });
                    window.setTimeout(hideLoader, 3500);
                }
            })();
        </script>
        @stack('scripts')
        @if(config('app.debug'))
            @include('partials.api-debug-console')
        @endif
    </body>
</html>
