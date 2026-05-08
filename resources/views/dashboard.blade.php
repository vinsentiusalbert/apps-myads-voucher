@php
    $title = 'Dashboard';
    $navMenus = [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'dashboard',
            'href' => route('dashboard'),
            'dashboard' => true,
            'interactive' => true,
        ],
        [
            'key' => 'sms',
            'label' => 'SMS',
            'icon' => 'sms',
            'children' => [
                [
                    'key' => 'sms-location-based-area',
                    'label' => 'Location Based Area',
                    'icon' => 'location',
                    'href' => route('campaign.menu', ['channel' => 'sms', 'menu' => 'location-based-area']),
                    'interactive' => true,
                ],
                [
                    'key' => 'sms-targeted',
                    'label' => 'Targeted',
                    'icon' => 'targeted',
                    'href' => route('campaign.menu', ['channel' => 'sms', 'menu' => 'targeted']),
                    'interactive' => true,
                ],
            ],
        ],
        [
            'key' => 'wa-business',
            'label' => 'WA Business',
            'icon' => 'wa',
            'children' => [
                [
                    'key' => 'wa-location-based-area',
                    'label' => 'Location Based Area',
                    'icon' => 'location',
                    'href' => route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'location-based-area']),
                    'interactive' => true,
                ],
                [
                    'key' => 'wa-campaign-template',
                    'label' => 'Campaign Template',
                    'icon' => 'template',
                    'href' => route('campaign-template.index'),
                    'interactive' => true,
                ],
                [
                    'key' => 'wa-targeted',
                    'label' => 'Targeted',
                    'icon' => 'targeted',
                    'href' => route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'targeted']),
                    'interactive' => true,
                ],
            ],
        ],
    ];
    $activeNav = 'dashboard';
@endphp

@extends('layouts.portal')

@section('content')
    @php
        $expiryLabel = '-';

        if (!empty($balanceExpiry) && is_string($balanceExpiry)) {
            try {
                $expiryLabel = 'Exp. ' . \Carbon\Carbon::parse($balanceExpiry)->translatedFormat('d M Y');
            } catch (Throwable) {
                $expiryLabel = 'Exp. ' . $balanceExpiry;
            }
        }

        $campaignTotalLabel = $campaignTotal === null ? '-' : (string) $campaignTotal;
        $campaignSmsLabel = $campaignSmsTotal === null ? '-' : (string) $campaignSmsTotal;
        $campaignWaLabel = $campaignWaTotal === null ? '-' : (string) $campaignWaTotal;
        $gwStatusLabel = session()->has('myads.gw_token') ? 'OK' : '-';
    @endphp

    <section class="portal-hero">
        <div class="portal-welcome">
            <p class="portal-welcome__lead">Selamat Datang,</p>
            <h1 class="portal-welcome__name">{{ auth()->user()->name }}</h1>
        </div>

        <article class="portal-balance">
            <div class="portal-balance__top">
                <div>
                    <p class="portal-balance__label">Saldo Utama</p>
                    <p class="portal-balance__value">{{ $balanceFormatted ?? '-' }}</p>
                </div>
            </div>

            <div class="portal-balance__bottom">
                <span>{{ $expiryLabel }}</span>
                <span>{{ $expiryLabel }}</span>
                <a href="#">Lihat Riwayat Saldo</a>
            </div>
        </article>
    </section>

    <section class="portal-card portal-card--insight">
        <div class="portal-card__header">
            <h2>Ringkasan Dashboard</h2>
            <a href="#">Refresh</a>
        </div>

        <div class="portal-stats">
            <div class="portal-stats__item">
                <p class="portal-stats__value">{{ $campaignTotalLabel }}</p>
                <p class="portal-stats__label">Total campaign</p>
            </div>
            <div class="portal-stats__item">
                <p class="portal-stats__value">{{ $campaignSmsLabel }}</p>
                <p class="portal-stats__label">Campaign channel SMS</p>
            </div>
            <div class="portal-stats__item">
                <p class="portal-stats__value">{{ $campaignWaLabel }}</p>
                <p class="portal-stats__label">Campaign channel WA Business</p>
            </div>
            <div class="portal-stats__item">
                <p class="portal-stats__value">{{ $gwStatusLabel }}</p>
                <p class="portal-stats__label">Token gateway di session</p>
            </div>
        </div>
    </section>

    <section class="portal-section">
        <h2 class="portal-section__title">Menu Campaign</h2>

        <div class="portal-service-grid">
            <article class="portal-service-card">
                <div class="portal-service-card__title-row">
                    <h3>SMS</h3>
                    <span>{{ $campaignSmsLabel }} campaign</span>
                </div>
                <div class="portal-service-list">
                    <span class="portal-service-list__item">Lihat campaign SMS di menu navigasi.</span>
                    <span class="portal-service-list__item">Gunakan token gateway untuk request list / create.</span>
                </div>
            </article>

            <article class="portal-service-card">
                <div class="portal-service-card__title-row">
                    <h3>WA Business</h3>
                    <span>{{ $campaignWaLabel }} campaign</span>
                </div>
                <div class="portal-service-list">
                    <span class="portal-service-list__item">Lihat campaign WA Business di menu navigasi.</span>
                    <span class="portal-service-list__item">Campaign Template tetap dari database UI.</span>
                    <span class="portal-service-list__item">Data campaign list/stop/add dari API GW.</span>
                </div>
            </article>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (() => {
            const dashboardLink = document.querySelector('[data-dashboard-link]');
            const subnavLinks = document.querySelectorAll('[data-subnav-link]');

            const clearNavSelection = () => {
                document.querySelectorAll('.portal-nav__item').forEach((item) => {
                    item.classList.remove('portal-nav__item--active', 'portal-nav__item--selected');
                });

                subnavLinks.forEach((link) => {
                    link.classList.remove('portal-subnav__item--active');
                });
            };

            dashboardLink?.addEventListener('click', () => {
                clearNavSelection();
                dashboardLink.closest('.portal-nav__item')?.classList.add('portal-nav__item--active');
            });

            subnavLinks.forEach((link) => {
                link.addEventListener('click', (event) => {
                    const isPlaceholder = link.getAttribute('href') === '#';

                    if (isPlaceholder) {
                        event.preventDefault();
                    }

                    const parentGroup = link.closest('[data-nav-group]');
                    const parentToggle = parentGroup?.querySelector('[data-nav-toggle]');

                    clearNavSelection();
                    link.classList.add('portal-subnav__item--active');
                    parentGroup?.classList.add('portal-nav__item--selected', 'portal-nav__item--open');
                    parentToggle?.setAttribute('aria-expanded', 'true');
                });
            });
        })();
    </script>
@endpush
