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
    $autoCampaignName = 'gotoads_waba_lba_' . now()->format('YmdHis');
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
    @include('partials.waba-template-suggestion-modal', ['flowLabel' => 'WA Business LBA'])

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
        <p>Pelajari cara membuat iklan Anda agar lebih menarik. <a href="#">Pelajari Selengkapnya.</a></p>
    </section> --}}

    <section class="campaign-compose-card campaign-compose-card--single">
        <div class="campaign-compose-card__header campaign-compose-card__header--single">
            <div>
                <p class="campaign-section-kicker" id="waLbaSectionKicker">Step 01</p>
                <h1 id="waLbaSectionHeading">Template Pesan</h1>
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
                    @if ($waTemplates->isEmpty())
                        <div class="campaign-template-empty-state">
                            <strong>Belum ada template pesan.</strong>
                            <p>Buat template pesan terlebih dahulu agar bisa dipilih untuk iklan WA Business LBA.</p>
                            <a href="{{ route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'campaign-template', 'view' => 'create']) }}" class="campaign-inline-link campaign-inline-link--fit">
                                Buat Template Pesan
                            </a>
                        </div>
                    @endif

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
                                    <strong id="waLbaPreviewName"></strong>
                                    <span id="waLbaPreviewSubtitle"></span>
                                </div>
                            </div>
                            <span class="campaign-wa-lba-preview__menu">⋮</span>
                        </div>

                        <div class="campaign-wa-lba-preview__chat">
                            <div class="campaign-wa-lba-preview__bubble" id="waLbaPreviewBubble">
                                <strong id="waLbaPreviewHeadline"></strong>
                                <p id="waLbaPreviewMessage"></p>
                            </div>

                            <div class="campaign-wa-lba-preview__carousel" id="waLbaPreviewCarousel" hidden>
                                <img src="" alt="Preview template" id="waLbaPreviewAsset" hidden>
                                <div class="campaign-wa-lba-preview__carousel-copy">
                                    <span class="campaign-wa-lba-preview__brand"></span>
                                    <strong id="waLbaPreviewCardTitle"></strong>
                                    <p id="waLbaPreviewCardCaption"></p>
                                </div>
                            </div>

                            <div class="campaign-wa-lba-preview__action" id="waLbaPreviewCta" hidden></div>
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

                <div class="campaign-step-two__section" hidden aria-hidden="true" style="display: none">
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
                            <p class="campaign-step-two__alert" data-wa-lba-recipient-count-alert>Jumlah penerima minimal 5 perhari</p>
                            <p class="campaign-block__copy">Berdasarkan policy Meta, batas maksimum kuota pengiriman Anda dalam satu hari sebesar 100.000 pesan.</p>
                        </div>
                        <span id="waLbaRecipientEstimateRange" hidden>0-0</span>
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
                                            <span class="campaign-time-picker__value" id="waLbaScheduleTimePreview1">09:30-13:30 WIB</span>
                                            <span class="campaign-time-picker__clock">i</span>
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
                            <p class="campaign-step-two__alert" data-wa-lba-schedule-duration-alert>Atur jadwal minimal 4 jam/hari untuk memastikan target anda tercapai minimal</p>
                        </article>
                    </div>
                    <div class="campaign-schedule-actions">
                        <div class="campaign-schedule-divider"></div>
                        <button type="button" class="campaign-inline-action" id="waLbaAddSchedule">
                            <span class="campaign-inline-action__icon">+</span>
                            <span>Tambah Jadwal Pengirim</span>
                        </button>
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
                    <h2 class="campaign-block__title">Tentukan Nomor Penerima Tes Iklan</h2>
                    <div class="campaign-test-recipient" id="waLbaTestRecipientBuilder">
                        <input type="hidden" id="waLbaTestRecipient" value="">
                        <fieldset class="campaign-test-recipient__box">
                            <legend>Nomor Penerima Tes Iklan</legend>
                            <div class="campaign-test-recipient__entry">
                                <span class="campaign-test-recipient__prefix">+62</span>
                                <input id="waLbaTestRecipientInput" type="text" inputmode="numeric" class="campaign-test-recipient__input" placeholder="Nomor Penerima Tes Iklan">
                                <button type="button" class="campaign-test-recipient__add" id="waLbaTestRecipientAdd">Tambahkan</button>
                            </div>
                            <div class="campaign-test-recipient__list" id="waLbaTestRecipientList"></div>
                        </fieldset>
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
                            Saya menyetujui <a href="{{ route('campaign.terms') }}" data-campaign-terms-trigger>Syarat dan Ketentuan</a> yang berlaku di website Telkomsel MyAds. Atas setiap pesan iklan yang dibuat oleh pengguna menggunakan produk dan/atau layanan melalui portal myAds, pengguna dilarang untuk mempergunakan kata-kata, komentar, gambar atau konten apapun yang mengandung unsur SARA atau diskriminasi terhadap pihak manapun, bersifat vulgar dan ancaman, atau hal-hal lain yang dapat dianggap tidak sesuai dengan nilai dan norma sosial.
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
                                <span class="campaign-review-card__icon campaign-review-card__icon--content" aria-hidden="true"></span>
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
                                <span class="campaign-review-card__icon campaign-review-card__icon--recipient" aria-hidden="true"></span>
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
                                <span class="campaign-review-card__icon campaign-review-card__icon--delivery" aria-hidden="true"></span>
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
                                <div class="campaign-cost-card__row">
                                    <span>Subtotal</span>
                                    <strong id="waLbaReviewSubtotal">-</strong>
                                </div>
                                <div class="campaign-cost-card__row">
                                    <span>PPN (11%)</span>
                                    <strong id="waLbaReviewTax">-</strong>
                                </div>
                            </div>
                            <div class="campaign-cost-card__section">
                                <div class="campaign-cost-card__row campaign-cost-card__row--total">
                                    <span>Grand Total</span>
                                    <strong id="waLbaReviewGrandTotal">Rp 5.500</strong>
                                </div>
                            </div>
                            <div class="campaign-cost-card__section">
                                <span class="campaign-cost-card__section-title">Pembayaran Anda Menggunakan</span>
                                <div class="campaign-cost-card__row">
                                    <span>Saldo Umum</span>
                                    <strong class="campaign-cost-card__danger" id="waLbaReviewPayment">Rp 5.500</strong>
                                </div>
                            </div>
                            @include('partials.payment-invoice-email', [
                                'inputId' => 'waLbaPaymentInvoiceEmail',
                                'checkboxId' => 'waLbaPaymentInvoiceEmailCheck',
                            ])
                            @if($errors->has('gw'))
                                <div class="alert alert-danger" style="color: #ff1f3d; background: #ffeef0; padding: 12px; border-radius: 8px; margin-bottom: 16px; border: 1px solid #ffaeb5; font-size: 14px; font-weight: bold; text-align: left;">
                                    {{ $errors->first('gw') }}
                                </div>
                            @endif
                            <p class="campaign-payment-error" id="waLbaPaymentError" hidden></p>
                            <p class="campaign-payment-hint" id="waLbaPaymentHint" hidden></p>
                            <button type="button" class="campaign-cost-card__cta" id="waLbaGwSubmitButton">Bayar & Kirim Iklan</button>
                            @include('partials.payment-qris-modal', [
                                'modalId' => 'waLbaPaymentModal',
                                'panelId' => 'waLbaPaymentPanel',
                                'statusId' => 'waLbaPaymentStatus',
                                'transactionId' => 'waLbaPaymentTransaction',
                                'qrId' => 'waLbaPaymentQr',
                                'qrLinkId' => 'waLbaPaymentQrLink',
                                'downloadId' => 'waLbaPaymentQrDownload',
                                'footnote' => 'Apabila terdapat pesan yang tidak terkirim, maka biaya akan dikembalikan (refund) sesuai jumlah pesan yang tidak terkirim. Detil mengenai jumlah iklan terkirim/tidak terkirim akan diinformasikan pada menu laporan setelah iklan Anda tayang.',
                            ])
                            <form id="waLbaGwSubmitForm" method="POST" action="{{ route('campaign.waba-lba.send') }}" hidden>
                                @csrf
                                <input type="hidden" name="payment_transaction_id" id="waLbaPaymentTransactionId" value="">
                                <input type="hidden" name="campaign_name" id="waLbaGwCampaignName" value="{{ $autoCampaignName }}">
                                <input type="hidden" name="waba_user_template_id" id="waLbaGwTemplateId" value="">
                                <input type="hidden" name="recipient_total" id="waLbaGwRecipientTotal" value="">
                                <input type="hidden" name="delivery_start_date" id="waLbaGwDeliveryStartDate" value="">
                                <input type="hidden" name="delivery_end_date" id="waLbaGwDeliveryEndDate" value="">
                                <input type="hidden" name="delivery_start_time" id="waLbaGwDeliveryStartTime" value="">
                                <input type="hidden" name="delivery_end_time" id="waLbaGwDeliveryEndTime" value="">
                                <input type="hidden" name="control_number" id="waLbaGwControlNumber" value="">
                                <input type="hidden" name="location_name" id="waLbaGwLocationName" value="">
                                <input type="hidden" name="location_lat" id="waLbaGwLocationLat" value="">
                                <input type="hidden" name="location_lng" id="waLbaGwLocationLng" value="">
                                <input type="hidden" name="location_radius" id="waLbaGwLocationRadius" value="">
                                <input type="hidden" name="location_type" id="waLbaGwLocationType" value="ALL">
                                <input type="hidden" name="recipient_max" id="waLbaGwRecipientMax" value="">
                                <input type="hidden" name="email_invoice" id="waLbaGwCustomerEmail" value="">
                                <input type="hidden" name="profile_gender" id="waLbaGwProfileGender" value="">
                                <input type="hidden" name="profile_age" id="waLbaGwProfileAge" value="">
                                <input type="hidden" name="profile_religion" id="waLbaGwProfileReligion" value="">
                            </form>
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
    @include('partials.payment-qris-modal-assets')

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
            window.paymentQrisRedirectUrl = "{{ route('campaign.menu', ['channel' => $channel, 'menu' => $menu]) }}";
            const templateSelect = document.getElementById('waLbaTemplate');
            const stepRoot = document.getElementById('waLbaStep');
            const nextButton = document.getElementById('waLbaNextButton');
            const stepPanels = document.querySelectorAll('[data-wa-lba-panel]');
            const stepItems = document.querySelectorAll('[data-wa-lba-step]');
            const sectionKicker = document.getElementById('waLbaSectionKicker');
            const sectionHeading = document.getElementById('waLbaSectionHeading');
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
            const previewBubble = document.getElementById('waLbaPreviewBubble');
            const previewCarousel = document.getElementById('waLbaPreviewCarousel');
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
            const addScheduleButton = document.getElementById('waLbaAddSchedule');
            const recipientCountInput = document.getElementById('waLbaRecipientCount');
            const termsCheck = document.getElementById('waLbaTermsCheck');
            const testRecipient = document.getElementById('waLbaTestRecipient');
            const testRecipientInput = document.getElementById('waLbaTestRecipientInput');
            const testRecipientAdd = document.getElementById('waLbaTestRecipientAdd');
            const testRecipientList = document.getElementById('waLbaTestRecipientList');
            const estimateRange = document.getElementById('waLbaRecipientEstimateRange');
            const reviewSchedules = document.getElementById('waLbaReviewSchedules');
            const waLbaGwSubmitButton = document.getElementById('waLbaGwSubmitButton');
            const waLbaGwSubmitForm = document.getElementById('waLbaGwSubmitForm');
            const waLbaPaymentTransactionId = document.getElementById('waLbaPaymentTransactionId');
            const waLbaPaymentPanel = document.getElementById('waLbaPaymentPanel');
            const waLbaPaymentStatus = document.getElementById('waLbaPaymentStatus');
            const waLbaPaymentTransaction = document.getElementById('waLbaPaymentTransaction');
            const waLbaPaymentQr = document.getElementById('waLbaPaymentQr');
            const waLbaPaymentQrLink = document.getElementById('waLbaPaymentQrLink');
            const waLbaPaymentError = document.getElementById('waLbaPaymentError');
            const waLbaPaymentHint = document.getElementById('waLbaPaymentHint');
            const paymentInitiateUrl = "{{ route('campaign.payment.initiate') }}";
            const paymentStatusUrlTemplate = "{{ route('campaign.payment.status', ['transactionId' => '__TRANSACTION_ID__']) }}";
            const qrisDownloadUrl = "{{ route('campaign.payment.qris-download') }}";
            const csrfToken = "{{ csrf_token() }}";
            const paymentCustomerPhone = '6285129640197';
            const defaultInvoiceEmail = 'myads@telkomsel.com';
            const waLbaPaymentInvoiceEmail = document.getElementById('waLbaPaymentInvoiceEmail');
            const waLbaPaymentInvoiceEmailCheck = document.getElementById('waLbaPaymentInvoiceEmailCheck');
            const waLbaUnitPrice = 1100;
            const defaultMapCenter = {
                lat: -6.1944491,
                lng: 106.8229198,
            };
            const locationState = {
                name: '',
                audience: 0,
                lat: null,
                lng: null,
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

                if (sectionKicker) {
                    sectionKicker.textContent = `Step ${String(stepNumber).padStart(2, '0')}`;
                }

                if (sectionHeading) {
                    sectionHeading.textContent =
                        stepNumber === 2 ? 'Atur Penerima' :
                        stepNumber === 3 ? 'Atur Pengiriman' :
                        stepNumber === 4 ? 'Review & Pembayaran' :
                        'Template Pesan';
                }
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
                const selectedHour = Math.min(18, Math.max(8, Number(selectedValue || 8)));
                const normalizedSelectedValue = String(selectedHour).padStart(2, '0');

                for (let hour = 8; hour <= 18; hour += 1) {
                    const value = String(hour).padStart(2, '0');
                    options.push(`<option value="${value}"${value === normalizedSelectedValue ? ' selected' : ''}>${value}</option>`);
                }
                return options.join('');
            };

            const buildMinuteOptions = (selectedValue) => {
                return Array.from({ length: 60 }, (_, minute) => String(minute).padStart(2, '0'))
                    .map((value) => `<option value="${value}"${value === selectedValue ? ' selected' : ''}>${value}</option>`)
                    .join('');
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

            const defaultDeliverySchedule = () => {
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

                const startDate = new Date(now);
                startDate.setDate(startDate.getDate() + 1);
                const endDate = new Date(startDate);
                const dayOffset = Math.floor((new Date(end.getFullYear(), end.getMonth(), end.getDate()) - new Date(start.getFullYear(), start.getMonth(), start.getDate())) / 86400000);
                endDate.setDate(endDate.getDate() + Math.max(0, dayOffset));
                const timeValue = (date) => `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;

                return {
                    startDate: toDateInputValue(startDate),
                    endDate: toDateInputValue(endDate),
                    startTime: timeValue(start),
                    endTime: timeValue(end),
                };
            };

            const applyDefaultDeliverySchedule = (scheduleId) => {
                const defaults = defaultDeliverySchedule();
                const minDate = toDateInputValue(new Date());
                const dateStart = document.querySelector(`[data-schedule-date-start="${scheduleId}"]`);
                const dateEnd = document.querySelector(`[data-schedule-date-end="${scheduleId}"]`);

                if (dateStart) {
                    dateStart.min = minDate;
                    dateStart.value = defaults.startDate;
                }
                if (dateEnd) {
                    dateEnd.min = minDate;
                    dateEnd.value = defaults.endDate;
                }

                initScheduleTimeSelects(scheduleId, defaults.startTime, defaults.endTime);
                syncSchedulePreview(scheduleId);
                return defaults;
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

                if (timePreview && timeStartHour && timeStartMinute && timeEndHour && timeEndMinute) {
                    timePreview.textContent = `${timeStartHour.value}:${timeStartMinute.value}-${timeEndHour.value}:${timeEndMinute.value} WIB`;
                }
            };

            const initScheduleTimeSelects = (scheduleId, startValue = '09:30', endValue = '13:30') => {
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

            const syncScheduleRemoveState = () => {
                const schedules = scheduleList?.querySelectorAll('[data-schedule-item]') || [];
                schedules.forEach((item, index) => {
                    const title = item.querySelector('.campaign-schedule-card__header span');
                    const removeButton = item.querySelector('[data-remove-schedule]');
                    if (title) {
                        title.textContent = `Jadwal Pengiriman ${index + 1}`;
                    }
                    if (removeButton) {
                        removeButton.hidden = schedules.length <= 1;
                    }
                });
            };

            const getScheduleDurationMinutes = (scheduleId) => {
                const startDate = document.querySelector(`[data-schedule-date-start="${scheduleId}"]`)?.value || '';
                const endDate = document.querySelector(`[data-schedule-date-end="${scheduleId}"]`)?.value || '';
                const startHour = Number(document.querySelector(`[data-schedule-time-start-hour="${scheduleId}"]`)?.value || NaN);
                const startMinute = Number(document.querySelector(`[data-schedule-time-start-minute="${scheduleId}"]`)?.value || NaN);
                const endHour = Number(document.querySelector(`[data-schedule-time-end-hour="${scheduleId}"]`)?.value || NaN);
                const endMinute = Number(document.querySelector(`[data-schedule-time-end-minute="${scheduleId}"]`)?.value || NaN);

                if (!startDate || !endDate || [startHour, startMinute, endHour, endMinute].some(Number.isNaN)) {
                    return 0;
                }

                const startAt = new Date(`${startDate}T${String(startHour).padStart(2, '0')}:${String(startMinute).padStart(2, '0')}:00`);
                const endAt = new Date(`${endDate}T${String(endHour).padStart(2, '0')}:${String(endMinute).padStart(2, '0')}:00`);

                if (Number.isNaN(startAt.getTime()) || Number.isNaN(endAt.getTime())) {
                    return 0;
                }

                return Math.floor((endAt.getTime() - startAt.getTime()) / 60000);
            };

            const getScheduleStartDateTime = (scheduleId) => {
                const startDate = document.querySelector(`[data-schedule-date-start="${scheduleId}"]`)?.value || '';
                const startHour = Number(document.querySelector(`[data-schedule-time-start-hour="${scheduleId}"]`)?.value || NaN);
                const startMinute = Number(document.querySelector(`[data-schedule-time-start-minute="${scheduleId}"]`)?.value || NaN);

                if (!startDate || [startHour, startMinute].some(Number.isNaN)) {
                    return null;
                }

                const startAt = new Date(`${startDate}T${String(startHour).padStart(2, '0')}:${String(startMinute).padStart(2, '0')}:00`);
                return Number.isNaN(startAt.getTime()) ? null : startAt;
            };

            const isScheduleStartAllowed = (scheduleId) => {
                const startAt = getScheduleStartDateTime(scheduleId);
                const minimumStartAt = new Date(Date.now() + (4 * 60 * 60 * 1000));
                return startAt !== null && startAt >= minimumStartAt;
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
                const isRecipientValid = recipientCount >= 5;
                const recipientAlert = document.querySelector('[data-wa-lba-recipient-count-alert]');
                const audienceBase = Number((audienceEstimate?.textContent || '0').replace(/\./g, '')) || 0;
                const minimum = Math.max(5, audienceBase - Math.round(audienceBase * 0.08));
                const maximum = Math.max(minimum + 1200, audienceBase + Math.round(audienceBase * 0.18));

                if (recipientAlert) {
                    recipientAlert.hidden = isRecipientValid;
                }

                if (estimateRange) {
                    estimateRange.textContent = `${new Intl.NumberFormat('id-ID').format(minimum)}-${new Intl.NumberFormat('id-ID').format(maximum)}`;
                }

                let isScheduleValid = true;
                document.querySelectorAll('[data-schedule-item]').forEach((item) => {
                    const scheduleId = item.dataset.scheduleItem;
                    const alert = item.querySelector('[data-wa-lba-schedule-duration-alert]');
                    const isDurationValid = getScheduleDurationMinutes(scheduleId) >= 240;
                    const isStartValid = isScheduleStartAllowed(scheduleId);
                    const isTimeOrderValid = isScheduleTimeOrderValid(scheduleId);
                    const isItemValid = isDurationValid && isStartValid && isTimeOrderValid;

                    if (alert) {
                        alert.textContent = !isStartValid
                            ? 'Jadwal pengiriman minimal 4 jam dari waktu sekarang.'
                            : (!isTimeOrderValid
                                ? 'Jam mulai harus lebih kecil dari jam berakhir.'
                                : 'Atur jadwal minimal 4 jam/hari untuk memastikan target anda tercapai minimal');
                        alert.hidden = isItemValid;
                    }

                    isScheduleValid = isScheduleValid && isItemValid;
                });

                if (stepThreeNext) {
                    stepThreeNext.disabled = !(isRecipientValid && termsCheck?.checked && testRecipient?.value && isScheduleValid);
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

            const normalizeMsisdn = (value) => {
                const digits = String(value || '').replace(/\D/g, '');
                if (digits === '') {
                    return '';
                }
                if (digits.startsWith('62')) {
                    return digits;
                }
                if (digits.startsWith('0')) {
                    return `62${digits.slice(1)}`;
                }
                return `62${digits}`;
            };

            const setHiddenValue = (id, value) => {
                const input = document.getElementById(id);
                if (input) {
                    input.value = String(value ?? '');
                }
            };

            const pickScheduleValue = (selector) => {
                const element = document.querySelector(selector);
                return element && typeof element.value === 'string' ? element.value : '';
            };

            const selectedValues = (select) => Array.from(select?.selectedOptions || [])
                .map((option) => option.value)
                .filter(Boolean);

            const mapProfileValues = (values, map = {}) => values
                .map((value) => map[value] ?? value)
                .filter((value) => value !== '' && value !== 'all')
                .join('|');

            const setPaymentError = (message = '') => {
                if (!waLbaPaymentError) {
                    return;
                }

                waLbaPaymentError.textContent = message;
                waLbaPaymentError.hidden = message === '';
            };

            const normalizePaymentErrorMessage = (message = '') => {
                return String(message).toLowerCase().includes('unauthorized')
                    ? 'Session expired. Silakan login ulang.'
                    : (message || 'Gagal memproses pembayaran.');
            };

            const sleep = (ms) => new Promise((resolve) => window.setTimeout(resolve, ms));

            const setPaymentUi = ({ status, transactionId, qrisUrl, forceSuccess } = {}) => {
                const waLbaPaymentModal = waLbaPaymentPanel?.closest('.campaign-payment-modal');
                const waLbaPaymentQrDownload = waLbaPaymentModal?.querySelector('[download]');
                const qrisPanel = document.getElementById('waLbaPaymentPanel');
                const successPanel = document.getElementById('waLbaPaymentPanelSuccess');
                const failurePanel = document.getElementById('waLbaPaymentPanelFailure');

                if (waLbaPaymentModal) {
                    waLbaPaymentModal.hidden = false;
                    waLbaPaymentModal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                }

                if (forceSuccess || status === 'SUCCESS') {
                    if (qrisPanel) qrisPanel.style.display = 'none';
                    if (successPanel) successPanel.style.display = 'block';
                    if (failurePanel) failurePanel.style.display = 'none';
                } else if (status === 'FAILED' || status === 'EXPIRED') {
                    if (qrisPanel) qrisPanel.style.display = 'none';
                    if (successPanel) successPanel.style.display = 'none';
                    if (failurePanel) {
                        failurePanel.style.display = 'block';
                        const msgEl = document.getElementById('waLbaPaymentPanelFailureMsg');
                        if (msgEl) {
                            msgEl.textContent = `Pembayaran gagal. Status: ${status}`;
                        }
                    }
                } else {
                    if (qrisPanel) {
                        qrisPanel.hidden = false;
                        qrisPanel.style.display = 'block';
                    }
                    if (successPanel) successPanel.style.display = 'none';
                    if (failurePanel) failurePanel.style.display = 'none';
                }

                if (waLbaPaymentStatus) {
                    waLbaPaymentStatus.textContent = status || '-';
                }
                if (waLbaPaymentTransaction) {
                    waLbaPaymentTransaction.textContent = transactionId || '-';
                }
                if (waLbaPaymentQr && qrisUrl) {
                    const cleanedUrl = qrisUrl.replace(/\\/g, '');
                    if (waLbaPaymentQr.getAttribute('data-src-raw') !== cleanedUrl) {
                        waLbaPaymentQr.src = qrisDownloadUrl + '?url=' + encodeURIComponent(cleanedUrl) + '&disposition=inline';
                        waLbaPaymentQr.setAttribute('data-src-raw', cleanedUrl);
                    }
                }
                if (waLbaPaymentQrLink) {
                    if (qrisUrl) {
                        const cleanedUrl = qrisUrl.replace(/\\/g, '');
                        waLbaPaymentQrLink.href = qrisDownloadUrl + '?url=' + encodeURIComponent(cleanedUrl) + '&disposition=inline';
                        waLbaPaymentQrLink.hidden = false;
                    }
                }
                if (waLbaPaymentQrDownload) {
                    if (qrisUrl) {
                        const cleanedUrl = qrisUrl.replace(/\\/g, '');
                        waLbaPaymentQrDownload.href = qrisDownloadUrl + '?url=' + encodeURIComponent(cleanedUrl) + '&filename=' + encodeURIComponent(`qris-${transactionId || 'payment'}.png`);
                        waLbaPaymentQrDownload.download = `qris-${transactionId || 'payment'}.png`;
                        waLbaPaymentQrDownload.hidden = false;
                    }
                }
            };

            const getPaymentReadinessMessage = () => {
                const recipientCount = Number(recipientCountInput?.value || 0);
                const selectedTemplate = templates.find((template) => String(template.id) === String(templateSelect?.value));

                if (!selectedTemplate) {
                    return 'Pilih template pesan terlebih dahulu.';
                }
                if (!openLocationModalButton?.classList.contains('campaign-outline-button--applied')) {
                    return 'Pilih lokasi target penerima terlebih dahulu.';
                }
                if (!recipientCount || recipientCount < 5) {
                    return 'Jumlah penerima minimal 5 pesan.';
                }
                if (!testRecipient?.value) {
                    return 'Tambahkan nomor penerima tes iklan terlebih dahulu.';
                }
                if (Array.from(scheduleList?.querySelectorAll('[data-schedule-item]') || []).some((item) => !isScheduleStartAllowed(item.dataset.scheduleItem))) {
                    return 'Jadwal pengiriman minimal 4 jam dari waktu sekarang.';
                }
                if (Array.from(scheduleList?.querySelectorAll('[data-schedule-item]') || []).some((item) => getScheduleDurationMinutes(item.dataset.scheduleItem) < 240)) {
                    return 'Atur jadwal minimal 4 jam/hari.';
                }
                if (!termsCheck?.checked) {
                    return 'Centang persetujuan Syarat dan Ketentuan terlebih dahulu.';
                }

                return '';
            };

            const syncPaymentSubmitState = () => {
                if (!waLbaGwSubmitButton) {
                    return;
                }

                const message = getPaymentReadinessMessage();
                waLbaGwSubmitButton.disabled = message !== '';
                waLbaGwSubmitButton.title = message;
                if (waLbaPaymentHint) {
                    waLbaPaymentHint.textContent = message;
                    waLbaPaymentHint.hidden = message === '';
                }
            };

            const pollPaymentUntilSuccess = async (transactionId) => {
                const statusUrl = paymentStatusUrlTemplate.replace('__TRANSACTION_ID__', encodeURIComponent(transactionId));
                const startedAt = Date.now();
                const timeoutMs = 10 * 60 * 1000;

                while ((Date.now() - startedAt) < timeoutMs) {
                    let status = '';
                    let latestQrisUrl = '';

                    try {
                        const response = await fetch(statusUrl, {
                            headers: { Accept: 'application/json' },
                        });
                        const payload = await response.json().catch(() => null);
                        status = String(payload?.data?.status || '').toUpperCase();
                        latestQrisUrl = String(payload?.data?.qris_url || '').trim();
                    } catch (error) {
                        // keep polling
                    }

                    if (status) {
                        setPaymentUi({ status, transactionId, qrisUrl: latestQrisUrl });
                    }

                    if (status === 'SUCCESS') {
                        setPaymentUi({ status, transactionId, qrisUrl: null, forceSuccess: true });
                        return new Promise((resolve) => {
                            const successBtn = document.getElementById('waLbaPaymentPanelSuccessBtn');
                            const autoRedirect = setTimeout(resolve, 5000);
                            if (successBtn) {
                                successBtn.onclick = () => {
                                    clearTimeout(autoRedirect);
                                    resolve();
                                };
                            }
                        });
                    }

                    if (status === 'FAILED' || status === 'EXPIRED') {
                        throw new Error('Pembayaran gagal. Status: ' + status);
                    }

                    await sleep(3000);
                }

                throw new Error('Timeout menunggu pembayaran. Silakan coba lagi.');
            };

            const resolvePaymentInvoiceEmail = () => {
                if (!waLbaPaymentInvoiceEmailCheck?.checked) {
                    return defaultInvoiceEmail;
                }

                const email = waLbaPaymentInvoiceEmail?.value?.trim() || '';
                if (!email || !waLbaPaymentInvoiceEmail?.checkValidity()) {
                    throw new Error('Email invoice tidak valid.');
                }

                return email;
            };

            const initiatePayment = async (amount, phone, detail) => {
                const response = await fetch(paymentInitiateUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        transaction_amount: amount,
                        customer_phone: phone,
                        customer_email: resolvePaymentInvoiceEmail(),
                        product_category: 'MYADS',
                        product_type: 'ADVERTISEMENT',
                        product_detail: detail,
                    }),
                });
                const payload = await response.json().catch(() => null);

                if (!response.ok || !payload || payload.success !== true) {
                    throw new Error(payload?.message || 'Gagal initiate pembayaran.');
                }

                return payload;
            };

            const submitWabaLbaAfterPayment = async () => {
                if (!waLbaGwSubmitForm || !waLbaPaymentTransactionId || !waLbaGwSubmitButton) {
                    return;
                }

                setPaymentError('');

                const selectedTemplate = templates.find((template) => String(template.id) === String(templateSelect?.value));
                const templateId = Number(selectedTemplate?.id || templateSelect?.value || 0);
                const campaignName = `gotoads_waba_lba_${new Date().toISOString().replace(/\D/g, '').slice(0, 14)}`;
                const recipientCount = Number(recipientCountInput?.value || 0);
                const controlNumbers = String(testRecipient?.value || '').trim();
                const phone = paymentCustomerPhone;
                const totalCost = recipientCount * waLbaUnitPrice;
                const scheduleId = '1';
                const startHour = pickScheduleValue(`[data-schedule-time-start-hour="${scheduleId}"]`);
                const startMinute = pickScheduleValue(`[data-schedule-time-start-minute="${scheduleId}"]`);
                const endHour = pickScheduleValue(`[data-schedule-time-end-hour="${scheduleId}"]`);
                const endMinute = pickScheduleValue(`[data-schedule-time-end-minute="${scheduleId}"]`);

                if (!templateId) {
                    throw new Error('Template pesan belum dipilih.');
                }
                if (!phone) {
                    throw new Error('Nomor test iklan belum dipilih.');
                }
                if (!totalCost || totalCost <= 0) {
                    throw new Error('Total biaya tidak valid.');
                }

                waLbaGwSubmitButton.disabled = true;
                waLbaGwSubmitButton.classList.add('is-loading');
                waLbaGwSubmitButton.textContent = 'Menyiapkan pembayaran...';

                const invoiceEmail = resolvePaymentInvoiceEmail();
                const initiateResp = await initiatePayment(totalCost, phone, `WABA LBA - ${campaignName}`);
                const transactionId = String(initiateResp?.data?.transaction_id || '').trim();
                const qrisUrl = String(initiateResp?.data?.qris_url || '').trim();

                if (!transactionId) {
                    throw new Error('Transaction ID tidak ditemukan dari response pembayaran.');
                }

                waLbaPaymentTransactionId.value = transactionId;
                setPaymentUi({ status: 'PENDING', transactionId, qrisUrl });
                waLbaGwSubmitButton.textContent = 'Menunggu pembayaran...';

                await pollPaymentUntilSuccess(transactionId);

                waLbaGwSubmitButton.textContent = 'Pembayaran sukses. Mengirim campaign...';

                setHiddenValue('waLbaGwCampaignName', campaignName);
                setHiddenValue('waLbaGwRecipientTotal', recipientCount);
                setHiddenValue('waLbaGwTemplateId', templateId);
                setHiddenValue('waLbaGwDeliveryStartDate', document.querySelector(`[data-schedule-date-start="${scheduleId}"]`)?.value || '');
                setHiddenValue('waLbaGwDeliveryEndDate', document.querySelector(`[data-schedule-date-end="${scheduleId}"]`)?.value || '');
                setHiddenValue('waLbaGwDeliveryStartTime', startHour && startMinute ? `${startHour}:${startMinute}` : '');
                setHiddenValue('waLbaGwDeliveryEndTime', endHour && endMinute ? `${endHour}:${endMinute}` : '');
                setHiddenValue('waLbaGwControlNumber', controlNumbers);
                setHiddenValue('waLbaGwLocationName', locationState.name || 'Lokasi dipilih');
                setHiddenValue('waLbaGwLocationLat', locationState.lat ?? '');
                setHiddenValue('waLbaGwLocationLng', locationState.lng ?? '');
                setHiddenValue('waLbaGwLocationRadius', Number(locationRadiusInput?.value || 0));
                setHiddenValue('waLbaGwLocationType', 'ALL');
                setHiddenValue('waLbaGwRecipientMax', estimateRange?.textContent || '');
                setHiddenValue('waLbaGwCustomerEmail', invoiceEmail);
                setHiddenValue('waLbaGwProfileGender', mapProfileValues(selectedValues(genderSelect), { male: 'm', female: 'f' }));
                setHiddenValue('waLbaGwProfileAge', mapProfileValues(selectedValues(ageSelect)));
                setHiddenValue('waLbaGwProfileReligion', mapProfileValues(selectedValues(religionSelect)));

                waLbaGwSubmitForm.submit();
            };

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
                const taxPercentage = 11.00;
                const taxAmount = Number((totalCost * (taxPercentage / 100)).toFixed(2));
                const grandTotal = Number((totalCost + taxAmount).toFixed(2));

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
                setText('waLbaReviewSubtotal', formatCurrency(totalCost));
                setText('waLbaReviewTax', formatCurrency(taxAmount));
                setText('waLbaReviewGrandTotal', formatCurrency(grandTotal));
                setText('waLbaReviewPayment', formatCurrency(grandTotal));

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
                        const options = Array.from(select.options).filter((option) => option.value.trim() !== '');
                        const selected = options.filter((option) => option.selected);
                        const filtered = options.filter((option) => option.text.toLowerCase().includes(keyword));
                        const allSelected = options.length > 0 && options.every((option) => option.selected);

                        chips.innerHTML = selected.map((option) => `
                            <button type="button" class="campaign-multiselect__chip" data-chip-value="${option.value}">
                                <span class="campaign-multiselect__chip-remove">×</span>
                                <span>${option.text}</span>
                            </button>
                        `).join('');

                        const selectAll = options.length > 0 ? `
                            <button type="button" class="campaign-multiselect__option campaign-multiselect__option--all${allSelected ? ' campaign-multiselect__option--selected' : ''}" data-option-all="true">
                                <input type="checkbox" class="campaign-multiselect__checkbox" ${allSelected ? 'checked' : ''} tabindex="-1">
                                <span>Centang Semua</span>
                            </button>
                        ` : '';

                        optionsWrap.innerHTML = selectAll + filtered.map((option) => `
                            <button type="button" class="campaign-multiselect__option${option.selected ? ' campaign-multiselect__option--selected' : ''}" data-option-value="${option.value}">
                                <input type="checkbox" class="campaign-multiselect__checkbox" ${option.selected ? 'checked' : ''} tabindex="-1">
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
                        const allButton = event.target.closest('[data-option-all]');
                        const optionButton = event.target.closest('[data-option-value]');
                        const chipButton = event.target.closest('[data-chip-value]');

                        if (allButton) {
                            const options = Array.from(select.options).filter((option) => option.value.trim() !== '');
                            const shouldSelect = !options.every((option) => option.selected);
                            options.forEach((option) => {
                                option.selected = shouldSelect;
                            });
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                            render(search.value);
                            return;
                        }

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

            const normalizeTestRecipient = (value) => {
                const digits = String(value || '').replace(/\D/g, '');
                if (digits === '') {
                    return '';
                }
                if (digits.startsWith('62')) {
                    return digits;
                }
                if (digits.startsWith('0')) {
                    return `62${digits.slice(1)}`;
                }
                return `62${digits}`;
            };

            const getSelectedTestRecipients = () => Array.from(testRecipientList?.querySelectorAll('input[type="checkbox"]:checked') || [])
                .map((checkbox) => checkbox.closest('[data-test-recipient]')?.dataset.testRecipient || '')
                .filter(Boolean);

            const updateTestRecipientField = () => {
                if (!testRecipient) {
                    return;
                }

                testRecipient.value = getSelectedTestRecipients().join(',');
                syncStepThreeState();
            };

            const renderTestRecipientItem = (number) => {
                if (!testRecipientList || !testRecipient) {
                    return;
                }

                const existing = testRecipientList.querySelector(`[data-test-recipient="${number}"]`);
                if (existing) {
                    existing.querySelector('input[type="checkbox"]').checked = true;
                    updateTestRecipientField();
                    return;
                }

                const item = document.createElement('label');
                item.className = 'campaign-test-recipient__item';
                item.dataset.testRecipient = number;
                item.innerHTML = `
                    <input type="checkbox" checked>
                    <span>${number}</span>
                    <button type="button" class="campaign-test-recipient__remove" aria-label="Hapus nomor" title="Hapus nomor">&times;</button>
                `;

                const checkbox = item.querySelector('input[type="checkbox"]');
                const removeButton = item.querySelector('button');

                checkbox.addEventListener('change', () => {
                    updateTestRecipientField();
                });

                removeButton.addEventListener('click', (event) => {
                    event.preventDefault();
                    item.remove();
                    updateTestRecipientField();
                });

                testRecipientList.appendChild(item);
                updateTestRecipientField();
            };

            const addTestRecipient = () => {
                const number = normalizeTestRecipient(testRecipientInput?.value);
                if (number === '') {
                    return;
                }
                renderTestRecipientItem(number);
                testRecipientInput.value = '';
            };

            const addScheduleItem = () => {
                if (!scheduleList) {
                    return;
                }

                scheduleIndex += 1;
                const defaults = defaultDeliverySchedule();
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
                                <input id="waLbaScheduleDate${scheduleIndex}Start" type="date" class="text-input" value="${defaults.startDate}" min="${toDateInputValue(new Date())}" data-schedule-date-start="${scheduleIndex}">
                                <span class="campaign-range-field__separator">-</span>
                                <input id="waLbaScheduleDate${scheduleIndex}End" type="date" class="text-input" value="${defaults.endDate}" min="${toDateInputValue(new Date())}" data-schedule-date-end="${scheduleIndex}">
                            </div>
                            <p class="campaign-range-preview" id="waLbaScheduleDatePreview${scheduleIndex}">${formatScheduleDate(defaults.startDate)} - ${formatScheduleDate(defaults.endDate)}</p>
                        </div>
                        <div class="field-group campaign-field-group--compact">
                            <label class="field-label">Jam Kirim</label>
                            <div class="campaign-time-picker" data-time-picker="${scheduleIndex}">
                                <button type="button" class="campaign-time-picker__trigger" data-time-trigger="${scheduleIndex}">
                                    <span class="campaign-time-picker__value" id="waLbaScheduleTimePreview${scheduleIndex}">${defaults.startTime}-${defaults.endTime} WIB</span>
                                    <span class="campaign-time-picker__clock">i</span>
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
                    <p class="campaign-step-two__alert" data-wa-lba-schedule-duration-alert>Atur jadwal minimal 4 jam/hari untuk memastikan target anda tercapai minimal</p>
                `;

                scheduleList.appendChild(item);
                initScheduleTimeSelects(scheduleIndex, defaults.startTime, defaults.endTime);
                syncSchedulePreview(scheduleIndex);
                syncScheduleRemoveState();
                syncStepThreeState();
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
                if (locationState.lat === null || locationState.lng === null) {
                    locationState.lat = defaultMapCenter.lat;
                    locationState.lng = defaultMapCenter.lng;
                    locationState.name = 'Bundaran HI, Jakarta';
                }
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
                    const initialLat = locationState.lat ?? defaultMapCenter.lat;
                    const initialLng = locationState.lng ?? defaultMapCenter.lng;
                    liveMap = L.map(locationCanvas, { zoomControl: true }).setView([initialLat, initialLng], 14);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(liveMap);

                    liveMarker = L.marker([initialLat, initialLng]).addTo(liveMap);
                    liveCircle = L.circle([initialLat, initialLng], {
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
                    previewName.textContent = '';
                    previewSubtitle.textContent = '';
                    previewHeadline.textContent = '';
                    previewMessage.textContent = '';
                    previewAsset.removeAttribute('src');
                    previewAsset.hidden = true;
                    previewCardTitle.textContent = '';
                    previewCardCaption.textContent = '';
                    previewBubble.hidden = false;
                    previewCarousel.hidden = true;
                    previewCta.textContent = '';
                    previewCta.hidden = true;
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
                previewBubble.hidden = false;
                if (selected.asset_url) {
                    previewAsset.src = selected.asset_url;
                    previewAsset.hidden = false;
                } else {
                    previewAsset.removeAttribute('src');
                    previewAsset.hidden = true;
                }
                previewCardTitle.textContent = (selected.cards && selected.cards[0] && selected.cards[0].title) || '';
                previewCardCaption.textContent = (selected.cards && selected.cards[0] && selected.cards[0].caption) || selected.bubble_message || '';
                previewCarousel.hidden = !(selected.asset_url || (selected.cards && selected.cards.length));
                previewCta.textContent = selected.cta || '';
                previewCta.hidden = !selected.cta;
                previewPhone?.classList.add(selected.cards && selected.cards.length ? 'campaign-wa-lba-preview__phone--carousel' : 'campaign-wa-lba-preview__phone--basic');
                renderCards(selected.cards || []);
            };

            templateSelect?.addEventListener('change', syncTemplateDisplay);
            nextButton?.addEventListener('click', () => {
                if (!nextButton.disabled) {
                    syncStepState(2);
                    
                    fetch("{{ route('campaign.track-event') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ event_type: 'campaign_setup_page' })
                    }).catch(err => console.warn('Tracking failed:', err));
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
                    syncPaymentSubmitState();
                    syncStepState(4);

                    fetch("{{ route('campaign.track-event') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ event_type: 'campaign_delivery_setup' })
                    }).catch(err => console.warn('Tracking failed:', err));
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
            addScheduleButton?.addEventListener('click', addScheduleItem);
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
                    syncScheduleRemoveState();
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

            const syncDraftScheduleState = () => {
                document.querySelectorAll('[data-schedule-date-start]').forEach((field) => {
                    const scheduleId = field.dataset.scheduleDateStart;
                    if (scheduleId) {
                        syncSchedulePreview(scheduleId);
                    }
                });
                syncScheduleRemoveState();
                syncStepThreeState();
                syncReviewSummary();
                syncPaymentSubmitState();
            };

            document.addEventListener('campaign:draft-restored', syncDraftScheduleState);

            recipientCountInput?.addEventListener('input', syncStepThreeState);
            termsCheck?.addEventListener('change', syncStepThreeState);
            testRecipient?.addEventListener('change', syncStepThreeState);
            testRecipientAdd?.addEventListener('click', addTestRecipient);
            testRecipientInput?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    addTestRecipient();
                }
            });
            waLbaGwSubmitButton?.addEventListener('click', async () => {
                try {
                    await submitWabaLbaAfterPayment();
                } catch (error) {
                    const message = normalizePaymentErrorMessage(error?.message ? String(error.message) : '');
                    setPaymentUi({ status: 'FAILED' });
                    setPaymentError(message);
                    waLbaGwSubmitButton.classList.remove('is-loading');
                    waLbaGwSubmitButton.disabled = false;
                    waLbaGwSubmitButton.textContent = 'Bayar & Kirim Iklan';
                }
            });
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
            applyDefaultDeliverySchedule(1);
            syncScheduleRemoveState();
            syncTemplateDisplay();
            syncStepState(1);
            syncStepTwoState();
            syncStepThreeState();
            syncPaymentSubmitState();
        });
    </script>
    @include('partials.campaign-draft-state', [
        'storageKey' => "campaign-draft:{$channel}:{$menu}",
    ])
@endpush
