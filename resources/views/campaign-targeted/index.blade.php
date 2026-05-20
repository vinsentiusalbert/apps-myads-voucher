@php
    $title = $page['title'];
    $bodyClass = '';
    $mainClass = '';
    $contentClass = 'portal-content--template-library';
    $campaignRows = collect($campaignRows ?? []);
    $navMenus = [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'dashboard',
            'href' => route('dashboard'),
        ],
        [
            'key' => 'sms',
            'label' => 'Daftar SMS',
            'icon' => 'sms',
            'children' => [
                [
                    'key' => 'sms-location-based-area',
                    'label' => 'Location Based Area',
                    'icon' => 'location',
                    'href' => route('campaign.menu', ['channel' => 'sms', 'menu' => 'location-based-area']),
                ],
                [
                    'key' => 'sms-targeted',
                    'label' => 'Targeted',
                    'icon' => 'targeted',
                    'href' => route('campaign.menu', ['channel' => 'sms', 'menu' => 'targeted']),
                ],
            ],
        ],
        [
            'key' => 'wa-business',
            'label' => 'Daftar WA Business',
            'icon' => 'wa',
            'children' => [
                [
                    'key' => 'wa-campaign-template',
                    'label' => 'Campaign Template',
                    'icon' => 'template',
                    'href' => route('campaign-template.index'),
                ],
                [
                    'key' => 'wa-location-based-area',
                    'label' => 'Location Based Area',
                    'icon' => 'location',
                    'href' => route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'location-based-area']),
                ],
                [
                    'key' => 'wa-targeted',
                    'label' => 'Targeted',
                    'icon' => 'targeted',
                    'href' => route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'targeted']),
                ],
            ],
        ],
    ];
    $activeNav = $channel;
    $activeSubnav = $channel === 'sms' ? 'sms-targeted' : 'wa-targeted';
@endphp

@extends('layouts.portal')

@section('content')
    <section class="template-library-hero">
        <div>
            <h1 class="template-library-hero__title">{{ $page['title'] }}</h1>
        </div>
        <a href="{{ route('campaign.menu', ['channel' => $channel, 'menu' => $menu, 'view' => 'create']) }}" class="template-library-hero__cta">
            <span>+</span>
            <span>Buat Iklan</span>
        </a>
    </section>

    <section class="template-library-card">
        <div class="template-library-tabs">
            <button type="button" class="template-library-tab template-library-tab--active">
                <span>All</span>
                <strong>{{ $campaignRows->count() }}</strong>
            </button>
        </div>

        <div class="template-library-table-wrap">
            <table class="template-library-table">
                <thead>
                    <tr>
                        <th>ID Iklan</th>
                        <th>Tgl Tayang</th>
                        <th>Judul Pesan Iklan</th>
                        <th>Operator Seluler</th>
                        <th>Kategori Iklan</th>
                        <th>Tipe Kanal</th>
                        <th>Detil Status</th>
                        <th>Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($campaignRows as $row)
                        @php
                            $reportUrl = route('campaign.menu', ['channel' => $channel, 'menu' => $menu, 'view' => 'show', 'id' => $row['id']]);
                        @endphp
                        <tr data-report-url="{{ $reportUrl }}" style="cursor: pointer;">
                            <td>{{ $row['id'] }}</td>
                            <td>{{ $row['date'] }}</td>
                            <td>
                                <a href="{{ $reportUrl }}">{{ $row['title'] }}</a>
                            </td>
                            <td>{{ $row['operator'] }}</td>
                            <td>{{ $row['category'] }}</td>
                            <td>{{ $row['channel_type'] }}</td>
                            <td>{{ $row['status_detail'] }}</td>
                            <td>{{ $row['total_price'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">Belum ada data campaign.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (() => {
            document.querySelectorAll('[data-report-url]').forEach((row) => {
                row.addEventListener('click', (event) => {
                    if (event.target.closest('a, button, input, select, textarea')) {
                        return;
                    }

                    window.location.href = row.dataset.reportUrl;
                });
            });
        })();
    </script>
@endpush
