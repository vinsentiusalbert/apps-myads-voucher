@php
    $title = $page['title'];
    $bodyClass = '';
    $mainClass = '';
    $contentClass = 'portal-content--template-library';
    $templateRows = $templateRows ?? collect();
    $templateCount = $templateCount ?? $templateRows->count();
    $approvedTemplateCount = $approvedTemplateCount ?? $templateRows->where('status', 'APPROVED')->count();
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
    $activeNav = 'wa-business';
    $activeSubnav = 'wa-campaign-template';
@endphp

@extends('layouts.portal')

@section('content')
    <section class="template-library-hero">
        <div>
            <h1 class="template-library-hero__title">Template Message</h1>
        </div>
        <a href="{{ route('campaign-template.index', ['view' => 'create']) }}" class="template-library-hero__cta">
            <span>+</span>
            <span>Create Template</span>
        </a>
    </section>

    @if (session('status'))
        <section class="template-library-card">
            <strong>{{ session('status') }}</strong>
        </section>
    @endif

    <section class="template-library-card">
        <div class="template-library-tabs">
            <button type="button" class="template-library-tab template-library-tab--active">
                <span>All</span>
                <strong>{{ $templateCount }}</strong>
            </button>
            <button type="button" class="template-library-tab">
                <span>Approved</span>
                <strong>{{ $approvedTemplateCount }}</strong>
            </button>
        </div>

        <form method="GET" action="{{ route('campaign-template.index') }}" class="template-library-filters">
            <label class="template-library-filter">
                <span>Category Template</span>
                <select name="category">
                    <option value="">All Category</option>
                    <option value="Single Banner" @selected(request('category') === 'Single Banner')>Single Banner</option>
                </select>
            </label>

            <label class="template-library-filter">
                <span>Start date</span>
                <input type="date" name="start_date" value="{{ request('start_date') }}">
            </label>

            <label class="template-library-filter">
                <span>End date</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}">
            </label>
            <button type="submit" class="template-builder-secondary">Filter</button>
        </form>

        <div class="template-library-table-wrap">
            <table class="template-library-table">
                <thead>
                    <tr>
                        <th>Template Name</th>
                        <th>Time Added</th>
                        <th>Category</th>
                        <th>Language</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($templateRows as $row)
                        <tr>
                            <td>
                                @if (!empty($row['gw_template_id']) || !empty($row['name']))
                                    <a href="{{ route('campaign-template.api-show', ['template_id' => $row['gw_template_id'] ?? '', 'name' => $row['name']]) }}">{{ $row['name'] }}</a>
                                @elseif (!empty($row['id']))
                                    <a href="{{ route('campaign-template.show', $row['id']) }}">{{ $row['name'] }}</a>
                                @else
                                    <span>{{ $row['name'] }}</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $row['created_at']->format('d M Y') }}</strong>
                                <span>{{ $row['created_at']->format('H:i') }} WIB</span>
                            </td>
                            <td>{{ $row['category'] }}</td>
                            <td>
                                <strong>{{ $row['language'] }}</strong>
                                <span>{{ \Illuminate\Support\Str::limit($row['body'] ?? '-', 80) }}</span>
                            </td>
                            <td>
                                <span class="template-library-status">{{ $row['status'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Belum ada template campaign.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
