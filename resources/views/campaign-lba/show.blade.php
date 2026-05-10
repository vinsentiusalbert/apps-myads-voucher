@php
    $title = $page['title'];
    $bodyClass = '';
    $mainClass = '';
    $contentClass = 'portal-content--campaign-report';
    $campaignTitle = $campaignRow['title'] ?? 'Belum diisi';
    preg_match('/Sukses:\s*([\d\.,]+)/i', $campaignRow['status_detail'] ?? '', $successMatch);
    preg_match('/Gagal:\s*([\d\.,]+)/i', $campaignRow['status_detail'] ?? '', $failedMatch);
    $successCount = $successMatch[1] ?? '1.500';
    $failedCount = $failedMatch[1] ?? '0';
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
    $activeNav = 'sms';
    $activeSubnav = 'sms-location-based-area';
    $reportSections = [
        [
            'key' => 'message',
            'icon' => 'message',
            'title' => 'Konten Pesan Iklan',
        ],
        [
            'key' => 'schedule',
            'icon' => 'schedule',
            'title' => 'Jadwal Pengiriman',
        ],
        [
            'key' => 'test',
            'icon' => 'profile',
            'title' => 'Nomor Test Iklan',
        ],
        [
            'key' => 'billing',
            'icon' => 'billing',
            'title' => 'Biaya Pembayaran',
        ],
    ];
@endphp

@extends('layouts.portal')

@section('content')
    <section class="campaign-report-head">
        <div class="campaign-report-head__copy">
            <p class="campaign-report-head__breadcrumbs">Dashboard / Laporan / Detail Iklan</p>
            <p class="campaign-report-head__kicker">Location Based Advertising - SMS</p>
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

    <section class="campaign-report-card campaign-report-card--delivery">
        <div class="campaign-report-card__status">
            <div class="campaign-report-card__status-badge">✓</div>
            <div>
                <h2>Pengiriman</h2>
                <p class="campaign-report-card__status-main">{{ $successCount }} Pesan <strong>Berhasil Dikirim</strong></p>
                <p class="campaign-report-card__status-sub">{{ $failedCount }} pesan tidak terkirim</p>
            </div>
        </div>
        <div class="campaign-report-card__delivery-info">
            <p>Menampilkan 1 - 1 dari 1 Jadwal</p>
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
            <strong>N/a</strong>
            <div>
                <p>Statistik jangkauan iklan tidak tersedia.</p>
                <span>Data tidak dapat diidentifikasi dan dipopulasi.</span>
            </div>
        </div>
    </section>

    <section class="campaign-report-card campaign-report-card--evaluation">
        <div class="campaign-report-card__section-head">
            <div>
                <h2>Evaluasi Jangkauan Iklan Berdasarkan Total Klik</h2>
                <p class="campaign-report-card__short-url">Short URL <strong>grandhikaadmin</strong></p>
            </div>
            <a href="#" class="campaign-report-card__link">Selengkapnya</a>
        </div>
        <div class="campaign-report-metrics">
            <div class="campaign-report-metrics__item">
                <div class="campaign-report-card__status-badge">✓</div>
                <div>
                    <span>Total Click</span>
                    <strong>9 Click</strong>
                    <small>1.500 pesan terkirim</small>
                </div>
            </div>
            <div class="campaign-report-metrics__item">
                <div>
                    <span>Click Rate</span>
                    <strong>0.6%</strong>
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
                                <strong>GRANDHIKA</strong>
                            </div>
                            <div class="campaign-report-copy__block">
                                <span>Isi Pesan (1 Pesan SMS)</span>
                                <strong>WORK FROM 65 - Hotel GranDhika Iskandarsyah Jakarta, hanya 250K, kamu bisa nikmatin workspace nyaman + fasilitas hotel yang lengkap. myads.id/grandhikaadmin</strong>
                            </div>
                            <div class="campaign-report-copy__block">
                                <span>Lokasi</span>
                                <strong>Hotel GranDhika Iskandarsyah Jakarta</strong>
                                <p>Jl. Iskandarsyah Raya No.65, RT.5/RW.2, Melawai, Kec. Kby. Baru, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12160, Indonesia</p>
                            </div>
                            <div class="campaign-report-grid">
                                <div><span>Radius</span><strong>3000 meter</strong></div>
                                <div><span>Tipe Lokasi</span><strong>Indoor & Outdoor</strong></div>
                                <div><span>Estimasi Penerima Potensial</span><strong>160386-196026</strong></div>
                            </div>
                            <div class="campaign-report-profile">
                                <h4>Profil</h4>
                                <div class="campaign-report-grid campaign-report-grid--profile">
                                    <div><span>Jenis Kelamin</span><strong>Semua</strong></div>
                                    <div><span>Rentang Umur</span><strong>25 tahun - 34|35 tahun</strong></div>
                                    <div><span>Agama</span><strong>Semua</strong></div>
                                    <div><span>Jenis Handphone</span><strong>Semua</strong></div>
                                </div>
                            </div>
                        </div>
                    @elseif ($section['key'] === 'schedule')
                        <div class="campaign-report-schedule">
                            <div class="campaign-report-schedule__row">
                                <div>
                                    <span>Jadwal Pengiriman 1</span>
                                    <strong>22 Apr 2026 - 22 Apr 2026</strong>
                                </div>
                                <div>
                                    <span>Jam Kirim</span>
                                    <strong>08.00 - 12.00 WIB</strong>
                                </div>
                            </div>
                            <div class="campaign-report-schedule__row">
                                <div>
                                    <span>Metode Pengiriman</span>
                                    <strong>Pengiriman Bagi Rata</strong>
                                </div>
                            </div>
                        </div>
                    @elseif ($section['key'] === 'test')
                        <div class="campaign-report-tests">
                            <strong>1) +6281219719717</strong>
                            <strong>2) +6281259660302</strong>
                            <strong>3) +628129092016</strong>
                            <strong>4) +628123462507</strong>
                            <strong>5) +6282246919058</strong>
                            <div class="campaign-report-total">
                                <span>Total Pesan yang akan dikirim</span>
                                <strong>1.500 Pesan</strong>
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
                                    <div class="campaign-report-billing__row"><span>Kategori Iklan</span><strong>Location Based Advertising</strong></div>
                                    <div class="campaign-report-billing__row"><span>Tipe Kanal</span><strong>SMS</strong></div>
                                    <div class="campaign-report-billing__row"><span>Harga Satuan</span><strong>Rp 200</strong></div>
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
                                        <strong>{{ $campaignRow['total_price'] ?? '-' }}</strong>
                                    </div>
                                </div>
                                <div class="campaign-report-billing__section">
                                    <div class="campaign-report-billing__section-head">
                                        <span>Pembayaran Anda Menggunakan</span>
                                        <span class="campaign-report-billing__caret"></span>
                                    </div>
                                    <div class="campaign-report-billing__row"><span>Saldo Umum</span><strong class="campaign-report-billing__danger">Rp 0</strong></div>
                                    <div class="campaign-report-billing__row"><span>Saldo Monetary</span><strong>Rp 300000</strong></div>
                                    <div class="campaign-report-billing__row"><span>Kuota Paket</span><strong>0 Pesan</strong></div>
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
