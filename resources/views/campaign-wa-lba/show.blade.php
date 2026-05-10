@php
    $title = $page['title'];
    $bodyClass = '';
    $mainClass = '';
    $contentClass = 'portal-content--campaign-report';
    $campaignTitle = $campaignRow['title'] ?? 'Belum diisi';
    preg_match('/Sukses:\s*([\d\.,]+)/i', $campaignRow['status_detail'] ?? '', $successMatch);
    preg_match('/Gagal:\s*([\d\.,]+)/i', $campaignRow['status_detail'] ?? '', $failedMatch);
    $successCount = $successMatch[1] ?? '8.769';
    $failedCount = $failedMatch[1] ?? '1.231';
    $navMenus = [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'dashboard',
            'href' => route('dashboard'),
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
            'label' => 'WA Business',
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
    $activeSubnav = 'wa-location-based-area';
    $reportSections = [
        ['key' => 'message', 'icon' => 'message', 'title' => 'Konten Pesan Iklan'],
        ['key' => 'profile', 'icon' => 'profile', 'title' => 'Profil Pengirim & Penerima'],
        ['key' => 'schedule', 'icon' => 'schedule', 'title' => 'Waktu Pengiriman'],
        ['key' => 'test', 'icon' => 'profile', 'title' => 'Nomor Tes Iklan'],
        ['key' => 'billing', 'icon' => 'billing', 'title' => 'Biaya Pembayaran'],
    ];
@endphp

@extends('layouts.portal')

@section('content')
    <section class="campaign-report-head">
        <div class="campaign-report-head__copy">
            <p class="campaign-report-head__breadcrumbs">Dashboard / Laporan / Detail Iklan</p>
            <p class="campaign-report-head__kicker">WA Business - Lba</p>
            <h1 class="campaign-report-head__title">{{ $campaignTitle }}</h1>
            <div class="campaign-report-head__meta">
                <span>Tanggal Penayangan Iklan: {{ $campaignRow['date'] }} - {{ $campaignRow['date'] }}</span>
                <span>ID: {{ $campaignRow['id'] }}</span>
            </div>
        </div>
        <div class="campaign-report-head__actions">
            <button type="button" class="campaign-report-head__primary">Download Laporan</button>
        </div>
    </section>

    <section class="campaign-report-card campaign-report-card--summary">
        <div class="campaign-report-summary">
            <div>
                <h2>Laporan Iklan - <span class="campaign-report-summary__done">DONE</span></h2>
                <p>Total Customer: 10.000</p>
            </div>
            <div class="campaign-report-summary__metrics">
                <div><strong>8.772</strong><span>Sent</span></div>
                <div><strong>{{ $successCount }}</strong><span>Delivered</span></div>
                <div><strong>4.287</strong><span>Read</span></div>
                <div><strong>0</strong><span>Click Short Url</span></div>
                <div><strong>{{ $failedCount }}</strong><span>Refunded</span></div>
            </div>
        </div>
    </section>

    <section class="campaign-report-card campaign-report-card--delivery">
        <div class="campaign-report-card__status">
            <div class="campaign-report-card__status-badge">✓</div>
            <div>
                <h2>Delivered</h2>
                <p class="campaign-report-card__status-main">{{ $successCount }} Pesan <strong>Berhasil Terkirim</strong></p>
                <p class="campaign-report-card__status-sub">Nomor Test Iklan <a href="#">Lihat Detail Nomor Test</a></p>
                <p class="campaign-report-card__status-sub">Total Biaya: <strong>Rp 0</strong></p>
                <p class="campaign-report-card__status-sub">Total Refund: <strong>Rp 0</strong></p>
                <p class="campaign-report-card__status-sub">Dari Total Pembayaran Awal: <strong>Rp 0</strong></p>
            </div>
        </div>
        <div class="campaign-report-card__delivery-info">
            <p>Menampilkan 1 - 2 dari 3 Jadwal</p>
            <p>[Jadwal Pengiriman 1] <strong>Sukses: 2.932</strong></p>
            <p>[Jadwal Pengiriman 2] <strong>Sukses: 2.853</strong></p>
        </div>
        <div class="campaign-report-card__delivery-result">
            <a href="#" class="campaign-report-card__link">Lihat Detail Pengiriman</a>
        </div>
    </section>

    <section class="campaign-report-card campaign-report-card--evaluation">
        <div class="campaign-report-card__section-head">
            <h2>Evaluasi Jangkauan Iklan Berdasarkan Total Read</h2>
            <a href="#" class="campaign-report-card__link">Lihat Detail Pengiriman</a>
        </div>
        <div class="campaign-report-metrics">
            <div class="campaign-report-metrics__item">
                <div class="campaign-report-card__status-badge">✓</div>
                <div>
                    <span>Total Read</span>
                    <strong>4.287 Read</strong>
                    <small>8.772 pesan terkirim</small>
                </div>
            </div>
            <div class="campaign-report-metrics__item">
                <div>
                    <span>View Rate</span>
                    <strong>48.87%</strong>
                </div>
            </div>
            <div class="campaign-report-metrics__item">
                <div>
                    <span>[Jadwal Pengiriman 1]</span>
                    <strong>Jumlah Read: 1.357</strong>
                    <small>[Jadwal Pengiriman 2] Jumlah Read: 1.432</small>
                </div>
            </div>
        </div>
    </section>

    <section class="campaign-report-card campaign-report-card--evaluation">
        <div class="campaign-report-card__section-head">
            <h2>Evaluasi Jangkauan Iklan Berdasarkan Pesan Delivered</h2>
            <a href="#" class="campaign-report-card__link">Selengkapnya</a>
        </div>
        <div class="campaign-report-summary__metrics campaign-report-summary__metrics--compact">
            <div><strong>32.57%</strong><span>Wanita</span></div>
            <div><strong>41.04%</strong><span>Pria</span></div>
            <div><strong>26.71%</strong><span>Usia 0 - 0 thn</span></div>
            <div><strong>99.97%</strong><span>Delivered Rate</span></div>
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
                                <span>Akun WA Business</span>
                                <strong>Telkomsel Promo & Rewards</strong>
                            </div>
                            <div class="campaign-report-copy__block">
                                <span>Template Pesan WA</span>
                                <strong>sehatdanbugarmyads</strong>
                            </div>
                            <div class="campaign-report-grid campaign-report-grid--profile">
                                <div><span>Tipe Template</span><strong>Basic</strong></div>
                                <div><span>Header</span><strong>0 Bytes</strong></div>
                            </div>
                            <div class="campaign-report-copy__block">
                                <span>Body</span>
                                <strong>Semangat pagi, sobat sehat! 👋 ☀️ Di tengah ramainya suasana CFD, jangan lewatkan kesempatan untuk menjangkau pelanggan yang tepat untuk bisnis Anda.</strong>
                            </div>
                            <div class="campaign-report-copy__block">
                                <span>Button</span>
                                <strong>Telkomsel Promo & Rewards</strong>
                            </div>
                            <div class="campaign-report-grid campaign-report-grid--profile">
                                <div><span>Tipe Template</span><strong>Basic</strong></div>
                                <div><span>Tipe Template</span><strong>Carousel</strong></div>
                            </div>
                            <div class="campaign-report-copy__block">
                                <span>Bubble Message</span>
                                <strong>Semangat pagi, sobat sehat! 👋 ☀️</strong>
                                <p>Di tengah ramainya suasana CFD, jangan lewatkan kesempatan untuk menjangkau pelanggan yang tepat untuk bisnis Anda.</p>
                            </div>
                            <div class="campaign-report-copy__block">
                                <span>Carousel Template</span>
                            </div>
                            <div class="campaign-report-accordion-list">
                                <button type="button" class="campaign-report-accordion-list__item">Card 1</button>
                                <button type="button" class="campaign-report-accordion-list__item">Card 2</button>
                            </div>
                            <button type="button" class="campaign-report-head__ghost campaign-report-preview-btn">Lihat Preview Iklan</button>
                        </div>
                    @elseif ($section['key'] === 'profile')
                        <div class="campaign-report-copy">
                            <div class="campaign-report-copy__block">
                                <span>Tipe Iklan</span>
                                <strong>LBA</strong>
                            </div>
                            <div class="campaign-report-copy__block">
                                <span>Lokasi</span>
                                <strong>fX Sudirman</strong>
                                <p>Jl. Jenderal Sudirman, RT.1/RW.3, Gelora, Kecamatan Tanah Abang, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10270, Indonesia</p>
                            </div>
                            <div class="campaign-report-grid">
                                <div><span>Radius</span><strong>3000 meter</strong></div>
                                <div><span>Tipe Lokasi</span><strong>Indoor & Outdoor</strong></div>
                                <div><span>Estimasi Penerima Potensial</span><strong>149042-182162</strong></div>
                            </div>
                        </div>
                    @elseif ($section['key'] === 'schedule')
                        <div class="campaign-report-schedule">
                            <div class="campaign-report-schedule__row">
                                <div>
                                    <span>Jadwal Pengiriman Target 1</span>
                                    <strong>19 Apr 2026 - 19 Apr 2026</strong>
                                </div>
                                <div>
                                    <span>Jam Kirim</span>
                                    <strong>08:00 - 12:47 WIB</strong>
                                </div>
                            </div>
                        </div>
                    @elseif ($section['key'] === 'test')
                        <div class="campaign-report-copy">
                            <p class="campaign-report-card__status-sub">Biaya Test Iklan ditanggung pelanggan.</p>
                        </div>
                    @else
                        <div class="campaign-report-billing">
                            <p class="campaign-report-billing__label">Detil Biaya</p>
                            <div class="campaign-report-billing__table campaign-report-billing__table--wide">
                                <div class="campaign-report-billing__section">
                                    <div class="campaign-report-billing__section-head">
                                        <span>Produk yang Dipilih</span>
                                        <span class="campaign-report-billing__caret"></span>
                                    </div>
                                    <div class="campaign-report-billing__row"><span>Kategori Iklan</span><strong>WA Business</strong></div>
                                    <div class="campaign-report-billing__row"><span>Tipe Kanal</span><strong>LBA</strong></div>
                                    <div class="campaign-report-billing__row"><span>Harga Satuan</span><strong>Rp 1.100</strong></div>
                                </div>
                                <div class="campaign-report-billing__section">
                                    <div class="campaign-report-billing__row"><span>Jumlah Pesan Dikirim</span><strong>10.000 Pesan</strong></div>
                                    <div class="campaign-report-billing__row"><span>Total Harga</span><strong>Rp 11.000.000</strong></div>
                                    <div class="campaign-report-billing__row campaign-report-billing__row--spread">
                                        <span>Grand Total <a href="#">Sembunyikan Detil</a></span>
                                        <strong>Rp 11.000.000</strong>
                                    </div>
                                </div>
                                <div class="campaign-report-billing__section">
                                    <div class="campaign-report-billing__section-head">
                                        <span>Pembayaran Anda Menggunakan</span>
                                        <span class="campaign-report-billing__caret"></span>
                                    </div>
                                    <div class="campaign-report-billing__row"><span>Saldo Umum</span><strong class="campaign-report-billing__danger">Rp 0</strong></div>
                                    <div class="campaign-report-billing__row"><span>Kuota Paket</span><strong>10.000 Pesan</strong></div>
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
