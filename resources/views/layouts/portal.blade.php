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
