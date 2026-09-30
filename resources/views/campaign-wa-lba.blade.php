@php
    $title = $page['title'];
    $mainClass = 'campaign-main';
    $contentClass = 'campaign-content';
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
    $activeSubnav = 'wa-location-based-area';
    $waTemplates = collect($waTemplates ?? []);
@endphp

@extends('layouts.portal')

@push('styles')
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    >
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css"
    >
@endpush

@section('content')
    <section class="campaign-page-intro">
        <p class="campaign-page-intro__crumb">Dashboard / Buat Iklan WA Business LBA</p>
        <h1 class="campaign-page-intro__title">Buat Iklan WA Business LBA</h1>
    </section>

    <section class="campaign-stepper-card">
        <div class="campaign-stepper">
            @foreach ($page['steps'] as $index => $step)
                <div class="campaign-step {{ $index === 0 ? 'campaign-step--active' : '' }}" data-wa-lba-step="{{ $index + 1 }}">
                    <span class="campaign-step__dot">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="campaign-step__label">{{ $step }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- <section class="campaign-info-banner">
        <span class="campaign-info-banner__icon">i</span>
        <p>Pelajari cara membuat iklan Anda agar lebih menarik. <a href="https://www.youtube.com/watch?v=0JDF-U6Yze0">Pelajari Selengkapnya.</a></p>
    </section> --}}

    <section class="campaign-compose-card campaign-compose-card--single">
        <div class="campaign-compose-card__header campaign-compose-card__header--single">
            <div>
                <h2 class="campaign-compose-card__section-title">Template Pesan</h2>
            </div>
        </div>

        <div class="campaign-step-panel" data-wa-lba-panel="1">
        <div class="campaign-wa-lba-step" id="waLbaStep">
            <div class="campaign-wa-lba-step__layout">
                <div class="campaign-wa-lba-step__main">
                    <div class="field-group campaign-wa-lba-step__field">
                        <label for="waLbaTemplate" class="field-label sr-only">Template Pesan</label>
                        <select id="waLbaTemplate" class="text-input text-input--select">
                            <option value="">Template Pesan</option>
                            @foreach ($waTemplates as $template)
                                <option value="{{ $template['id'] }}">{{ $template['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campaign-wa-lba-template" id="waLbaTemplateDetails" hidden>
                        <p class="campaign-wa-lba-template__note" id="waLbaPriceNote"></p>

                        <div class="campaign-wa-lba-template__meta">
                            <div>
                                <span>Akun WA Business</span>
                                <strong id="waLbaAccountName">-</strong>
                            </div>
                            <div>
                                <span>Tipe Template</span>
                                <strong id="waLbaTemplateKind">-</strong>
                            </div>
                            <div>
                                <span>Tipe Template</span>
                                <strong id="waLbaChannelKind">-</strong>
                            </div>
                        </div>

                        <div class="campaign-wa-lba-template__body">
                            <span>Bubble Message</span>
                            <strong id="waLbaBubbleHeadline">-</strong>
                            <p id="waLbaBubbleMessage">-</p>
                        </div>

                        <div class="campaign-wa-lba-template__cards" id="waLbaCardsBlock" hidden>
                            <span>Carousel Template</span>
                            <div class="campaign-wa-lba-template__accordion" id="waLbaCardsAccordion"></div>
                        </div>
                    </div>
                </div>

                <aside class="campaign-wa-lba-preview" id="waLbaPreview" hidden>
                    <div class="campaign-wa-lba-preview__phone" id="waLbaPreviewPhone">
                        <div class="campaign-wa-lba-preview__topbar">
                            <span class="campaign-wa-lba-preview__back">←</span>
                            <div class="campaign-wa-lba-preview__profile">
                                <span class="campaign-wa-lba-preview__avatar"></span>
                                <div>
                                    <strong id="waLbaPreviewName">Name</strong>
                                    <span id="waLbaPreviewSubtitle">Business Account</span>
                                </div>
                            </div>
                            <span class="campaign-wa-lba-preview__menu">⋮</span>
                        </div>

                        <div class="campaign-wa-lba-preview__chat">
                            <div class="campaign-wa-lba-preview__bubble">
                                <strong id="waLbaPreviewHeadline">-</strong>
                                <p id="waLbaPreviewMessage">-</p>
                            </div>

                            <div class="campaign-wa-lba-preview__carousel">
                                <img src="{{ asset('assets/shopads-icon.svg') }}" alt="Preview template" id="waLbaPreviewAsset">
                                <div class="campaign-wa-lba-preview__carousel-copy">
                                    <span class="campaign-wa-lba-preview__brand">ShopAds</span>
                                    <strong id="waLbaPreviewCardTitle">Template Carousel</strong>
                                    <p id="waLbaPreviewCardCaption">Detail card pertama akan tampil di sini.</p>
                                </div>
                            </div>

                            <div class="campaign-wa-lba-preview__action" id="waLbaPreviewCta">Coba Sekarang</div>
                        </div>
                    </div>
                </aside>
            </div>

            <div class="campaign-form-actions campaign-form-actions--wa-lba">
                {{-- <button type="button" class="campaign-draft-btn">
                    <span class="campaign-draft-btn__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M6 3.75h8.75L19.5 8.5v11a1.75 1.75 0 0 1-1.75 1.75H6A1.75 1.75 0 0 1 4.25 19.5V5.5A1.75 1.75 0 0 1 6 3.75Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M14.75 3.75V8.5h4.75" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M8 12h8M8 16h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span>Simpan Iklan Sebagai Draft</span>
                </button> --}}

                <button type="button" class="submit-btn lba-primary-btn campaign-step-nav-btn" id="waLbaNextButton" disabled>
                    Lanjutkan
                </button>
            </div>
        </div>
        </div>

        <div class="campaign-step-panel" data-wa-lba-panel="2" hidden>
            <section class="campaign-step-two">
                <div class="campaign-step-two__section">
                    <h2 class="campaign-block__title">Atur Lokasi Target</h2>
                    <div class="campaign-location-row campaign-location-row--single">
                        <button type="button" class="campaign-outline-button campaign-outline-button--wide" id="campaignOpenLocationModal" onclick="window.openCampaignLocationModal && window.openCampaignLocationModal()">
                            <span class="campaign-outline-button__icon" id="campaignLocationButtonIcon">+</span>
                            <span class="campaign-location-button__content" id="campaignLocationButtonContent">
                                <span class="campaign-location-button__title">Tambah Lokasi</span>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="campaign-step-two__section">
                    <h2 class="campaign-block__title">Atur Profil Target</h2>
                    <p class="campaign-block__copy">
                        Anda dapat membuat target lebih spesifik dengan menentukan profil dan lokasi penerima. Semakin banyak profil yang dipilih akan memengaruhi estimasi penerima potensial.
                    </p>

                    <div class="campaign-profile-grid">
                        <div class="field-group campaign-field-group--compact">
                            <label for="campaign_gender" class="field-label">Jenis Kelamin</label>
                            <select id="campaign_gender" class="campaign-search-multiselect" multiple data-placeholder="Cari jenis kelamin">
                                <option value="male">Laki-laki</option>
                                <option value="female">Perempuan</option>
                                <option value="all">Semua</option>
                            </select>
                        </div>

                        <div class="field-group campaign-field-group--compact">
                            <label for="campaign_age" class="field-label">Rentang Umur</label>
                            <select id="campaign_age" class="campaign-search-multiselect" multiple data-placeholder="Cari rentang umur">
                                <option value="under-15">&lt; 15 tahun</option>
                                <option value="15-24">15 - 24 tahun</option>
                                <option value="25-34">25 - 34 tahun</option>
                                <option value="35-44">35 - 44 tahun</option>
                                <option value="45-plus">45+ tahun</option>
                            </select>
                        </div>

                        <div class="field-group campaign-field-group--compact">
                            <label for="campaign_religion" class="field-label">Agama</label>
                            <select id="campaign_religion" class="campaign-search-multiselect" multiple data-placeholder="Cari agama">
                                <option value="islam">Islam</option>
                                <option value="kristen">Kristen</option>
                                <option value="katolik">Katolik</option>
                                <option value="hindu">Hindu</option>
                                <option value="budha">Budha</option>
                                <option value="konghucu">Konghucu</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- <div class="campaign-send-grid">
                    <div class="campaign-step-two__section">
                        <h2 class="campaign-block__title">Hitung Estimasi Penerima Potensial</h2>
                        <p class="campaign-block__copy">Gunakan lokasi dan profil target di atas untuk melihat estimasi penerima potensial sebelum melanjutkan ke tahap pengiriman.</p>
                    </div>
                    <div class="campaign-estimate-card">
                        <span class="campaign-estimate-card__label">Estimasi Penerima Potensial</span>
                        <strong id="campaignAudienceEstimate">0</strong>
                    </div>
                </div> --}}

                <div class="campaign-form-actions campaign-form-actions--step-two">
                    <button type="button" class="campaign-outline-button campaign-step-nav-btn" id="waLbaStepTwoBack" hidden>Sebelumnya</button>
                    <button type="button" class="submit-btn lba-primary-btn campaign-step-two__next" id="waLbaStepTwoNext" disabled>Lanjutkan</button>
                </div>
            </section>
        </div>

        <div class="campaign-step-panel" data-wa-lba-panel="3" hidden>
            <section class="campaign-step-two">
                <div class="campaign-step-two__section">
                    <h2 class="campaign-block__title">Tentukan Jumlah Penerima yang Akan Dikirim</h2>
                    <div class="campaign-send-grid">
                        <div class="field-group campaign-field-group--compact">
                            <label for="waLbaRecipientCount" class="field-label">Jumlah Penerima</label>
                            <input id="waLbaRecipientCount" type="number" class="text-input" min="5" value="0" placeholder="Jumlah Penerima">
                            <p class="campaign-step-two__alert">Minimal 5 jumlah penerima perhari</p>
                            {{-- <p class="campaign-block__copy">Berdasarkan policy Meta, batas maksimum kuota pengiriman Anda dalam satu hari sebesar 100.000 pesan.</p> --}}
                        </div>
                        {{-- <div class="campaign-estimate-card">
                            <span class="campaign-estimate-card__label">Estimasi Maksimal Penerima Potensial</span>
                            <strong id="waLbaRecipientEstimateRange">140.630-171.880</strong>
                        </div> --}}
                    </div>
                </div>

                <div class="campaign-step-two__section">
                    <h2 class="campaign-block__title">Jadwal Pengiriman Pesan</h2>
                    <div class="campaign-schedule-list" id="waLbaScheduleList">
                        <article class="campaign-schedule-card" data-schedule-item="1">
                            <div class="campaign-schedule-card__header">
                                <span>Jadwal Pengiriman 1</span>
                                <button type="button" class="campaign-schedule-card__remove" data-remove-schedule hidden>Hapus</button>
                            </div>
                            <div class="campaign-schedule-card__grid">
                                <div class="field-group campaign-field-group--compact">
                                    <label class="field-label">Tanggal Kirim</label>
                                    <div class="campaign-range-field">
                                        <input id="waLbaScheduleDate1Start" type="date" class="text-input" value="{{ now()->addDay()->toDateString() }}" min="{{ now()->toDateString() }}" data-schedule-date-start="1">
                                        <span class="campaign-range-field__separator">-</span>
                                        <input id="waLbaScheduleDate1End" type="date" class="text-input" value="{{ now()->addDay()->toDateString() }}" min="{{ now()->toDateString() }}" data-schedule-date-end="1">
                                    </div>
                                    <p class="campaign-range-preview" id="waLbaScheduleDatePreview1">{{ now()->addDay()->format('d/m/y') }} - {{ now()->addDay()->format('d/m/y') }}</p>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label class="field-label">Jam Kirim</label>
                                    <div class="campaign-time-picker" data-time-picker="1">
                                        <button type="button" class="campaign-time-picker__trigger" data-time-trigger="1">
                                            <span class="campaign-time-picker__value" id="waLbaScheduleTimePreview1">13:00-13:30 WIB</span>
                                            <span class="campaign-time-picker__clock">◷</span>
                                        </button>
                                        <div class="campaign-time-picker__panel" data-time-panel="1" hidden>
                                            <div class="campaign-time-picker__columns">
                                                <div class="campaign-time-picker__column">
                                                    <span class="campaign-time-picker__label">Jam Mulai</span>
                                                    <div class="campaign-time-picker__inputs">
                                                        <select id="waLbaScheduleTime1StartHour" class="campaign-time-picker__select" data-schedule-time-start-hour="1"></select>
                                                        <span class="campaign-time-picker__colon">:</span>
                                                        <select id="waLbaScheduleTime1StartMinute" class="campaign-time-picker__select" data-schedule-time-start-minute="1"></select>
                                                        <span class="campaign-time-picker__suffix">WIB</span>
                                                    </div>
                                                </div>
                                                <div class="campaign-time-picker__column">
                                                    <span class="campaign-time-picker__label">Jam Berakhir</span>
                                                    <div class="campaign-time-picker__inputs">
                                                        <select id="waLbaScheduleTime1EndHour" class="campaign-time-picker__select" data-schedule-time-end-hour="1"></select>
                                                        <span class="campaign-time-picker__colon">:</span>
                                                        <select id="waLbaScheduleTime1EndMinute" class="campaign-time-picker__select" data-schedule-time-end-minute="1"></select>
                                                        <span class="campaign-time-picker__suffix">WIB</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="campaign-time-picker__hint">Disarankan durasi pengiriman pesan lebih dari 4 jam agar pengiriman lebih optimal</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="campaign-step-two__alert">Pembuatan iklan tidak boleh sebelum waktu sekarang.</p>
                        </article>
                    </div>
                </div>

                <div class="campaign-step-two__section">
                    <div class="field-group">
                        <label for="waLbaDeliveryMethod" class="field-label">Metode Pengiriman</label>
                        <select id="waLbaDeliveryMethod" class="text-input text-input--select">
                            <option value="secepatnya" selected>Pengiriman Secepatnya</option>
                            <option value="merata">Pengiriman Bagi Rata</option>
                        </select>
                    </div>
                    <p class="campaign-block__copy">
                        Metode pengiriman pesan secepatnya akan memaksimalkan pesan Anda dikirim sesuai jadwal yang ditentukan. Apabila terdapat pesan yang belum terkirim pada jadwal pertama, maka pesan tersebut akan dikirim sesuai jadwal selanjutnya.
                    </p>
                </div>

                <div class="campaign-step-two__section">
                    <h2 class="campaign-block__title">Tentukan Nomor Test</h2>
                    <div class="field-group">
                        <label for="waLbaTestRecipient" class="field-label">Nomor Tes Iklan</label>
                        <select id="waLbaTestRecipient" class="text-input text-input--select">
                            <option value="">Nomor Tes Iklan</option>
                            <option>081234567890</option>
                            <option>082345678901</option>
                        </select>
                    </div>
                    <p class="campaign-block__copy">
                        Nomor test digunakan sebagai nomor penerima pesan untuk memastikan apakah pesan berhasil terkirim. Nomor test akan menerima pesan 1 jam sebelum jadwal pengiriman.
                    </p>
                    <p class="campaign-block__copy"><strong>Nomor test yang digunakan harus terdaftar pada Whatsapp</strong></p>
                </div>

                <div class="campaign-step-two__section">
                    <label class="campaign-terms">
                        <input type="checkbox" id="waLbaTermsCheck">
                        <span>
                            Saya menyetujui <a href="{{ route('campaign.terms') }}" data-campaign-terms-trigger>Syarat dan Ketentuan</a> yang berlaku di website ShopAds. Atas setiap pesan iklan yang dibuat oleh pengguna menggunakan produk dan/atau layanan melalui portal ShopAds, pengguna dilarang untuk mempergunakan kata-kata, komentar, gambar atau konten apapun yang mengandung unsur SARA atau diskriminasi terhadap pihak manapun, bersifat vulgar dan ancaman, atau hal-hal lain yang dapat dianggap tidak sesuai dengan nilai dan norma sosial.
                        </span>
                    </label>
                </div>

                @include('partials.campaign-terms-modal')

                <div class="campaign-form-actions campaign-form-actions--step-two">
                    <button type="button" class="campaign-outline-button campaign-step-nav-btn" id="waLbaStepThreeBack">Sebelumnya</button>
                    {{-- <button type="button" class="campaign-draft-btn">
                        <span class="campaign-draft-btn__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M6 3.75h8.75L19.5 8.5v11a1.75 1.75 0 0 1-1.75 1.75H6A1.75 1.75 0 0 1 4.25 19.5V5.5A1.75 1.75 0 0 1 6 3.75Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <path d="M14.75 3.75V8.5h4.75" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <path d="M8 12h8M8 16h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>Simpan Iklan Sebagai Draft</span>
                    </button> --}}
                    <button type="button" class="submit-btn lba-primary-btn campaign-step-two__next" id="waLbaStepThreeNext" disabled>Lanjutkan</button>
                </div>
            </section>
        </div>

        <div class="campaign-step-panel" data-wa-lba-panel="4" hidden>
            <section class="campaign-review">
                <div class="campaign-review__main">
                    <div class="campaign-review__hero">
                        <h2 class="campaign-review__title">Review</h2>
                    </div>

                    <div class="campaign-review__field">
                        <div class="campaign-review__field-head">
                            <span class="campaign-review__label">Judul Iklan</span>
                            <button type="button" class="campaign-review-card__link" data-wa-lba-review-edit="1">Ubah</button>
                        </div>
                        <strong id="waLbaReviewTitle">-</strong>
                    </div>

                    <article class="campaign-review-card" data-wa-lba-review-card="content">
                        <div class="campaign-review-card__header">
                            <div class="campaign-review-card__head">
                                <span class="campaign-review-card__icon">💬</span>
                                <h3>Konten Iklan</h3>
                            </div>
                            <div class="campaign-review-card__actions">
                                <button type="button" class="campaign-review-card__link" data-wa-lba-review-edit="1">Ubah</button>
                                <button type="button" class="campaign-review-card__toggle" data-wa-lba-review-toggle="content">Tampilkan</button>
                            </div>
                        </div>
                        <div class="campaign-review-card__body" data-wa-lba-review-body="content" hidden>
                            <div class="campaign-review-grid">
                                <div>
                                    <span class="campaign-review-grid__label">Template Pesan</span>
                                    <strong id="waLbaReviewTemplateName">-</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Akun WA Business</span>
                                    <strong id="waLbaReviewSender">-</strong>
                                </div>
                                <div class="campaign-review-grid__wide">
                                    <span class="campaign-review-grid__label">Bubble Message</span>
                                    <strong id="waLbaReviewMessage">-</strong>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="campaign-review-card" data-wa-lba-review-card="recipient">
                        <div class="campaign-review-card__header">
                            <div class="campaign-review-card__head">
                                <span class="campaign-review-card__icon">👥</span>
                                <h3>Profil Penerima</h3>
                            </div>
                            <div class="campaign-review-card__actions">
                                <button type="button" class="campaign-review-card__link" data-wa-lba-review-edit="2">Ubah</button>
                                <button type="button" class="campaign-review-card__toggle" data-wa-lba-review-toggle="recipient">Tampilkan</button>
                            </div>
                        </div>
                        <div class="campaign-review-card__body" data-wa-lba-review-body="recipient" hidden>
                            <div class="campaign-review-grid">
                                <div class="campaign-review-grid__wide">
                                    <span class="campaign-review-grid__label">Lokasi</span>
                                    <strong id="waLbaReviewLocation">-</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Radius</span>
                                    <strong id="waLbaReviewRadius">300 meter</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Jenis Kelamin</span>
                                    <strong id="waLbaReviewGender">Semua</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Rentang Umur</span>
                                    <strong id="waLbaReviewAge">Semua</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Agama</span>
                                    <strong id="waLbaReviewReligion">Semua</strong>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="campaign-review-card" data-wa-lba-review-card="delivery">
                        <div class="campaign-review-card__header">
                            <div class="campaign-review-card__head">
                                <span class="campaign-review-card__icon">⏰</span>
                                <h3>Waktu Pengiriman</h3>
                            </div>
                            <div class="campaign-review-card__actions">
                                <button type="button" class="campaign-review-card__link" data-wa-lba-review-edit="3">Ubah</button>
                                <button type="button" class="campaign-review-card__toggle" data-wa-lba-review-toggle="delivery">Tampilkan</button>
                            </div>
                        </div>
                        <div class="campaign-review-card__body" data-wa-lba-review-body="delivery" hidden>
                            <div class="campaign-review-schedule" id="waLbaReviewSchedules"></div>
                            <div class="campaign-review-grid campaign-review-grid--delivery">
                                <div>
                                    <span class="campaign-review-grid__label">Metode Pengiriman</span>
                                    <strong id="waLbaReviewDeliveryMethod">Pengiriman Secepatnya</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Nomor Test Iklan</span>
                                    <strong id="waLbaReviewTestRecipient">-</strong>
                                </div>
                            </div>
                            <div class="campaign-review-total">
                                <span>Total Pesan yang akan dikirim</span>
                                <strong id="waLbaReviewRecipientCount">0 Pesan</strong>
                            </div>
                            <p class="campaign-review-note">dari estimasi <span id="waLbaReviewPotentialRange">0</span> penerima potensial</p>
                        </div>
                    </article>
                </div>

                <aside class="campaign-review__sidebar">
                    <div class="campaign-cost-card">
                        <div class="campaign-cost-card__topbar"></div>
                        <div class="campaign-cost-card__body">
                            <h3>Detil Biaya</h3>
                            <div class="campaign-cost-card__section">
                                <span class="campaign-cost-card__section-title">Produk yang Dipilih</span>
                                <div class="campaign-cost-card__row">
                                    <span>Kategori Iklan</span>
                                    <strong>WA Business</strong>
                                </div>
                                <div class="campaign-cost-card__row">
                                    <span>Tipe Kanal</span>
                                    <strong>LBA</strong>
                                </div>
                                <div class="campaign-cost-card__row">
                                    <span>Harga</span>
                                    <strong>Rp 1.100</strong>
                                </div>
                                <p class="campaign-cost-card__footnote">Harga iklan sebesar <strong>Rp 1.100 per pesan</strong> karena menggunakan Display Name Default.</p>
                            </div>
                            <div class="campaign-cost-card__section">
                                <div class="campaign-cost-card__row campaign-cost-card__row--total">
                                    <span>Grand Total</span>
                                    <strong id="waLbaReviewGrandTotal">Rp 5.500</strong>
                                </div>
                            </div>
                            <div class="campaign-cost-card__section">
                                <span class="campaign-cost-card__section-title">Saldo & Paket Anda</span>
                                <div class="campaign-cost-card__row">
                                    <span>Gunakan Paket?</span>
                                    <strong class="campaign-cost-card__warning">(Tersisa 1757 Pesan)</strong>
                                </div>
                                <div class="campaign-cost-card__row">
                                    <span>Saldo Umum</span>
                                    <strong>Rp 2.443.005</strong>
                                </div>
                            </div>
                            <div class="campaign-cost-card__section">
                                <span class="campaign-cost-card__section-title">Pembayaran Anda Menggunakan</span>
                                <div class="campaign-cost-card__row">
                                    <span>Saldo Umum</span>
                                    <strong class="campaign-cost-card__danger" id="waLbaReviewPayment">Rp 5.500</strong>
                                </div>
                            </div>
                            <button type="button" class="campaign-cost-card__cta">Bayar & Kirim Iklan</button>
                            <p class="campaign-cost-card__footnote">
                                Apabila terdapat pesan yang tidak terkirim, maka biaya akan dikembalikan (refund) sesuai jumlah pesan yang tidak terkirim. Detil mengenai jumlah iklan terkirim/tidak terkirim akan diinformasikan pada menu laporan setelah iklan Anda tayang.
                            </p>
                        </div>
                    </div>
                </aside>
                <div class="campaign-form-actions campaign-form-actions--review campaign-form-actions--review-bottom">
                    <button type="button" class="campaign-outline-button campaign-step-nav-btn" id="waLbaStepFourBack">Sebelumnya</button>
                </div>
            </section>
        </div>
    </section>
@endsection

@section('after_shell')
    <div class="campaign-map-modal" id="campaignMapModal" hidden aria-hidden="true">
        <div class="campaign-map-modal__backdrop" data-location-close onclick="window.closeCampaignLocationModal && window.closeCampaignLocationModal()"></div>
        <div class="campaign-map-modal__card" role="dialog" aria-modal="true" aria-labelledby="campaignMapHeading">
            <div class="campaign-map-modal__layout">
                <div class="campaign-map-panel">
                    <div class="campaign-map-panel__canvas campaign-map-panel__canvas--live" id="campaignMapCanvas"></div>
                    <p class="campaign-map-panel__hint">Klik peta atau cari alamat untuk menentukan titik target.</p>
                </div>

                <div class="campaign-map-controls">
                    <div class="campaign-map-controls__top">
                        <h2 id="campaignMapHeading">Tambah Lokasi</h2>
                        <button type="button" class="campaign-map-close" data-location-close aria-label="Tutup lokasi" onclick="window.closeCampaignLocationModal && window.closeCampaignLocationModal()">×</button>
                    </div>

                    <div class="campaign-map-search-host" id="campaignLocationSearchHost"></div>
                    <p class="campaign-map-search-feedback" id="campaignMapFeedback">Cari lokasi atau klik langsung pada peta untuk menentukan area target.</p>

                    <p class="campaign-block__copy">
                        Tentukan lokasi yang Anda inginkan dengan mengetik nama atau alamat lokasi pada kolom pencarian, lalu atur radius pengiriman.
                    </p>

                    <section class="campaign-map-settings">
                        <h3>Pengaturan</h3>
                        <div class="campaign-map-settings__grid">
                            <div class="field-group campaign-field-group--compact">
                                <label for="campaignLocationRadius" class="field-label">Radius (dalam Meter)</label>
                                <input id="campaignLocationRadius" type="number" class="text-input" min="0" step="100" value="300">
                            </div>
                        </div>
                    </section>

                    <div class="campaign-map-actions">
                        <button type="button" class="campaign-modal-btn campaign-modal-btn--primary" id="campaignApplyLocation" onclick="window.applyCampaignLocationSelection && window.applyCampaignLocationSelection()">Gunakan Lokasi</button>
                        <button type="button" class="campaign-map-cancel" data-location-close onclick="window.closeCampaignLocationModal && window.closeCampaignLocationModal()">Batal</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script src="{{ asset('js/indonesia-map-geocoder.js') }}?v={{ filemtime(public_path('js/indonesia-map-geocoder.js')) }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const templateSelect = document.getElementById('waLbaTemplate');
            const stepRoot = document.getElementById('waLbaStep');
            const nextButton = document.getElementById('waLbaNextButton');
            const stepPanels = document.querySelectorAll('[data-wa-lba-panel]');
            const stepItems = document.querySelectorAll('[data-wa-lba-step]');
            const stepTwoBack = document.getElementById('waLbaStepTwoBack');
            const stepTwoNext = document.getElementById('waLbaStepTwoNext');
            const stepThreeBack = document.getElementById('waLbaStepThreeBack');
            const stepThreeNext = document.getElementById('waLbaStepThreeNext');
            const stepFourBack = document.getElementById('waLbaStepFourBack');
            const templateDetails = document.getElementById('waLbaTemplateDetails');
            const preview = document.getElementById('waLbaPreview');
            const previewPhone = document.getElementById('waLbaPreviewPhone');
            const priceNote = document.getElementById('waLbaPriceNote');
            const accountName = document.getElementById('waLbaAccountName');
            const templateKind = document.getElementById('waLbaTemplateKind');
            const channelKind = document.getElementById('waLbaChannelKind');
            const bubbleHeadline = document.getElementById('waLbaBubbleHeadline');
            const bubbleMessage = document.getElementById('waLbaBubbleMessage');
            const cardsBlock = document.getElementById('waLbaCardsBlock');
            const cardsAccordion = document.getElementById('waLbaCardsAccordion');
            const previewName = document.getElementById('waLbaPreviewName');
            const previewSubtitle = document.getElementById('waLbaPreviewSubtitle');
            const previewHeadline = document.getElementById('waLbaPreviewHeadline');
            const previewMessage = document.getElementById('waLbaPreviewMessage');
            const previewAsset = document.getElementById('waLbaPreviewAsset');
            const previewCardTitle = document.getElementById('waLbaPreviewCardTitle');
            const previewCardCaption = document.getElementById('waLbaPreviewCardCaption');
            const previewCta = document.getElementById('waLbaPreviewCta');
            const templates = @json($waTemplates->values());
            const locationModal = document.getElementById('campaignMapModal');
            const openLocationModalButton = document.getElementById('campaignOpenLocationModal');
            const locationCloseButtons = document.querySelectorAll('[data-location-close]');
            const locationCanvas = document.getElementById('campaignMapCanvas');
            const locationSearchHost = document.getElementById('campaignLocationSearchHost');
            const mapFeedback = document.getElementById('campaignMapFeedback');
            const locationRadiusInput = document.getElementById('campaignLocationRadius');
            const audienceEstimate = document.getElementById('campaignAudienceEstimate');
            const genderSelect = document.getElementById('campaign_gender');
            const ageSelect = document.getElementById('campaign_age');
            const religionSelect = document.getElementById('campaign_religion');
            const scheduleList = document.getElementById('waLbaScheduleList');
            const recipientCountInput = document.getElementById('waLbaRecipientCount');
            const termsCheck = document.getElementById('waLbaTermsCheck');
            const testRecipient = document.getElementById('waLbaTestRecipient');
            const estimateRange = document.getElementById('waLbaRecipientEstimateRange');
            const reviewSchedules = document.getElementById('waLbaReviewSchedules');
            const locationState = {
                name: 'Bundaran HI, Jakarta',
                audience: 2400,
                lat: -6.1944491,
                lng: 106.8229198,
            };
            let liveMap = null;
            let liveMarker = null;
            let liveCircle = null;
            let geocoderControl = null;
            let scheduleIndex = 1;

            const syncStepState = (stepNumber) => {
                stepPanels.forEach((panel) => {
                    panel.hidden = Number(panel.dataset.waLbaPanel) !== stepNumber;
                });

                if (stepTwoBack) {
                    stepTwoBack.hidden = stepNumber !== 2;
                }
                if (stepThreeBack) {
                    stepThreeBack.hidden = stepNumber !== 3;
                }
                if (stepFourBack) {
                    stepFourBack.hidden = stepNumber !== 4;
                }

                stepItems.forEach((item, index) => {
                    const current = index + 1;
                    item.classList.toggle('campaign-step--active', current === stepNumber);
                    item.classList.toggle('campaign-step--completed', current < stepNumber);
                });
            };

            const getSelectedCount = (select) => Array.from(select?.selectedOptions || []).length;

            const syncStepTwoState = () => {
                const hasLocation = openLocationModalButton?.classList.contains('campaign-outline-button--applied');
                const profileWeight = getSelectedCount(genderSelect) + getSelectedCount(ageSelect) + getSelectedCount(religionSelect);
                const radius = Number(locationRadiusInput?.value || 0);
                const estimate = hasLocation ? Math.max(120, Math.round((radius || 300) * 4.6) - profileWeight * 90) : 0;

                if (audienceEstimate) {
                    audienceEstimate.textContent = new Intl.NumberFormat('id-ID').format(estimate);
                }

                if (stepTwoNext) {
                    stepTwoNext.disabled = !hasLocation;
                }
            };

            const buildHourOptions = (selectedValue) => {
                const options = [];
                const selectedHour = Math.min(20, Math.max(8, Number(selectedValue || 8)));
                const normalizedSelectedValue = String(selectedHour).padStart(2, '0');

                for (let hour = 8; hour <= 20; hour += 1) {
                    const value = String(hour).padStart(2, '0');
                    options.push(`<option value="${value}"${value === normalizedSelectedValue ? ' selected' : ''}>${value}</option>`);
                }
                return options.join('');
            };

            const buildMinuteOptions = (selectedValue) => {
                return ['00', '30'].map((value) => `<option value="${value}"${value === selectedValue ? ' selected' : ''}>${value}</option>`).join('');
            };

            const formatScheduleDate = (value) => {
                if (!value) {
                    return '--/--/--';
                }

                const [year, month, day] = value.split('-');
                return `${day}/${month}/${year.slice(-2)}`;
            };

            const toDateInputValue = (date) => {
                const pad = (value) => String(value).padStart(2, '0');
                return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
            };

            const todayDateValue = () => toDateInputValue(new Date());

            const tomorrowDateValue = () => {
                const date = new Date();
                date.setDate(date.getDate() + 1);
                return toDateInputValue(date);
            };

            const defaultDeliveryTime = () => {
                const now = new Date();
                let start = new Date(now);
                start.setSeconds(0, 0);

                let end = new Date(start);
                end.setHours(end.getHours() + 4);

                const isAllowedTime = (date) => {
                    const minutes = date.getHours() * 60 + date.getMinutes();
                    return minutes >= (8 * 60) && minutes <= ((18 * 60) + 59);
                };

                if (!isAllowedTime(start) || !isAllowedTime(end)) {
                    start = new Date(now);
                    start.setHours(8, 0, 0, 0);
                    end = new Date(start);
                    end.setHours(12, 0, 0, 0);
                }

                const timeValue = (date) => `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;

                return {
                    startTime: timeValue(start),
                    endTime: timeValue(end),
                };
            };

            const syncSchedulePreview = (scheduleId) => {
                const dateStart = document.querySelector(`[data-schedule-date-start="${scheduleId}"]`);
                const dateEnd = document.querySelector(`[data-schedule-date-end="${scheduleId}"]`);
                const timeStartHour = document.querySelector(`[data-schedule-time-start-hour="${scheduleId}"]`);
                const timeStartMinute = document.querySelector(`[data-schedule-time-start-minute="${scheduleId}"]`);
                const timeEndHour = document.querySelector(`[data-schedule-time-end-hour="${scheduleId}"]`);
                const timeEndMinute = document.querySelector(`[data-schedule-time-end-minute="${scheduleId}"]`);
                const datePreview = document.getElementById(`waLbaScheduleDatePreview${scheduleId}`);
                const timePreview = document.getElementById(`waLbaScheduleTimePreview${scheduleId}`);

                [dateStart, dateEnd].forEach((field) => {
                    if (field?.min && field.value && field.value < field.min) {
                        field.value = field.min;
                    }
                });

                if (datePreview && dateStart && dateEnd) {
                    datePreview.textContent = `${formatScheduleDate(dateStart.value)} - ${formatScheduleDate(dateEnd.value)}`;
                }

                [[timeStartHour, timeStartMinute], [timeEndHour, timeEndMinute]].forEach(([hour, minute]) => {
                    if (hour?.value === '20') minute.value = '00';
                });

                if (timePreview && timeStartHour && timeStartMinute && timeEndHour && timeEndMinute) {
                    timePreview.textContent = `${timeStartHour.value}:${timeStartMinute.value}-${timeEndHour.value}:${timeEndMinute.value} WIB`;
                }
            };

            const initScheduleTimeSelects = (scheduleId, startValue = null, endValue = null) => {
                const defaults = defaultDeliveryTime();
                startValue = startValue || defaults.startTime;
                endValue = endValue || defaults.endTime;
                const [startHour, startMinute] = startValue.split(':');
                const [endHour, endMinute] = endValue.split(':');
                const timeStartHour = document.querySelector(`[data-schedule-time-start-hour="${scheduleId}"]`);
                const timeStartMinute = document.querySelector(`[data-schedule-time-start-minute="${scheduleId}"]`);
                const timeEndHour = document.querySelector(`[data-schedule-time-end-hour="${scheduleId}"]`);
                const timeEndMinute = document.querySelector(`[data-schedule-time-end-minute="${scheduleId}"]`);

                if (timeStartHour) {
                    timeStartHour.innerHTML = buildHourOptions(startHour);
                }
                if (timeStartMinute) {
                    timeStartMinute.innerHTML = buildMinuteOptions(startMinute);
                }
                if (timeEndHour) {
                    timeEndHour.innerHTML = buildHourOptions(endHour);
                }
                if (timeEndMinute) {
                    timeEndMinute.innerHTML = buildMinuteOptions(endMinute);
                }
            };

            const toggleTimePicker = (scheduleId, forceState = null) => {
                document.querySelectorAll('[data-time-picker]').forEach((picker) => {
                    const isTarget = picker.dataset.timePicker === String(scheduleId);
                    const panel = picker.querySelector('[data-time-panel]');
                    const nextState = isTarget ? (forceState === null ? panel.hidden : forceState) : false;
                    picker.classList.toggle('campaign-time-picker--open', !!nextState);
                    if (panel) {
                        panel.hidden = !nextState;
                    }
                });
            };

            const isScheduleTimeOrderValid = (scheduleId) => {
                const startHour = Number(document.querySelector(`[data-schedule-time-start-hour="${scheduleId}"]`)?.value || NaN);
                const startMinute = Number(document.querySelector(`[data-schedule-time-start-minute="${scheduleId}"]`)?.value || NaN);
                const endHour = Number(document.querySelector(`[data-schedule-time-end-hour="${scheduleId}"]`)?.value || NaN);
                const endMinute = Number(document.querySelector(`[data-schedule-time-end-minute="${scheduleId}"]`)?.value || NaN);

                if ([startHour, startMinute, endHour, endMinute].some(Number.isNaN)) {
                    return false;
                }

                return (startHour * 60 + startMinute) < (endHour * 60 + endMinute);
            };

            const syncStepThreeState = () => {
                const recipientCount = Number(recipientCountInput?.value || 0);
                const audienceBase = Number((audienceEstimate?.textContent || '0').replace(/\./g, '')) || 0;
                const minimum = Math.max(5, audienceBase - Math.round(audienceBase * 0.08));
                const maximum = Math.max(minimum + 1200, audienceBase + Math.round(audienceBase * 0.18));

                if (estimateRange) {
                    estimateRange.textContent = `${new Intl.NumberFormat('id-ID').format(minimum)}-${new Intl.NumberFormat('id-ID').format(maximum)}`;
                }

                let isScheduleValid = true;
                document.querySelectorAll('[data-schedule-item]').forEach((item) => {
                    const scheduleId = item.dataset.scheduleItem;
                    const alert = item.querySelector('.campaign-step-two__alert');
                    const isTimeOrderValid = isScheduleTimeOrderValid(scheduleId);

                    if (alert) {
                        alert.textContent = isTimeOrderValid
                            ? 'Pembuatan iklan tidak boleh sebelum waktu sekarang.'
                            : 'Jam mulai harus lebih kecil dari jam berakhir.';
                        alert.hidden = isTimeOrderValid;
                    }

                    isScheduleValid = isScheduleValid && isTimeOrderValid;
                });

                if (stepThreeNext) {
                    stepThreeNext.disabled = !(recipientCount >= 5 && termsCheck?.checked && testRecipient?.value && isScheduleValid);
                }
            };

            const getSelectedTexts = (selectId) => {
                const select = document.getElementById(selectId);

                if (!select) {
                    return 'Semua';
                }

                const values = Array.from(select.options)
                    .filter((option) => option.selected)
                    .map((option) => option.text.trim());

                return values.length ? values.join(', ') : 'Semua';
            };

            const formatCurrency = (value) => `Rp ${new Intl.NumberFormat('id-ID').format(Number(value || 0))}`;

            const collapseReviewCards = () => {
                document.querySelectorAll('[data-wa-lba-review-card]').forEach((card) => {
                    card.classList.remove('campaign-review-card--open');
                });

                document.querySelectorAll('[data-wa-lba-review-body]').forEach((body) => {
                    body.hidden = true;
                });

                document.querySelectorAll('[data-wa-lba-review-toggle]').forEach((toggle) => {
                    toggle.textContent = 'Tampilkan';
                });
            };

            const syncReviewSummary = () => {
                const selected = templates.find((template) => String(template.id) === String(templateSelect?.value));
                const recipientCount = Number(recipientCountInput?.value || 0);
                const radius = Number(locationRadiusInput?.value || 0);
                const totalCost = recipientCount * 1100;

                const setText = (id, value) => {
                    const target = document.getElementById(id);
                    if (target) {
                        target.textContent = value;
                    }
                };

                setText('waLbaReviewTitle', selected?.name || 'Tanpa Judul');
                setText('waLbaReviewTemplateName', selected?.name || '-');
                setText('waLbaReviewSender', selected?.account_name || '-');
                setText('waLbaReviewMessage', `${selected?.bubble_headline || ''}\n\n${selected?.bubble_message || '-'}`);
                setText('waLbaReviewLocation', locationState.name || '-');
                setText('waLbaReviewRadius', `${new Intl.NumberFormat('id-ID').format(radius)} meter`);
                setText('waLbaReviewAudience', audienceEstimate?.textContent || '0');
                setText('waLbaReviewGender', getSelectedTexts('campaign_gender'));
                setText('waLbaReviewAge', getSelectedTexts('campaign_age'));
                setText('waLbaReviewReligion', getSelectedTexts('campaign_religion'));
                setText('waLbaReviewDeliveryMethod', document.getElementById('waLbaDeliveryMethod')?.selectedOptions?.[0]?.text || 'Pengiriman Secepatnya');
                setText('waLbaReviewTestRecipient', testRecipient?.value || 'Belum dipilih');
                setText('waLbaReviewRecipientCount', `${new Intl.NumberFormat('id-ID').format(recipientCount)} Pesan`);
                setText('waLbaReviewPotentialRange', audienceEstimate?.textContent || '0');
                setText('waLbaReviewGrandTotal', formatCurrency(totalCost));
                setText('waLbaReviewPayment', formatCurrency(totalCost));

                if (reviewSchedules) {
                    const schedules = Array.from(scheduleList?.querySelectorAll('[data-schedule-item]') || []).map((item, index) => {
                        const scheduleId = item.dataset.scheduleItem;
                        const date = document.getElementById(`waLbaScheduleDatePreview${scheduleId}`)?.textContent || '-';
                        const time = document.getElementById(`waLbaScheduleTimePreview${scheduleId}`)?.textContent || '-';

                        return `
                            <div class="campaign-review-schedule__item">
                                <span class="campaign-review-grid__label">Jadwal Pengiriman ${index + 1}</span>
                                <strong>${date}</strong>
                                <strong>${time}</strong>
                            </div>
                        `;
                    });

                    reviewSchedules.innerHTML = schedules.join('');
                }
            };

            const initCampaignMultiselects = () => {
                document.querySelectorAll('.campaign-search-multiselect').forEach((select) => {
                    if (select.dataset.enhanced === 'true') {
                        return;
                    }

                    const wrapper = document.createElement('div');
                    wrapper.className = 'campaign-multiselect';
                    wrapper.innerHTML = `
                        <div class="campaign-multiselect__control" tabindex="0">
                            <div class="campaign-multiselect__chips"></div>
                            <input type="text" class="campaign-multiselect__search" placeholder="${select.dataset.placeholder || 'Cari data'}">
                        </div>
                        <div class="campaign-multiselect__dropdown">
                            <div class="campaign-multiselect__empty" hidden>Data tidak ditemukan</div>
                            <div class="campaign-multiselect__options"></div>
                        </div>
                    `;

                    select.dataset.enhanced = 'true';
                    select.classList.add('campaign-multiselect__native');
                    select.after(wrapper);

                    const control = wrapper.querySelector('.campaign-multiselect__control');
                    const chips = wrapper.querySelector('.campaign-multiselect__chips');
                    const search = wrapper.querySelector('.campaign-multiselect__search');
                    const optionsWrap = wrapper.querySelector('.campaign-multiselect__options');
                    const empty = wrapper.querySelector('.campaign-multiselect__empty');

                    const render = (filter = '') => {
                        const keyword = filter.trim().toLowerCase();
                        const options = Array.from(select.options);
                        const selected = options.filter((option) => option.selected);
                        const filtered = options.filter((option) => option.text.toLowerCase().includes(keyword));

                        chips.innerHTML = selected.map((option) => `
                            <button type="button" class="campaign-multiselect__chip" data-chip-value="${option.value}">
                                <span class="campaign-multiselect__chip-remove">×</span>
                                <span>${option.text}</span>
                            </button>
                        `).join('');

                        optionsWrap.innerHTML = filtered.map((option) => `
                            <button type="button" class="campaign-multiselect__option${option.selected ? ' campaign-multiselect__option--selected' : ''}" data-option-value="${option.value}">
                                <span>${option.text}</span>
                                ${option.selected ? '<span class="campaign-multiselect__check">✓</span>' : ''}
                            </button>
                        `).join('');

                        empty.hidden = filtered.length > 0;
                    };

                    const toggleDropdown = (isOpen) => {
                        wrapper.classList.toggle('campaign-multiselect--open', isOpen);
                        if (isOpen) {
                            search.focus();
                        }
                    };

                    render();

                    control.addEventListener('click', () => toggleDropdown(true));
                    search.addEventListener('input', () => render(search.value));

                    wrapper.addEventListener('click', (event) => {
                        const optionButton = event.target.closest('[data-option-value]');
                        const chipButton = event.target.closest('[data-chip-value]');

                        if (optionButton) {
                            const option = Array.from(select.options).find((item) => item.value === optionButton.dataset.optionValue);
                            if (option) {
                                option.selected = !option.selected;
                                select.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                            render(search.value);
                        }

                        if (chipButton) {
                            const option = Array.from(select.options).find((item) => item.value === chipButton.dataset.chipValue);
                            if (option) {
                                option.selected = false;
                                select.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                            render(search.value);
                        }
                    });

                    select.addEventListener('change', () => {
                        render(search.value);
                        syncStepTwoState();
                    });

                    document.addEventListener('click', (event) => {
                        if (!wrapper.contains(event.target)) {
                            toggleDropdown(false);
                        }
                    });
                });
            };

            const addScheduleItem = () => {
                if (!scheduleList) {
                    return;
                }

                const defaults = defaultDeliveryTime();
                scheduleIndex += 1;
                const item = document.createElement('article');
                item.className = 'campaign-schedule-card';
                item.dataset.scheduleItem = String(scheduleIndex);
                item.innerHTML = `
                    <div class="campaign-schedule-card__header">
                        <span>Jadwal Pengiriman ${scheduleIndex}</span>
                        <button type="button" class="campaign-schedule-card__remove" data-remove-schedule>Hapus</button>
                    </div>
                    <div class="campaign-schedule-card__grid">
                        <div class="field-group campaign-field-group--compact">
                            <label class="field-label">Tanggal Kirim</label>
                            <div class="campaign-range-field">
                                <input id="waLbaScheduleDate${scheduleIndex}Start" type="date" class="text-input" value="${tomorrowDateValue()}" min="${todayDateValue()}" data-schedule-date-start="${scheduleIndex}">
                                <span class="campaign-range-field__separator">-</span>
                                <input id="waLbaScheduleDate${scheduleIndex}End" type="date" class="text-input" value="${tomorrowDateValue()}" min="${todayDateValue()}" data-schedule-date-end="${scheduleIndex}">
                            </div>
                            <p class="campaign-range-preview" id="waLbaScheduleDatePreview${scheduleIndex}">${formatScheduleDate(tomorrowDateValue())} - ${formatScheduleDate(tomorrowDateValue())}</p>
                        </div>
                        <div class="field-group campaign-field-group--compact">
                            <label class="field-label">Jam Kirim</label>
                            <div class="campaign-time-picker" data-time-picker="${scheduleIndex}">
                                <button type="button" class="campaign-time-picker__trigger" data-time-trigger="${scheduleIndex}">
                                    <span class="campaign-time-picker__value" id="waLbaScheduleTimePreview${scheduleIndex}">${defaults.startTime}-${defaults.endTime} WIB</span>
                                    <span class="campaign-time-picker__clock">◷</span>
                                </button>
                                <div class="campaign-time-picker__panel" data-time-panel="${scheduleIndex}" hidden>
                                    <div class="campaign-time-picker__columns">
                                        <div class="campaign-time-picker__column">
                                            <span class="campaign-time-picker__label">Jam Mulai</span>
                                            <div class="campaign-time-picker__inputs">
                                                <select id="waLbaScheduleTime${scheduleIndex}StartHour" class="campaign-time-picker__select" data-schedule-time-start-hour="${scheduleIndex}"></select>
                                                <span class="campaign-time-picker__colon">:</span>
                                                <select id="waLbaScheduleTime${scheduleIndex}StartMinute" class="campaign-time-picker__select" data-schedule-time-start-minute="${scheduleIndex}"></select>
                                                <span class="campaign-time-picker__suffix">WIB</span>
                                            </div>
                                        </div>
                                        <div class="campaign-time-picker__column">
                                            <span class="campaign-time-picker__label">Jam Berakhir</span>
                                            <div class="campaign-time-picker__inputs">
                                                <select id="waLbaScheduleTime${scheduleIndex}EndHour" class="campaign-time-picker__select" data-schedule-time-end-hour="${scheduleIndex}"></select>
                                                <span class="campaign-time-picker__colon">:</span>
                                                <select id="waLbaScheduleTime${scheduleIndex}EndMinute" class="campaign-time-picker__select" data-schedule-time-end-minute="${scheduleIndex}"></select>
                                                <span class="campaign-time-picker__suffix">WIB</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="campaign-time-picker__hint">Disarankan durasi pengiriman pesan lebih dari 4 jam agar pengiriman lebih optimal</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="campaign-step-two__alert">Pembuatan iklan tidak boleh sebelum waktu sekarang.</p>
                `;

                scheduleList.appendChild(item);
                initScheduleTimeSelects(scheduleIndex);
                syncSchedulePreview(scheduleIndex);
            };

            window.openCampaignLocationModal = function () {
                if (!locationModal) {
                    return;
                }

                locationModal.hidden = false;
                locationModal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('campaign-map-open');
                setTimeout(() => initLiveMap(), 40);
            };

            window.closeCampaignLocationModal = function () {
                if (!locationModal) {
                    return;
                }

                locationModal.hidden = true;
                locationModal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('campaign-map-open');
            };

            const updateLiveRadius = () => {
                if (liveCircle) {
                    liveCircle.setRadius(Number(locationRadiusInput?.value || 0));
                }
            };

            const updateLocationSummary = () => {
                const button = document.getElementById('campaignOpenLocationModal');
                const buttonIcon = document.getElementById('campaignLocationButtonIcon');
                const buttonContent = document.getElementById('campaignLocationButtonContent');
                if (!button || !buttonContent) {
                    return;
                }

                button.classList.add('campaign-outline-button--applied', 'campaign-outline-button--location-summary');
                if (buttonIcon) {
                    buttonIcon.textContent = '@';
                }

                buttonContent.innerHTML = `
                    <span class="campaign-location-button__title">${locationState.name || 'Lokasi dipilih'}</span>
                    <span class="campaign-location-button__meta">Longitude ${Number(locationState.lng).toFixed(6)} | Latitude ${Number(locationState.lat).toFixed(6)}</span>
                    <span class="campaign-location-button__meta">Radius ${new Intl.NumberFormat('id-ID').format(Number(locationRadiusInput?.value || 0))} meter</span>
                `;
            };

            window.applyCampaignLocationSelection = function () {
                updateLocationSummary();
                syncStepTwoState();
                window.closeCampaignLocationModal && window.closeCampaignLocationModal();
            };

            const initLiveMap = () => {
                if (!locationCanvas) {
                    return;
                }

                if (typeof window.L === 'undefined') {
                    locationCanvas.innerHTML = '<div class="campaign-map-fallback">Peta tidak dapat dimuat saat ini. Periksa koneksi internet atau cache browser, lalu coba buka lagi popup lokasi.</div>';
                    return;
                }

                if (!liveMap) {
                    liveMap = L.map(locationCanvas, { zoomControl: true }).setView([locationState.lat, locationState.lng], 14);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(liveMap);

                    liveMarker = L.marker([locationState.lat, locationState.lng]).addTo(liveMap);
                    liveCircle = L.circle([locationState.lat, locationState.lng], {
                        radius: Number(locationRadiusInput?.value || 300),
                        color: '#ff3b4d',
                        weight: 2,
                        fillColor: '#ff6b72',
                        fillOpacity: 0.25,
                    }).addTo(liveMap);

                    liveMap.on('click', (event) => {
                        locationState.lat = event.latlng.lat;
                        locationState.lng = event.latlng.lng;
                        locationState.name = `Titik ${event.latlng.lat.toFixed(4)}, ${event.latlng.lng.toFixed(4)}`;
                        mapFeedback.textContent = `Lokasi dipilih di ${locationState.name}`;
                        liveMarker.setLatLng(event.latlng);
                        liveCircle.setLatLng(event.latlng);
                    });

                    if (locationSearchHost && typeof L.Control?.Geocoder !== 'undefined') {
                        geocoderControl = L.Control.geocoder({
                            geocoder: window.createIndonesiaGeocoder(L),
                            defaultMarkGeocode: false,
                            placeholder: 'Cari jalan atau alamat di Indonesia',
                            queryMinLength: 3,
                            suggestMinLength: 3,
                            suggestTimeout: 250,
                        })
                        .on('markgeocode', (event) => {
                            const center = event.geocode.center;
                            locationState.lat = center.lat;
                            locationState.lng = center.lng;
                            locationState.name = event.geocode.name;
                            mapFeedback.textContent = `Lokasi ditemukan: ${event.geocode.name}`;
                            liveMap.setView(center, 15);
                            liveMarker.setLatLng(center);
                            liveCircle.setLatLng(center);
                        });

                        const geocoderContainer = geocoderControl.onAdd(liveMap);
                        locationSearchHost.innerHTML = '';
                        locationSearchHost.appendChild(geocoderContainer);
                    }
                }

                setTimeout(() => liveMap.invalidateSize(), 120);
            };

            const renderCards = (cards) => {
                if (!cardsAccordion || !cardsBlock) {
                    return;
                }

                cardsAccordion.innerHTML = '';
                const hasCards = Array.isArray(cards) && cards.length > 0;
                cardsBlock.hidden = !hasCards;

                if (!hasCards) {
                    return;
                }

                cards.forEach((card, index) => {
                    const item = document.createElement('article');
                    item.className = 'campaign-wa-lba-template__card-item';
                    item.innerHTML = `
                        <button type="button" class="campaign-wa-lba-template__card-toggle" data-wa-lba-card-toggle>
                            <span>${card.title || `Card ${index + 1}`}</span>
                            <span>⌄</span>
                        </button>
                        <div class="campaign-wa-lba-template__card-panel" hidden>
                            <p>${card.caption || 'Detail card template.'}</p>
                        </div>
                    `;
                    cardsAccordion.appendChild(item);
                });
            };

            const syncTemplateDisplay = () => {
                if (!templateSelect || !nextButton) {
                    return;
                }

                const selected = templates.find((template) => String(template.id) === String(templateSelect.value));
                const hasValue = Boolean(selected);
                nextButton.disabled = !hasValue;
                stepRoot?.classList.toggle('campaign-wa-lba-step--selected', hasValue);
                templateDetails.hidden = !hasValue;
                preview.hidden = !hasValue;
                previewPhone?.classList.remove('campaign-wa-lba-preview__phone--basic', 'campaign-wa-lba-preview__phone--carousel');

                if (!selected) {
                    cardsAccordion.innerHTML = '';
                    return;
                }

                priceNote.textContent = selected.price_note || '';
                accountName.textContent = selected.account_name || '-';
                templateKind.textContent = selected.template_kind || '-';
                channelKind.textContent = selected.channel_kind || '-';
                bubbleHeadline.textContent = selected.bubble_headline || '-';
                bubbleMessage.textContent = selected.bubble_message || '-';
                previewName.textContent = selected.account_name || selected.preview_name || 'Name';
                previewSubtitle.textContent = selected.channel_kind === 'Carousel' ? 'Carousel Business Account' : 'Business Account';
                previewHeadline.textContent = selected.bubble_headline || '-';
                previewMessage.textContent = selected.bubble_message || '-';
                previewAsset.src = selected.asset_url || @json(asset('assets/shopads-icon.svg'));
                previewCardTitle.textContent = (selected.cards && selected.cards[0] && selected.cards[0].title) || 'Template Carousel';
                previewCardCaption.textContent = (selected.cards && selected.cards[0] && selected.cards[0].caption) || selected.bubble_message || 'Detail card pertama akan tampil di sini.';
                previewCta.textContent = selected.cta || 'Coba Sekarang';
                previewPhone?.classList.add(selected.cards && selected.cards.length ? 'campaign-wa-lba-preview__phone--carousel' : 'campaign-wa-lba-preview__phone--basic');
                renderCards(selected.cards || []);
            };

            templateSelect?.addEventListener('change', syncTemplateDisplay);
            nextButton?.addEventListener('click', () => {
                if (!nextButton.disabled) {
                    syncStepState(2);
                }
            });
            stepTwoBack?.addEventListener('click', () => syncStepState(1));
            stepTwoNext?.addEventListener('click', () => {
                if (!stepTwoNext.disabled) {
                    syncStepState(3);
                    syncStepThreeState();
                }
            });
            stepThreeBack?.addEventListener('click', () => syncStepState(2));
            stepThreeNext?.addEventListener('click', () => {
                if (!stepThreeNext.disabled) {
                    syncReviewSummary();
                    collapseReviewCards();
                    syncStepState(4);
                }
            });
            stepFourBack?.addEventListener('click', () => syncStepState(3));
            cardsAccordion?.addEventListener('click', (event) => {
                const toggle = event.target.closest('[data-wa-lba-card-toggle]');
                if (!toggle) {
                    return;
                }

                const item = toggle.closest('.campaign-wa-lba-template__card-item');
                const panel = item?.querySelector('.campaign-wa-lba-template__card-panel');
                const isOpen = panel && !panel.hidden;

                if (panel) {
                    panel.hidden = isOpen;
                }
            });
            document.addEventListener('click', (event) => {
                const toggleButton = event.target.closest('[data-wa-lba-review-toggle]');
                if (toggleButton) {
                    const cardKey = toggleButton.dataset.waLbaReviewToggle;
                    const card = document.querySelector(`[data-wa-lba-review-card="${cardKey}"]`);
                    const body = document.querySelector(`[data-wa-lba-review-body="${cardKey}"]`);
                    const isOpen = card?.classList.contains('campaign-review-card--open');

                    card?.classList.toggle('campaign-review-card--open', !isOpen);
                    if (body) {
                        body.hidden = isOpen;
                    }
                    toggleButton.textContent = isOpen ? 'Tampilkan' : 'Sembunyikan';
                }

                const editButton = event.target.closest('[data-wa-lba-review-edit]');
                if (editButton) {
                    syncStepState(Number(editButton.dataset.waLbaReviewEdit));
                }
            });
            locationCloseButtons.forEach((button) => {
                button.addEventListener('click', () => window.closeCampaignLocationModal && window.closeCampaignLocationModal());
            });
            locationRadiusInput?.addEventListener('input', () => {
                updateLiveRadius();
                syncStepTwoState();
            });
            scheduleList?.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-time-trigger]');
                if (trigger) {
                    toggleTimePicker(trigger.dataset.timeTrigger);
                    return;
                }

                const removeButton = event.target.closest('[data-remove-schedule]');
                if (removeButton) {
                    const item = removeButton.closest('[data-schedule-item]');
                    item?.remove();
                    syncStepThreeState();
                }
            });
            scheduleList?.addEventListener('input', (event) => {
                const field = event.target.closest('[data-schedule-date-start], [data-schedule-date-end], [data-schedule-time-start-hour], [data-schedule-time-start-minute], [data-schedule-time-end-hour], [data-schedule-time-end-minute]');
                if (!field) {
                    return;
                }

                const scheduleId =
                    field.dataset.scheduleDateStart ||
                    field.dataset.scheduleDateEnd ||
                    field.dataset.scheduleTimeStartHour ||
                    field.dataset.scheduleTimeStartMinute ||
                    field.dataset.scheduleTimeEndHour ||
                    field.dataset.scheduleTimeEndMinute;

                if (scheduleId) {
                    syncSchedulePreview(scheduleId);
                    syncStepThreeState();
                }
            });
            scheduleList?.addEventListener('change', (event) => {
                const field = event.target.closest('[data-schedule-date-start], [data-schedule-date-end], [data-schedule-time-start-hour], [data-schedule-time-start-minute], [data-schedule-time-end-hour], [data-schedule-time-end-minute]');
                if (!field) {
                    return;
                }

                const scheduleId =
                    field.dataset.scheduleDateStart ||
                    field.dataset.scheduleDateEnd ||
                    field.dataset.scheduleTimeStartHour ||
                    field.dataset.scheduleTimeStartMinute ||
                    field.dataset.scheduleTimeEndHour ||
                    field.dataset.scheduleTimeEndMinute;

                if (scheduleId) {
                    syncSchedulePreview(scheduleId);
                    syncStepThreeState();
                }
            });
            recipientCountInput?.addEventListener('input', syncStepThreeState);
            termsCheck?.addEventListener('change', syncStepThreeState);
            testRecipient?.addEventListener('change', syncStepThreeState);
            document.addEventListener('click', (event) => {
                if (!event.target.closest('[data-time-picker]')) {
                    document.querySelectorAll('[data-time-picker]').forEach((picker) => {
                        picker.classList.remove('campaign-time-picker--open');
                        const panel = picker.querySelector('[data-time-panel]');
                        if (panel) {
                            panel.hidden = true;
                        }
                    });
                }
            });
            initCampaignMultiselects();
            initScheduleTimeSelects(1);
            syncSchedulePreview(1);
            syncTemplateDisplay();
            syncStepState(1);
            syncStepTwoState();
            syncStepThreeState();
        });
    </script>
@endpush
