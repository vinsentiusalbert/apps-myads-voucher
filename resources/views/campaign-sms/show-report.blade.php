@php
    $title = $page['title'];
    $bodyClass = '';
    $mainClass = '';
    $contentClass = 'portal-content--campaign-report';
    $isTargeted = $menu === 'targeted';
    $listDetail = is_array($campaignRow['report_detail'] ?? null) ? $campaignRow['report_detail'] : [];
    $rawCampaign = (array) ($campaignRow['raw'] ?? []);
    $campaignReport = is_array($campaignReport ?? null) ? $campaignReport : [];
    $reportData = data_get($campaignReport, 'data.data')
        ?? data_get($campaignReport, 'data')
        ?? [];
    $reportData = is_array($reportData) ? $reportData : [];

    $decodeNestedJson = function ($value) use (&$decodeNestedJson) {
        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed !== '' && in_array($trimmed[0], ['{', '['], true)) {
                $decoded = json_decode($trimmed, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decodeNestedJson($decoded);
                }
            }

            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $decodeNestedJson($item);
            }
        }

        return $value;
    };

    $rawCampaign = $decodeNestedJson($rawCampaign);
    $reportData = $decodeNestedJson($reportData);

    $formatNumber = function ($value, $default = '-') {
        if ($value === null || $value === '') {
            return $default;
        }

        return is_numeric($value) ? number_format((float) $value, 0, ',', '.') : $value;
    };

    $formatMoney = function ($value, $default = '-') {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_string($value) && str_starts_with(trim($value), 'Rp')) {
            return $value;
        }

        return is_numeric($value) ? 'Rp ' . number_format((float) $value, 0, ',', '.') : $value;
    };

    $formatDate = function ($value, $default = '-') {
        $value = trim((string) $value);
        if ($value === '') {
            return $default;
        }

        try {
            if (preg_match('/^\d{8}$/', $value)) {
                return \Illuminate\Support\Carbon::createFromFormat('Ymd', $value)->translatedFormat('d M Y');
            }

            if (is_numeric($value) && strlen($value) >= 12) {
                return \Illuminate\Support\Carbon::createFromTimestamp((int) ($value / 1000))->translatedFormat('d M Y');
            }

            return \Illuminate\Support\Carbon::parse($value)->translatedFormat('d M Y');
        } catch (Throwable) {
            return $value;
        }
    };

    $formatTime = function ($value, $default = '-') {
        $value = trim((string) $value);
        if ($value === '') {
            return $default;
        }

        if (preg_match('/^\d{4}$/', $value)) {
            return substr($value, 0, 2) . '.' . substr($value, 2, 2);
        }

        return str_replace(':', '.', $value);
    };

    $normalizeKey = function (string $key): string {
        if (str_contains($key, '.')) {
            $key = substr(strrchr($key, '.'), 1);
        }

        return strtolower((string) preg_replace('/[^a-z0-9]/i', '', $key));
    };

    $findNestedValue = function ($source, array $keys) use (&$findNestedValue, $normalizeKey) {
        if (! is_array($source)) {
            return null;
        }

        $targets = array_map($normalizeKey, $keys);
        foreach ($source as $key => $value) {
            if (in_array($normalizeKey((string) $key), $targets, true) && $value !== null && $value !== '') {
                return $value;
            }

            if (is_array($value)) {
                $nested = $findNestedValue($value, $keys);
                if ($nested !== null && $nested !== '') {
                    return $nested;
                }
            }
        }

        return null;
    };

    $firstValue = function (array $keys, $default = null) use ($listDetail, $reportData, $rawCampaign, $campaignRow, $findNestedValue) {
        foreach ($keys as $key) {
            foreach ([$reportData, $campaignRow, $listDetail, $rawCampaign] as $source) {
                $value = data_get($source, $key);
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }
        }

        foreach ([$reportData, $campaignRow, $listDetail, $rawCampaign] as $source) {
            $value = $findNestedValue($source, $keys);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $default;
    };

    $firstAssoc = function (array $keys) use ($firstValue): array {
        $value = $firstValue($keys, []);
        if (! is_array($value)) {
            return [];
        }

        if (array_is_list($value)) {
            return is_array($value[0] ?? null) ? $value[0] : [];
        }

        return $value;
    };

    $listValue = function (array $keys) use ($firstValue): array {
        $value = $firstValue($keys, []);
        if (! is_array($value)) {
            return [];
        }

        return array_is_list($value) ? $value : [$value];
    };

    preg_match('/Sukses:\s*([\d\.,]+)/i', $campaignRow['status_detail'] ?? '', $successMatch);
    preg_match('/Gagal:\s*([\d\.,]+)/i', $campaignRow['status_detail'] ?? '', $failedMatch);

    $campaignId = $firstValue(['campaignId', 'id'], $campaignRow['id'] ?? '-');
    $campaignTitle = $firstValue(['campaignName', 'title', 'name'], $campaignRow['title'] ?? 'Belum diisi');
    $campaignDate = $campaignRow['date']
        ?? $formatDate($firstValue(['deliveryDate', 'deliveryStartDate', 'startDate', 'createdAt'], ''), '-');
    $categoryLabel = $isTargeted ? 'Targeted' : 'Location Based Advertising';
    $channelLabel = $firstValue(['inventoryType', 'channel', 'channel_type'], $campaignRow['channel_type'] ?? 'SMS');
    $senderName = $firstValue(['sender_name', 'senderName', 'sender', 'from'], '-');
    $messageText = $firstValue(['message', 'content.message', 'contents.message', 'messageText', 'smsMessage', 'bodyMessage'], '-');
    $totalRecipient = $formatNumber($firstValue(['recipient_total', 'recepientTotal', 'recipientTotal', 'totalRecipient', 'totalRecepient', 'totalMessage'], null), '-');

    $successCount = $formatNumber($firstValue(['success', 'successCount', 'totalSuccess', 'delivered', 'deliveredCount', 'sent', 'sentCount'], $successMatch[1] ?? null), '0');
    $failedCount = $formatNumber($firstValue(['failed', 'failedCount', 'totalFailed', 'undelivered', 'undeliveredCount', 'fail', 'failCount'], $failedMatch[1] ?? null), '0');
    $totalClick = $formatNumber($firstValue(['totalClick', 'clickTotal', 'clicks', 'clickCount'], 0), '0');
    $clickRateRaw = $firstValue(['clickRate', 'ctr', 'click_rate'], null);
    $clickRate = is_numeric($clickRateRaw) ? number_format((float) $clickRateRaw, 2, ',', '.') . '%' : ($clickRateRaw ?: '0%');
    $shortUrl = $firstValue(['short_url', 'shortUrl', 'url.shortUrl', 'content.shortUrl', 'contents.shortUrl'], '-');

    $location = $firstAssoc(['location', 'content.locations', 'contents.locations', 'locations']);
    $locationName = data_get($location, 'locationNameUser')
        ?? data_get($location, 'locationNameMap')
        ?? data_get($location, 'locationName')
        ?? data_get($location, 'name')
        ?? '-';
    $locationAddress = data_get($location, 'address') ?? '-';
    $radius = $formatNumber(data_get($location, 'radius') ?? $firstValue(['radius'], null), '-');
    $locationType = data_get($location, 'locationType') ?? $firstValue(['locationType'], '-');
    $estimatedPotential = $formatNumber($firstValue(['estimatedRecipient', 'estimatedRecepient', 'potentialRecipient', 'potentialRecepient'], null), '-');

    $profile = $firstAssoc(['profile', 'profiles', 'targetProfile']);
    $gender = data_get($profile, 'gender') ?? data_get($profile, 'profileGender') ?? $firstValue(['profile_gender', 'gender'], 'Semua');
    $age = data_get($profile, 'age') ?? data_get($profile, 'profileAge') ?? $firstValue(['profile_age', 'age'], 'Semua');
    $religion = data_get($profile, 'religion') ?? data_get($profile, 'profileReligion') ?? $firstValue(['profile_religion', 'religion'], 'Semua');
    $device = data_get($profile, 'device') ?? data_get($profile, 'os') ?? data_get($profile, 'profileOs') ?? $firstValue(['profile_os', 'device', 'os'], 'Semua');

    $deliveries = $listValue(['deliveries', 'delivery', 'schedules', 'schedule']);
    if ($deliveries === []) {
        $deliveries = [[
            'deliveryDate' => $firstValue(['deliveryDate', 'startDate'], ''),
            'deliveryStartDate' => $firstValue(['deliveryStartDate', 'startDate'], ''),
            'deliveryEndDate' => $firstValue(['deliveryEndDate', 'endDate'], ''),
            'deliveryTime' => $firstValue(['deliveryTime'], ''),
            'deliveryStartTime' => $firstValue(['deliveryStartTime'], ''),
            'deliveryEndTime' => $firstValue(['deliveryEndTime'], ''),
            'deliveryMethod' => $firstValue(['deliveryMethod', 'method'], '-'),
        ]];
    }

    $controlNumbersRaw = $firstValue(['control_numbers', 'controlNumbers', 'controlNumber', 'testNumbers', 'testNumber'], []);
    if (is_string($controlNumbersRaw)) {
        $controlNumbers = array_filter(array_map('trim', preg_split('/[,;\n]+/', $controlNumbersRaw)));
    } elseif (is_array($controlNumbersRaw)) {
        $controlNumbers = array_values(array_filter($controlNumbersRaw, fn ($number) => trim((string) $number) !== ''));
    } else {
        $controlNumbers = [];
    }

    $unitPrice = $formatMoney($firstValue(['unit_price', 'unitPrice', 'pricePerMessage', 'price'], null), '-');
    $grandTotal = $formatMoney($firstValue(['grand_total', 'grandTotal', 'totalPrice', 'totalCost', 'bucketMain'], $campaignRow['total_price'] ?? null), '-');
    $balanceMain = $formatMoney($firstValue(['balanceMain', 'mainBalance', 'saldoUmum'], null), '-');
    $balanceMonetary = $formatMoney($firstValue(['balanceMonetary', 'monetaryBalance', 'saldoMonetary'], null), '-');
    $quota = $formatNumber($firstValue(['quota', 'quotaPackage', 'kuotaPaket'], 0), '0') . ' Pesan';

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
    $activeNav = 'sms';
    $activeSubnav = $isTargeted ? 'sms-targeted' : 'sms-location-based-area';
    $reportSections = [
        ['key' => 'message', 'icon' => 'message', 'title' => 'Konten Pesan Iklan'],
        ['key' => 'schedule', 'icon' => 'schedule', 'title' => 'Jadwal Pengiriman'],
        ['key' => 'test', 'icon' => 'profile', 'title' => 'Nomor Test Iklan'],
        ['key' => 'billing', 'icon' => 'billing', 'title' => 'Biaya Pembayaran'],
    ];
@endphp

@extends('layouts.portal')

@section('content')
    <section class="campaign-report-head">
        <div class="campaign-report-head__copy">
            <p class="campaign-report-head__breadcrumbs">Dashboard / Laporan / Detail Iklan</p>
            <p class="campaign-report-head__kicker">{{ $categoryLabel }} - SMS</p>
            <h1 class="campaign-report-head__title">{{ $campaignTitle }}</h1>
            <div class="campaign-report-head__meta">
                <span>Tanggal Penayangan Iklan: {{ $campaignDate }}</span>
                <span>ID: {{ $campaignId }}</span>
            </div>
        </div>
        <div class="campaign-report-head__actions">
            <button type="button" class="campaign-report-head__primary">Download Laporan</button>
        </div>
    </section>

    <section class="campaign-report-card campaign-report-card--delivery">
        <div class="campaign-report-card__status">
            <div class="campaign-report-card__status-badge">&#10003;</div>
            <div>
                <h2>Pengiriman</h2>
                <p class="campaign-report-card__status-main">{{ $successCount }} Pesan <strong>Berhasil Dikirim</strong></p>
                <p class="campaign-report-card__status-sub">{{ $failedCount }} pesan tidak terkirim</p>
            </div>
        </div>
        <div class="campaign-report-card__delivery-info">
            <p>Menampilkan 1 - {{ count($deliveries) }} dari {{ count($deliveries) }} Jadwal</p>
            <p>Jadwal Pengiriman 1</p>
        </div>
        <div class="campaign-report-card__delivery-result">
            <a href="#" class="campaign-report-card__link">Lihat Detil Pengiriman</a>
            <strong>Sukses: {{ $successCount }} | Gagal: {{ $failedCount }}</strong>
        </div>
    </section>

    <section class="campaign-report-card campaign-report-card--evaluation">
        <div class="campaign-report-card__section-head">
            <h2>Evaluasi Jangkauan Iklan Berdasarkan Lokasi</h2>
            <span class="campaign-report-card__muted-link">Selengkapnya</span>
        </div>
        <div class="campaign-report-card__empty-state">
            <strong>{{ $estimatedPotential }}</strong>
            <div>
                <p>Estimasi penerima potensial</p>
                <span>{{ $locationName }}</span>
            </div>
        </div>
    </section>

    <section class="campaign-report-card campaign-report-card--evaluation">
        <div class="campaign-report-card__section-head">
            <div>
                <h2>Evaluasi Jangkauan Iklan Berdasarkan Total Klik</h2>
                <p class="campaign-report-card__short-url">Short URL <strong>{{ $shortUrl }}</strong></p>
            </div>
            <a href="#" class="campaign-report-card__link">Selengkapnya</a>
        </div>
        <div class="campaign-report-metrics">
            <div class="campaign-report-metrics__item">
                <div class="campaign-report-card__status-badge">&#10003;</div>
                <div>
                    <span>Total Click</span>
                    <strong>{{ $totalClick }} Click</strong>
                    <small>{{ $successCount }} pesan terkirim</small>
                </div>
            </div>
            <div class="campaign-report-metrics__item">
                <div>
                    <span>Click Rate</span>
                    <strong>{{ $clickRate }}</strong>
                </div>
            </div>
        </div>
    </section>

    <section class="campaign-report-stack">
        @foreach ($reportSections as $section)
            <article class="campaign-report-detail" data-report-card="{{ $section['key'] }}">
                <button type="button" class="campaign-report-detail__head" data-report-toggle="{{ $section['key'] }}">
                    <div class="campaign-report-detail__title">
                        <span class="campaign-report-detail__icon campaign-report-detail__icon--{{ $section['icon'] }}"></span>
                        <h3>{{ $section['title'] }}</h3>
                    </div>
                    <span class="campaign-report-detail__toggle-label">Sembunyikan</span>
                </button>
                <div class="campaign-report-detail__body" data-report-body="{{ $section['key'] }}">
                    @if ($section['key'] === 'message')
                        <div class="campaign-report-copy">
                            <div class="campaign-report-copy__block">
                                <span>Pengirim</span>
                                <strong>{{ $senderName }}</strong>
                            </div>
                            <div class="campaign-report-copy__block">
                                <span>Isi Pesan (1 Pesan SMS)</span>
                                <strong>{{ $messageText }}</strong>
                            </div>
                            @unless ($isTargeted)
                                <div class="campaign-report-copy__block">
                                    <span>Lokasi</span>
                                    <strong>{{ $locationName }}</strong>
                                    <p>{{ $locationAddress }}</p>
                                </div>
                                <div class="campaign-report-grid">
                                    <div><span>Radius</span><strong>{{ $radius }} meter</strong></div>
                                    <div><span>Tipe Lokasi</span><strong>{{ $locationType }}</strong></div>
                                    <div><span>Estimasi Penerima Potensial</span><strong>{{ $estimatedPotential }}</strong></div>
                                </div>
                            @endunless
                            <div class="campaign-report-profile">
                                <h4>Profil</h4>
                                <div class="campaign-report-grid campaign-report-grid--profile">
                                    <div><span>Jenis Kelamin</span><strong>{{ is_array($gender) ? implode(', ', $gender) : $gender }}</strong></div>
                                    <div><span>Rentang Umur</span><strong>{{ is_array($age) ? implode(', ', $age) : $age }}</strong></div>
                                    <div><span>Agama</span><strong>{{ is_array($religion) ? implode(', ', $religion) : $religion }}</strong></div>
                                    <div><span>Jenis Handphone</span><strong>{{ is_array($device) ? implode(', ', $device) : $device }}</strong></div>
                                </div>
                            </div>
                        </div>
                    @elseif ($section['key'] === 'schedule')
                        <div class="campaign-report-schedule">
                            @foreach ($deliveries as $deliveryIndex => $delivery)
                                @php
                                    $startDate = data_get($delivery, 'deliveryStartDate') ?? data_get($delivery, 'deliveryDate') ?? data_get($delivery, 'startDate') ?? '';
                                    $endDate = data_get($delivery, 'deliveryEndDate') ?? data_get($delivery, 'endDate') ?? $startDate;
                                    $startTime = data_get($delivery, 'deliveryStartTime') ?? data_get($delivery, 'deliveryTime') ?? data_get($delivery, 'startTime') ?? '';
                                    $endTime = data_get($delivery, 'deliveryEndTime') ?? data_get($delivery, 'endTime') ?? '';
                                    $method = data_get($delivery, 'deliveryMethod') ?? data_get($delivery, 'method') ?? '-';
                                @endphp
                                <div class="campaign-report-schedule__row">
                                    <div>
                                        <span>Jadwal Pengiriman {{ $deliveryIndex + 1 }}</span>
                                        <strong>{{ $formatDate($startDate) }} - {{ $formatDate($endDate) }}</strong>
                                    </div>
                                    <div>
                                        <span>Jam Kirim</span>
                                        <strong>{{ $formatTime($startTime) }}{{ $endTime ? ' - ' . $formatTime($endTime) : '' }} WIB</strong>
                                    </div>
                                </div>
                                <div class="campaign-report-schedule__row">
                                    <div>
                                        <span>Metode Pengiriman</span>
                                        <strong>{{ $method }}</strong>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif ($section['key'] === 'test')
                        <div class="campaign-report-tests">
                            @forelse ($controlNumbers as $index => $number)
                                <strong>{{ $index + 1 }}) {{ $number }}</strong>
                            @empty
                                <strong>Nomor test tidak tersedia.</strong>
                            @endforelse
                            <div class="campaign-report-total">
                                <span>Total Pesan yang akan dikirim</span>
                                <strong>{{ $totalRecipient }} Pesan</strong>
                            </div>
                        </div>
                    @else
                        <div class="campaign-report-billing">
                            <p class="campaign-report-billing__label">Detil Biaya</p>
                            <div class="campaign-report-billing__table">
                                <div class="campaign-report-billing__section">
                                    <div class="campaign-report-billing__section-head">
                                        <span>Produk yang Dipilih</span>
                                        <span class="campaign-report-billing__caret"></span>
                                    </div>
                                    <div class="campaign-report-billing__row"><span>Kategori Iklan</span><strong>{{ $categoryLabel }}</strong></div>
                                    <div class="campaign-report-billing__row"><span>Tipe Kanal</span><strong>{{ $channelLabel }}</strong></div>
                                    <div class="campaign-report-billing__row"><span>Harga Satuan</span><strong>{{ $unitPrice }}</strong></div>
                                </div>
                                <div class="campaign-report-billing__section">
                                    <div class="campaign-report-billing__row campaign-report-billing__row--spread">
                                        <span>Pengaturan Pengiriman 1</span>
                                        <a href="#">Tampilkan</a>
                                    </div>
                                </div>
                                <div class="campaign-report-billing__section">
                                    <div class="campaign-report-billing__row campaign-report-billing__row--spread">
                                        <span>Grand Total <a href="#">Sembunyikan Detil</a></span>
                                        <strong>{{ $grandTotal }}</strong>
                                    </div>
                                </div>
                                <div class="campaign-report-billing__section">
                                    <div class="campaign-report-billing__section-head">
                                        <span>Pembayaran Anda Menggunakan</span>
                                        <span class="campaign-report-billing__caret"></span>
                                    </div>
                                    <div class="campaign-report-billing__row"><span>Saldo Refund</span><strong class="campaign-report-billing__danger">{{ $balanceMain }}</strong></div>
                                    
                                    <div class="campaign-report-billing__row"><span>Kuota Paket</span><strong>{{ $quota }}</strong></div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </article>
        @endforeach
    </section>
@endsection

@push('scripts')
    <script>
        (() => {
            document.querySelectorAll('[data-report-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const key = button.dataset.reportToggle;
                    const body = document.querySelector(`[data-report-body="${key}"]`);
                    const label = button.querySelector('.campaign-report-detail__toggle-label');
                    const isHidden = body.hasAttribute('hidden');

                    if (isHidden) {
                        body.removeAttribute('hidden');
                        label.textContent = 'Sembunyikan';
                    } else {
                        body.setAttribute('hidden', 'hidden');
                        label.textContent = 'Tampilkan';
                    }
                });
            });
        })();
    </script>
@endpush
