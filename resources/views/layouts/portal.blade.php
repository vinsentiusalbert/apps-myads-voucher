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
        <link rel="stylesheet" href="{{ asset('css/auth-portal.css') }}">
        @stack('styles')
    </head>
    <body class="{{ $bodyClass ?? '' }}">
        <main class="portal-shell">
            <button type="button" class="portal-overlay" data-menu-close aria-label="Tutup menu"></button>

            @include('partials.portal-sidebar', [
                'navMenus' => $navMenus ?? [],
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
        @stack('scripts')
    </body>
</html>
