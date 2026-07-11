@php
    $title = $page['title'];
    $bodyClass = 'campaign-title-ready';
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
    $activeSubnav = 'wa-targeted';
    $autoCampaignName = 'gotoads_waba_targeted_' . now()->format('YmdHis');
    $waTemplates = collect($waTemplates ?? []);
@endphp

@extends('layouts.portal')

@section('content')
    @include('partials.waba-template-suggestion-modal', ['flowLabel' => 'WA Business Targeted'])

    <section class="campaign-page-intro">
        <p class="campaign-page-intro__crumb">Dashboard / Buat Iklan WA Business Targeted</p>
        <h1 class="campaign-page-intro__title">Buat Iklan WA Business Targeted</h1>
    </section>

    <section class="campaign-stepper-card">
        <div class="campaign-stepper">
            @foreach ($page['steps'] as $index => $step)
                <div class="campaign-step {{ $index === 0 ? 'campaign-step--active' : '' }}" data-wa-targeted-step="{{ $index + 1 }}">
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
                <p class="campaign-section-kicker" id="waTargetedSectionKicker">Step 01</p>
                <h1 id="waTargetedSectionHeading">Template Pesan</h1>
            </div>
            <div class="campaign-title-chip">
                <span>Judul Iklan</span>
                <strong id="waTargetedCampaignName">{{ $autoCampaignName }}</strong>
            </div>
        </div>

        <div class="campaign-step-panel" data-wa-targeted-panel="1">
        <div class="campaign-wa-lba-step" id="waTargetedStep">
            <div class="campaign-wa-lba-step__layout">
                <div class="campaign-wa-lba-step__main">
                    <div class="field-group campaign-wa-lba-step__field">
                        <label for="waTargetedTemplate" class="field-label sr-only">Template Pesan</label>
                        <select id="waTargetedTemplate" class="text-input text-input--select">
                            <option value="">Template Pesan</option>
                            @foreach ($waTemplates as $template)
                                <option value="{{ $template['id'] }}">{{ $template['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($waTemplates->isEmpty())
                        <div class="campaign-template-empty-state">
                            <strong>Belum ada template pesan.</strong>
                            <p>Buat template pesan terlebih dahulu agar bisa dipilih untuk iklan WA Business Targeted.</p>
                            <a href="{{ route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'campaign-template', 'view' => 'create']) }}" class="campaign-inline-link campaign-inline-link--fit">
                                Buat Template Pesan
                            </a>
                        </div>
                    @endif

                    <div class="campaign-wa-lba-template" id="waTargetedTemplateDetails" hidden>
                        <p class="campaign-wa-lba-template__note" id="waTargetedPriceNote"></p>

                        <div class="campaign-wa-lba-template__meta">
                            <div>
                                <span>Akun WA Business</span>
                                <strong id="waTargetedAccountName">-</strong>
                            </div>
                            <div>
                                <span>Tipe Template</span>
                                <strong id="waTargetedTemplateKind">-</strong>
                            </div>
                            <div>
                                <span>Tipe Template</span>
                                <strong id="waTargetedChannelKind">-</strong>
                            </div>
                        </div>

                        <div class="campaign-wa-lba-template__body">
                            <span>Bubble Message</span>
                            <strong id="waTargetedBubbleHeadline">-</strong>
                            <p id="waTargetedBubbleMessage">-</p>
                        </div>

                        <div class="campaign-wa-lba-template__cards" id="waTargetedCardsBlock" hidden>
                            <span>Carousel Template</span>
                            <div class="campaign-wa-lba-template__accordion" id="waTargetedCardsAccordion"></div>
                        </div>
                    </div>
                </div>

                <aside class="campaign-wa-lba-preview" id="waTargetedPreview" hidden>
                    <div class="campaign-wa-lba-preview__phone" id="waTargetedPreviewPhone">
                        <div class="campaign-wa-lba-preview__topbar">
                            <span class="campaign-wa-lba-preview__back">←</span>
                            <div class="campaign-wa-lba-preview__profile">
                                <span class="campaign-wa-lba-preview__avatar"></span>
                                <div>
                                    <strong id="waTargetedPreviewName"></strong>
                                    <span id="waTargetedPreviewSubtitle"></span>
                                </div>
                            </div>
                            <span class="campaign-wa-lba-preview__menu">⋮</span>
                        </div>

                        <div class="campaign-wa-lba-preview__chat">
                            <div class="campaign-wa-lba-preview__bubble" id="waTargetedPreviewBubble">
                                <strong id="waTargetedPreviewHeadline"></strong>
                                <p id="waTargetedPreviewMessage"></p>
                            </div>

                            <div class="campaign-wa-lba-preview__carousel" id="waTargetedPreviewCarousel" hidden>
                                <img src="" alt="Preview template" id="waTargetedPreviewAsset" hidden>
                                <div class="campaign-wa-lba-preview__carousel-copy">
                                    <span class="campaign-wa-lba-preview__brand"></span>
                                    <strong id="waTargetedPreviewCardTitle"></strong>
                                    <p id="waTargetedPreviewCardCaption"></p>
                                </div>
                            </div>

                            <div class="campaign-wa-lba-preview__action" id="waTargetedPreviewCta" hidden></div>
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

                <button type="button" class="submit-btn lba-primary-btn campaign-step-nav-btn" id="waTargetedNextButton" disabled>
                    Lanjutkan
                </button>
            </div>
        </div>
        </div>

        <div class="campaign-step-panel" data-wa-targeted-panel="2" hidden>
            <section class="campaign-targeted-step-two">
                <div class="campaign-targeted-step-two__section">
                    <h2 class="campaign-block__title">Atur Profil Penerima</h2>
                    <div class="campaign-loading-inline" id="waTargetedProfileLoading">
                        <span class="campaign-loading-inline__spinner" aria-hidden="true"></span>
                        <span>Memuat opsi profil penerima...</span>
                    </div>
                    <div class="field-group campaign-targeted-step-two__field">
                        <label for="waTargetedRecipientType" class="field-label">Target Penerima</label>
                        <select id="waTargetedRecipientType" class="text-input text-input--select">
                            <option value="profile-recipient" selected>Profil Penerima</option>
                        </select>
                    </div>

                    <div class="campaign-targeted-profile-builder" id="waTargetedProfileBuilder" hidden>
                        <div class="campaign-targeted-step-two__saved">
                            <h3>Buat Baru Profil Penerima</h3>
                            <div class="campaign-targeted-profile-grid">
                                <div class="field-group campaign-field-group--compact">
                                    <label for="waTargetedGender" class="field-label">Jenis Kelamin</label>
                                    <select id="waTargetedGender" class="campaign-search-multiselect" multiple data-placeholder="Cari jenis kelamin">
                                        <option value="M">Laki-laki</option>
                                        <option value="F">Perempuan</option>
                                    </select>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label for="waTargetedCardType" class="field-label">Tipe Kartu</label>
                                    <select id="waTargetedCardType" class="campaign-search-multiselect" multiple data-placeholder="Cari tipe kartu">
                                        <option value="Telkomsel Halo">Telkomsel Halo</option>
                                        <option value="Telkomsel PraBayar">Telkomsel PraBayar</option>
                                    </select>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label for="waTargetedAge" class="field-label">Rentang Umur</label>
                                    <select id="waTargetedAge" class="campaign-search-multiselect" multiple data-placeholder="Cari rentang umur">
                                        <option value="under-15">&lt; 15 tahun</option>
                                        <option value="15-24">15 - 24 tahun</option>
                                        <option value="25-34">25 - 34 tahun</option>
                                        <option value="35-44">35 - 44 tahun</option>
                                        <option value="45-plus">45+ tahun</option>
                                    </select>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label for="waTargetedOs" class="field-label">Tipe OS Handphone</label>
                                    <select id="waTargetedOs" class="campaign-search-multiselect" multiple data-placeholder="Cari tipe OS handphone">
                                        <option value="Android">Android</option>
                                        <option value="iOS">iOS</option>
                                    </select>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label for="waTargetedDataPackage" class="field-label">Penerima dengan Paket Data</label>
                                    <select id="waTargetedDataPackage" class="campaign-search-multiselect" multiple data-placeholder="Cari status paket data">
                                        <option value="Y">Aktif</option>
                                        <option value="N">Tidak Aktif</option>
                                    </select>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label for="waTargetedHouseholdSpend" class="field-label">Tingkat Pengeluaran Rumah Tangga (per Bulan)</label>
                                    <select id="waTargetedHouseholdSpend" class="campaign-search-multiselect" multiple data-placeholder="Cari tingkat pengeluaran rumah tangga">
                                        <option value="lt-2m">&lt; Rp 2.000.000</option>
                                        <option value="2m-5m">Rp 2.000.000 - Rp 5.000.000</option>
                                        <option value="5m-10m">Rp 5.000.000 - Rp 10.000.000</option>
                                        <option value="gt-10m">&gt; Rp 10.000.000</option>
                                    </select>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label for="waTargetedMaritalStatus" class="field-label">Status Pernikahan</label>
                                    <select id="waTargetedMaritalStatus" class="campaign-search-multiselect" multiple data-placeholder="Cari status pernikahan">
                                        <option value="SINGLE">Single</option>
                                        <option value="MARRIED">Married</option>
                                    </select>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label for="waTargetedLifestyle" class="field-label">Lifestyle Segment</label>
                                    <select id="waTargetedLifestyle" class="campaign-search-multiselect" multiple data-placeholder="Cari lifestyle segment">
                                        <option value="FOODIES">Foodies</option>
                                        <option value="TRAVELER">Traveler</option>
                                        <option value="FAMILY">Family</option>
                                    </select>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label for="waTargetedReligion" class="field-label">Agama</label>
                                    <select id="waTargetedReligion" class="campaign-search-multiselect" multiple data-placeholder="Cari agama">
                                        <option value="ISLAM">Islam</option>
                                        <option value="KRISTEN">Kristen</option>
                                        <option value="KATOLIK">Katolik</option>
                                        <option value="HINDU">Hindu</option>
                                        <option value="BUDHA">Budha</option>
                                        <option value="KONGHUCU">Konghucu</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        {{-- <a href="#" class="campaign-inline-link campaign-inline-link--strong campaign-inline-link--arrow campaign-inline-link--fit">Gunakan Pengaturan Audience</a> --}}

                        <div class="campaign-targeted-location-group">
                            <div class="campaign-targeted-location-group__head">
                                <h3>Lokasi Tempat Tinggal</h3>
                                <button type="button" class="campaign-inline-action" id="waTargetedAddHomeLocation">
                                    <span class="campaign-inline-action__icon">+</span>
                                    <span>Tambah Lokasi Tempat Tinggal</span>
                                </button>
                            </div>
                            <div class="campaign-targeted-location-list" id="waTargetedHomeLocationList"></div>
                        </div>

                        <div class="campaign-targeted-location-group">
                            <div class="campaign-targeted-location-group__head">
                                <h3>Lokasi Tempat Bekerja</h3>
                                <button type="button" class="campaign-inline-action" id="waTargetedAddWorkLocation">
                                    <span class="campaign-inline-action__icon">+</span>
                                    <span>Tambah Lokasi Tempat Bekerja</span>
                                </button>
                            </div>
                            <div class="campaign-targeted-location-list" id="waTargetedWorkLocationList"></div>
                        </div>

                        {{-- <label class="campaign-terms campaign-terms--compact">
                            <input type="checkbox" id="waTargetedSaveProfile">
                            <span>Simpan Pengaturan Profil Penerima</span>
                        </label> --}}
                    </div>
                </div>

                <div class="campaign-form-actions campaign-form-actions--step-two campaign-form-actions--targeted">
                    <button type="button" class="campaign-outline-button campaign-step-nav-btn" id="waTargetedStepTwoBack">Sebelumnya</button>
                    <button type="button" class="submit-btn lba-primary-btn campaign-step-two__next" id="waTargetedStepTwoNext" disabled>Lanjutkan</button>
                </div>
            </section>
        </div>

        <div class="campaign-step-panel" data-wa-targeted-panel="3" hidden>
            <section class="campaign-step-two">
                <div class="campaign-step-two__section">
                    <h2 class="campaign-block__title">Tentukan Jumlah Penerima yang Akan Dikirim</h2>
                    <div class="campaign-send-grid">
                        <div class="field-group campaign-field-group--compact">
                            <label for="waTargetedRecipientCount" class="field-label">Jumlah Penerima</label>
                            <input id="waTargetedRecipientCount" type="number" class="text-input" min="5" value="0" placeholder="Jumlah Penerima">
                            <p class="campaign-step-two__alert" data-wa-targeted-recipient-count-alert>Jumlah penerima minimal 5 perhari</p>
                        </div>
                        <strong id="waTargetedEstimate" hidden>-</strong>
                    </div>
                </div>

                <div class="campaign-step-two__section">
                    <h2 class="campaign-block__title">Jadwal Pengiriman Pesan</h2>
                    <div class="campaign-schedule-list" id="waTargetedScheduleList">
                        <article class="campaign-schedule-card" data-wa-targeted-schedule-item="1">
                            <div class="campaign-schedule-card__header">
                                <span>Jadwal Pengiriman 1</span>
                                <button type="button" class="campaign-schedule-card__remove" data-wa-targeted-remove-schedule hidden>Hapus</button>
                            </div>
                            <div class="campaign-schedule-card__grid">
                                <div class="field-group campaign-field-group--compact">
                                    <label class="field-label" for="waTargetedScheduleDate1Start">Tgl Kirim</label>
                                    <div class="campaign-range-field">
                                        <input id="waTargetedScheduleDate1Start" type="date" class="text-input" value="{{ now()->addDay()->toDateString() }}" min="{{ now()->toDateString() }}" data-wa-targeted-schedule-date-start="1">
                                        <span class="campaign-range-field__separator">-</span>
                                        <input id="waTargetedScheduleDate1End" type="date" class="text-input" value="{{ now()->addDay()->toDateString() }}" min="{{ now()->toDateString() }}" data-wa-targeted-schedule-date-end="1">
                                    </div>
                                    <p class="campaign-range-preview" id="waTargetedScheduleDatePreview1">{{ now()->addDay()->format('d/m/y') }} - {{ now()->addDay()->format('d/m/y') }}</p>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label class="field-label" for="waTargetedScheduleTime1">Jam Kirim</label>
                                    <div class="campaign-time-picker" data-wa-targeted-time-picker="1">
                                        <button type="button" class="campaign-time-picker__trigger" data-wa-targeted-time-trigger="1">
                                            <span class="campaign-time-picker__value" id="waTargetedScheduleTimePreview1">09:30-10:00 WIB</span>
                                            <span class="campaign-time-picker__clock">i</span>
                                        </button>
                                        <div class="campaign-time-picker__panel" data-wa-targeted-time-panel="1" hidden>
                                            <div class="campaign-time-picker__columns">
                                                <div class="campaign-time-picker__column">
                                                    <span class="campaign-time-picker__label">Jam Mulai</span>
                                                    <div class="campaign-time-picker__inputs">
                                                        <select id="waTargetedScheduleTime1StartHour" class="campaign-time-picker__select" data-wa-targeted-schedule-time-start-hour="1"></select>
                                                        <span class="campaign-time-picker__colon">:</span>
                                                        <select id="waTargetedScheduleTime1StartMinute" class="campaign-time-picker__select" data-wa-targeted-schedule-time-start-minute="1"></select>
                                                        <span class="campaign-time-picker__suffix">WIB</span>
                                                    </div>
                                                </div>
                                                <div class="campaign-time-picker__column">
                                                    <span class="campaign-time-picker__label">Jam Berakhir</span>
                                                    <div class="campaign-time-picker__inputs">
                                                        <select id="waTargetedScheduleTime1EndHour" class="campaign-time-picker__select" data-wa-targeted-schedule-time-end-hour="1"></select>
                                                        <span class="campaign-time-picker__colon">:</span>
                                                        <select id="waTargetedScheduleTime1EndMinute" class="campaign-time-picker__select" data-wa-targeted-schedule-time-end-minute="1"></select>
                                                        <span class="campaign-time-picker__suffix">WIB</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="campaign-time-picker__hint">Disarankan durasi pengiriman pesan lebih dari 4 jam agar pengiriman lebih optimal</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="campaign-step-two__alert" data-wa-targeted-schedule-duration-alert>Atur jadwal minimal 4 jam/hari untuk memastikan target anda tercapai minimal</p>
                        </article>
                    </div>
                    <div class="campaign-schedule-actions">
                        <div class="campaign-schedule-divider"></div>
                        <button type="button" class="campaign-inline-action" id="waTargetedAddSchedule">
                            <span class="campaign-inline-action__icon">+</span>
                            <span>Tambah Jadwal Pengirim</span>
                        </button>
                    </div>
                </div>

                <div class="campaign-step-two__section">
                    <div class="field-group">
                        <label for="waTargetedDeliveryMethod" class="field-label">Metode Pengiriman Pesan</label>
                        <select id="waTargetedDeliveryMethod" class="text-input text-input--select">
                            {{-- <option>Metode Pengiriman Pesan</option> --}}
                            <option>Secepatnya</option>
                            <option>Merata per jadwal</option>
                        </select>
                    </div>
                    <p class="campaign-block__copy">
                        Metode pengiriman pesan secepatnya akan memaksimalkan pesan Anda dikirim sesuai dengan jadwal pertama yang ditentukan. Apabila terdapat sejumlah pesan yang belum terkirim pada jadwal pertama, maka pesan-pesan tersebut akan dikirim sesuai dengan jadwal selanjutnya.
                    </p>
                </div>

                <div class="campaign-step-two__section">
                    <h2 class="campaign-block__title">Tentukan Nomor Penerima Tes Iklan</h2>
                    <div class="campaign-test-recipient" id="waTargetedTestRecipientBuilder">
                        <input type="hidden" id="waTargetedTestRecipient" value="">
                        <fieldset class="campaign-test-recipient__box">
                            <legend>Nomor Penerima Tes Iklan</legend>
                            <div class="campaign-test-recipient__entry">
                                <span class="campaign-test-recipient__prefix">+62</span>
                                <input id="waTargetedTestRecipientInput" type="text" inputmode="numeric" class="campaign-test-recipient__input" placeholder="Nomor Penerima Tes Iklan">
                                <button type="button" class="campaign-test-recipient__add" id="waTargetedTestRecipientAdd">Tambahkan</button>
                            </div>
                            <div class="campaign-test-recipient__list" id="waTargetedTestRecipientList"></div>
                        </fieldset>
                    </div>
                </div>

                <div class="campaign-step-two__section">
                    <label class="campaign-terms">
                        <input type="checkbox" id="waTargetedTermsCheck">
                        <span>
                            Saya menyetujui <a href="{{ route('campaign.terms') }}" data-campaign-terms-trigger>Syarat dan Ketentuan</a> yang berlaku di website Telkomsel MyAds. Atas setiap pesan iklan yang dibuat oleh pengguna menggunakan produk dan/atau layanan melalui portal myAds, pengguna dilarang untuk mempergunakan kata-kata, komentar, gambar atau konten apapun yang mengandung unsur SARA atau diskriminasi terhadap pihak manapun, bersifat vulgar dan ancaman, atau hal-hal lain yang dapat dianggap tidak sesuai dengan nilai dan norma sosial.
                        </span>
                    </label>
                </div>

                @include('partials.campaign-terms-modal')

                <div class="campaign-form-actions campaign-form-actions--step-two campaign-form-actions--targeted">
                    <button type="button" class="campaign-outline-button campaign-step-nav-btn" id="waTargetedStepThreeBack">Sebelumnya</button>
                    <button type="button" class="submit-btn lba-primary-btn campaign-step-two__next" id="waTargetedStepThreeNext" disabled>Lanjutkan</button>
                </div>
            </section>
        </div>

        <div class="campaign-step-panel" data-wa-targeted-panel="4" hidden>
            <section class="campaign-review">
                <div class="campaign-review__main">
                    <div class="campaign-review__hero">
                        <h2 class="campaign-review__title">Review</h2>
                    </div>

                    <div class="campaign-review__field">
                        <div class="campaign-review__field-head">
                            <span class="campaign-review__label">Judul Iklan</span>
                            <button type="button" class="campaign-review-card__link" data-wa-targeted-review-edit="1">Ubah</button>
                        </div>
                        <strong id="waTargetedReviewTitle">-</strong>
                    </div>

                    <article class="campaign-review-card" data-wa-targeted-review-card="content">
                        <div class="campaign-review-card__header">
                            <div class="campaign-review-card__head">
                                <span class="campaign-review-card__icon campaign-review-card__icon--content" aria-hidden="true"></span>
                                <h3>Konten Iklan</h3>
                            </div>
                            <div class="campaign-review-card__actions">
                                <button type="button" class="campaign-review-card__link" data-wa-targeted-review-edit="1">Ubah</button>
                                <button type="button" class="campaign-review-card__toggle" data-wa-targeted-review-toggle="content">Tampilkan</button>
                            </div>
                        </div>
                        <div class="campaign-review-card__body" data-wa-targeted-review-body="content" hidden>
                            <div class="campaign-review-grid">
                                <div>
                                    <span class="campaign-review-grid__label">Template Pesan</span>
                                    <strong id="waTargetedReviewTemplateName">-</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Akun WA Business</span>
                                    <strong id="waTargetedReviewSender">-</strong>
                                </div>
                                <div class="campaign-review-grid__wide">
                                    <span class="campaign-review-grid__label">Bubble Message</span>
                                    <strong id="waTargetedReviewMessage">-</strong>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="campaign-review-card" data-wa-targeted-review-card="recipient">
                        <div class="campaign-review-card__header">
                            <div class="campaign-review-card__head">
                                <span class="campaign-review-card__icon campaign-review-card__icon--recipient" aria-hidden="true"></span>
                                <h3>Profil Penerima</h3>
                            </div>
                            <div class="campaign-review-card__actions">
                                <button type="button" class="campaign-review-card__link" data-wa-targeted-review-edit="2">Ubah</button>
                                <button type="button" class="campaign-review-card__toggle" data-wa-targeted-review-toggle="recipient">Tampilkan</button>
                            </div>
                        </div>
                        <div class="campaign-review-card__body" data-wa-targeted-review-body="recipient" hidden>
                            <div class="campaign-review-grid">
                                <div>
                                    <span class="campaign-review-grid__label">Jenis Kelamin</span>
                                    <strong id="waTargetedReviewGender">Semua</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Rentang Umur</span>
                                    <strong id="waTargetedReviewAge">Semua</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Agama</span>
                                    <strong id="waTargetedReviewReligion">Semua</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Tipe Kartu</span>
                                    <strong id="waTargetedReviewCardType">Semua</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Tipe OS Handphone</span>
                                    <strong id="waTargetedReviewOs">Semua</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Lifestyle Segment</span>
                                    <strong id="waTargetedReviewLifestyle">Semua</strong>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="campaign-review-card" data-wa-targeted-review-card="delivery">
                        <div class="campaign-review-card__header">
                            <div class="campaign-review-card__head">
                                <span class="campaign-review-card__icon campaign-review-card__icon--delivery" aria-hidden="true"></span>
                                <h3>Waktu Pengiriman</h3>
                            </div>
                            <div class="campaign-review-card__actions">
                                <button type="button" class="campaign-review-card__link" data-wa-targeted-review-edit="3">Ubah</button>
                                <button type="button" class="campaign-review-card__toggle" data-wa-targeted-review-toggle="delivery">Tampilkan</button>
                            </div>
                        </div>
                        <div class="campaign-review-card__body" data-wa-targeted-review-body="delivery" hidden>
                            <div class="campaign-review-schedule" id="waTargetedReviewSchedules"></div>
                            <div class="campaign-review-grid campaign-review-grid--delivery">
                                <div>
                                    <span class="campaign-review-grid__label">Metode Pengiriman</span>
                                    <strong id="waTargetedReviewDeliveryMethod">Pengiriman Secepatnya</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Nomor Test Iklan</span>
                                    <strong id="waTargetedReviewTestRecipient">-</strong>
                                </div>
                            </div>
                            <div class="campaign-review-total">
                                <span>Total Pesan yang akan dikirim</span>
                                <strong id="waTargetedReviewRecipientCount">0 Pesan</strong>
                            </div>
                            <p class="campaign-review-note">dari estimasi <span id="waTargetedReviewPotentialRange">-</span> penerima potensial</p>
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
                                    <strong>Targeted</strong>
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
                                    <strong id="waTargetedReviewGrandTotal">Rp 5.500</strong>
                                </div>
                            </div>
                            <div class="campaign-cost-card__section">
                                <span class="campaign-cost-card__section-title">Pembayaran Anda Menggunakan</span>
                                <div class="campaign-cost-card__row">
                                    <span>Saldo Umum</span>
                                    <strong class="campaign-cost-card__danger" id="waTargetedReviewPayment">Rp 5.500</strong>
                                </div>
                            </div>
                            @include('partials.payment-invoice-email', [
                                'inputId' => 'waTargetedPaymentInvoiceEmail',
                                'checkboxId' => 'waTargetedPaymentInvoiceEmailCheck',
                            ])
                            @if($errors->has('gw'))
                                <div class="alert alert-danger" style="color: #ff1f3d; background: #ffeef0; padding: 12px; border-radius: 8px; margin-bottom: 16px; border: 1px solid #ffaeb5; font-size: 14px; font-weight: bold; text-align: left;">
                                    {{ $errors->first('gw') }}
                                </div>
                            @endif
                            <p class="campaign-payment-error" id="waTargetedPaymentError" hidden></p>
                            <p class="campaign-payment-hint" id="waTargetedPaymentHint" hidden></p>
                            <button type="button" class="campaign-cost-card__cta" id="waTargetedGwSubmitButton">Bayar & Kirim Iklan</button>
                            @include('partials.payment-qris-modal', [
                                'modalId' => 'waTargetedPaymentModal',
                                'panelId' => 'waTargetedPaymentPanel',
                                'statusId' => 'waTargetedPaymentStatus',
                                'transactionId' => 'waTargetedPaymentTransaction',
                                'qrId' => 'waTargetedPaymentQr',
                                'qrLinkId' => 'waTargetedPaymentQrLink',
                                'downloadId' => 'waTargetedPaymentQrDownload',
                                'footnote' => 'Apabila terdapat pesan yang tidak terkirim, maka biaya akan dikembalikan (refund) sesuai jumlah pesan yang tidak terkirim. Detil mengenai jumlah iklan terkirim/tidak terkirim akan diinformasikan pada menu laporan setelah iklan Anda tayang.',
                            ])
                            <form id="waTargetedGwSubmitForm" method="POST" action="{{ route('campaign.waba-targeted.send') }}" hidden>
                                @csrf
                                <input type="hidden" name="payment_transaction_id" id="waTargetedPaymentTransactionId" value="">
                                <input type="hidden" name="campaign_name" id="waTargetedGwCampaignName" value="{{ $autoCampaignName }}">
                                <input type="hidden" name="waba_user_template_id" id="waTargetedGwTemplateId" value="">
                                <input type="hidden" name="recipient_total" id="waTargetedGwRecipientTotal" value="">
                                <input type="hidden" name="delivery_date" id="waTargetedGwDeliveryDate" value="">
                                <input type="hidden" name="delivery_time" id="waTargetedGwDeliveryTime" value="">
                                <input type="hidden" name="control_number" id="waTargetedGwControlNumber" value="">
                                <input type="hidden" name="email_invoice" id="waTargetedGwCustomerEmail" value="">
                                <input type="hidden" name="profile_gender" id="waTargetedGwProfileGender" value="">
                                <input type="hidden" name="profile_age" id="waTargetedGwProfileAge" value="">
                                <input type="hidden" name="profile_mobile_data_subs" id="waTargetedGwProfileMobileDataSubs" value="">
                                <input type="hidden" name="profile_arpu" id="waTargetedGwProfileArpu" value="">
                                <input type="hidden" name="profile_os" id="waTargetedGwProfileOs" value="">
                                <input type="hidden" name="profile_marital_status" id="waTargetedGwProfileMaritalStatus" value="">
                                <input type="hidden" name="profile_lifestyle_segment" id="waTargetedGwProfileLifestyleSegment" value="">
                                <input type="hidden" name="profile_religion" id="waTargetedGwProfileReligion" value="">
                                <input type="hidden" name="profile_card_type" id="waTargetedGwProfileCardType" value="">
                                <input type="hidden" name="profile_hometown" id="waTargetedGwProfileHometown" value="">
                                <input type="hidden" name="profile_work" id="waTargetedGwProfileWork" value="">
                            </form>
                        </div>
                    </div>
                </aside>
                <div class="campaign-form-actions campaign-form-actions--review campaign-form-actions--review-bottom">
                    <button type="button" class="campaign-outline-button campaign-step-nav-btn" id="waTargetedStepFourBack">Sebelumnya</button>
                </div>
            </section>
        </div>
    </section>
@endsection

@section('after_shell')
    @include('partials.payment-qris-modal-assets')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.paymentQrisRedirectUrl = "{{ route('campaign.menu', ['channel' => $channel, 'menu' => $menu]) }}";
            const templateSelect = document.getElementById('waTargetedTemplate');
            const stepRoot = document.getElementById('waTargetedStep');
            const nextButton = document.getElementById('waTargetedNextButton');
            const stepTwoBack = document.getElementById('waTargetedStepTwoBack');
            const stepTwoNext = document.getElementById('waTargetedStepTwoNext');
            const stepThreeBack = document.getElementById('waTargetedStepThreeBack');
            const stepThreeNext = document.getElementById('waTargetedStepThreeNext');
            const stepFourBack = document.getElementById('waTargetedStepFourBack');
            const stepPanels = document.querySelectorAll('[data-wa-targeted-panel]');
            const stepItems = document.querySelectorAll('[data-wa-targeted-step]');
            const sectionKicker = document.getElementById('waTargetedSectionKicker');
            const sectionHeading = document.getElementById('waTargetedSectionHeading');
            const targetedRecipient = document.getElementById('waTargetedRecipientType');
            const targetedProfileBuilder = document.getElementById('waTargetedProfileBuilder');
            const targetedProfileFields = Array.from(document.querySelectorAll('#waTargetedProfileBuilder select'));
            const addHomeLocationButton = document.getElementById('waTargetedAddHomeLocation');
            const addWorkLocationButton = document.getElementById('waTargetedAddWorkLocation');
            const homeLocationList = document.getElementById('waTargetedHomeLocationList');
            const workLocationList = document.getElementById('waTargetedWorkLocationList');
            const scheduleList = document.getElementById('waTargetedScheduleList');
            const addScheduleButton = document.getElementById('waTargetedAddSchedule');
            const recipientCountInput = document.getElementById('waTargetedRecipientCount');
            const termsCheck = document.getElementById('waTargetedTermsCheck');
            const testRecipientField = document.getElementById('waTargetedTestRecipient');
            const testRecipientInput = document.getElementById('waTargetedTestRecipientInput');
            const testRecipientAdd = document.getElementById('waTargetedTestRecipientAdd');
            const testRecipientList = document.getElementById('waTargetedTestRecipientList');
            const reviewSchedules = document.getElementById('waTargetedReviewSchedules');
            const templateDetails = document.getElementById('waTargetedTemplateDetails');
            const preview = document.getElementById('waTargetedPreview');
            const previewPhone = document.getElementById('waTargetedPreviewPhone');
            const priceNote = document.getElementById('waTargetedPriceNote');
            const accountName = document.getElementById('waTargetedAccountName');
            const templateKind = document.getElementById('waTargetedTemplateKind');
            const channelKind = document.getElementById('waTargetedChannelKind');
            const bubbleHeadline = document.getElementById('waTargetedBubbleHeadline');
            const bubbleMessage = document.getElementById('waTargetedBubbleMessage');
            const cardsBlock = document.getElementById('waTargetedCardsBlock');
            const cardsAccordion = document.getElementById('waTargetedCardsAccordion');
            const previewName = document.getElementById('waTargetedPreviewName');
            const previewSubtitle = document.getElementById('waTargetedPreviewSubtitle');
            const previewHeadline = document.getElementById('waTargetedPreviewHeadline');
            const previewMessage = document.getElementById('waTargetedPreviewMessage');
            const previewBubble = document.getElementById('waTargetedPreviewBubble');
            const previewCarousel = document.getElementById('waTargetedPreviewCarousel');
            const previewAsset = document.getElementById('waTargetedPreviewAsset');
            const previewCardTitle = document.getElementById('waTargetedPreviewCardTitle');
            const previewCardCaption = document.getElementById('waTargetedPreviewCardCaption');
            const previewCta = document.getElementById('waTargetedPreviewCta');
            const templates = @json($waTemplates->values());
            const profileDropdownUrl = "{{ route('campaign.targeted.profile-dropdown-list', ['channel' => $channel]) }}";
            const paymentInitiateUrl = "{{ route('campaign.payment.initiate') }}";
            const paymentStatusUrlTemplate = "{{ route('campaign.payment.status', ['transactionId' => '__TRANSACTION_ID__']) }}";
            const qrisDownloadUrl = "{{ route('campaign.payment.qris-download') }}";
            const csrfToken = "{{ csrf_token() }}";
            const paymentCustomerPhone = '6285129640197';
            const defaultInvoiceEmail = 'myads@telkomsel.com';
            const waTargetedUnitPrice = 1100;
            const waTargetedPaymentError = document.getElementById('waTargetedPaymentError');
            const waTargetedPaymentHint = document.getElementById('waTargetedPaymentHint');
            const waTargetedPaymentInvoiceEmail = document.getElementById('waTargetedPaymentInvoiceEmail');
            const waTargetedPaymentInvoiceEmailCheck = document.getElementById('waTargetedPaymentInvoiceEmailCheck');
            const waTargetedProfileLoading = document.getElementById('waTargetedProfileLoading');
            let profileDropdownSource = null;
            let scheduleIndex = 1;

            const syncTargetedStep = (stepNumber) => {
                stepPanels.forEach((panel) => {
                    panel.hidden = Number(panel.dataset.waTargetedPanel) !== stepNumber;
                });

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
                        stepNumber === 2 ? 'Atur Profil Penerima' :
                        stepNumber === 3 ? 'Atur Pengiriman' :
                        stepNumber === 4 ? 'Review & Pembayaran' :
                        'Template Pesan';
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
                                <span class="campaign-multiselect__chip-remove">&times;</span>
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

                    optionsWrap.addEventListener('click', (event) => {
                        const allButton = event.target.closest('[data-option-all]');
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

                        const optionButton = event.target.closest('[data-option-value]');
                        if (!optionButton) {
                            return;
                        }

                        const option = Array.from(select.options).find((item) => item.value === optionButton.dataset.optionValue);
                        if (!option) {
                            return;
                        }

                        option.selected = !option.selected;
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        render(search.value);
                    });

                    chips.addEventListener('click', (event) => {
                        const chip = event.target.closest('[data-chip-value]');
                        if (!chip) {
                            return;
                        }

                        const option = Array.from(select.options).find((item) => item.value === chip.dataset.chipValue);
                        if (!option) {
                            return;
                        }

                        option.selected = false;
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        render(search.value);
                    });

                    document.addEventListener('click', (event) => {
                        if (!wrapper.contains(event.target)) {
                            toggleDropdown(false);
                        }
                    });

                    control.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            toggleDropdown(true);
                        }
                    });
                });
            };

            const hasTargetedProfileFilters = () => targetedProfileFields.some((field) => Array.from(field?.selectedOptions || []).some((option) => option.value.trim() !== ''));

            const escapeHtml = (value) => String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const normalizeProfileDropdownOptions = (items) => {
                if (!Array.isArray(items)) {
                    return [];
                }

                return items.map((item) => {
                    if (item === null || item === undefined) {
                        return null;
                    }

                    if (typeof item !== 'object') {
                        const label = String(item);
                        return { value: label, label };
                    }

                    const value = item.value ?? item.id ?? item.code ?? item.key ?? item.name ?? item.label ?? item.text;
                    const label = item.label ?? item.name ?? item.text ?? item.desc ?? item.description ?? value;

                    if (value === null || value === undefined || label === null || label === undefined) {
                        return null;
                    }

                    return {
                        value: String(value),
                        label: String(label),
                    };
                }).filter(Boolean);
            };

            const findProfileDropdownOptions = (source, keys) => {
                const normalizedKeys = keys.map((key) => key.toLowerCase());
                const matchesProfileKey = (item) => {
                    if (!item || typeof item !== 'object') {
                        return false;
                    }

                    const tokens = [item.profile, item.profileType, item.type, item.category, item.group, item.field, item.key]
                        .filter((token) => token !== null && token !== undefined)
                        .map((token) => String(token).toLowerCase());

                    return tokens.some((token) => normalizedKeys.some((key) => token.includes(key) || key.includes(token)));
                };
                const queue = [source];

                while (queue.length > 0) {
                    const current = queue.shift();

                    if (!current || typeof current !== 'object') {
                        continue;
                    }

                    if (Array.isArray(current)) {
                        const groupedOptions = normalizeProfileDropdownOptions(current.filter(matchesProfileKey));
                        if (groupedOptions.length > 0) {
                            return groupedOptions;
                        }

                        queue.push(...current);
                        continue;
                    }

                    for (const [key, value] of Object.entries(current)) {
                        if (normalizedKeys.includes(key.toLowerCase())) {
                            const options = normalizeProfileDropdownOptions(Array.isArray(value) ? value : Object.values(value || {}));
                            if (options.length > 0) {
                                return options;
                            }
                        }

                        if (value && typeof value === 'object') {
                            queue.push(value);
                        }
                    }
                }

                return [];
            };

            const extractProfileOptions = (source, keys) => {
                const keyed = findProfileDropdownOptions(source, keys);
                if (keyed.length > 0) {
                    return keyed;
                }

                const candidates = [
                    source?.data?.data,
                    source?.data?.items,
                    source?.data?.list,
                    source?.data,
                    source?.items,
                    source?.list,
                    source,
                ];

                for (const candidate of candidates) {
                    const options = normalizeProfileDropdownOptions(
                        Array.isArray(candidate)
                            ? candidate
                            : (candidate && typeof candidate === 'object' ? Object.values(candidate) : [])
                    );
                    if (options.length > 0) {
                        return options;
                    }
                }

                return [];
            };

            const fetchProfileDropdownOptions = async (params, keys) => {
                if (!profileDropdownUrl) {
                    return [];
                }

                const url = new URL(profileDropdownUrl, window.location.origin);
                Object.entries(params).forEach(([key, value]) => {
                    if (value) {
                        url.searchParams.set(key, value);
                    }
                });

                const response = await fetch(url.toString(), {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                if (!response.ok) {
                    return [];
                }

                const payload = await response.json();
                const source = payload?.data?.data ?? payload?.data ?? payload;
                return extractProfileOptions(source, keys);
            };

            const setProfileDropdownOptions = (fieldId, options) => {
                const field = document.getElementById(fieldId);
                if (!field || options.length === 0) {
                    return;
                }

                const multiselectWrapper = field.nextElementSibling?.classList?.contains('campaign-multiselect')
                    ? field.nextElementSibling
                    : null;
                multiselectWrapper?.remove();
                delete field.dataset.enhanced;
                field.classList.remove('campaign-multiselect__native');

                field.innerHTML = '';
                options.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = item.value;
                    option.textContent = item.label;
                    field.appendChild(option);
                });
            };

            const loadProfileDropdownOptions = async () => {
                if (!profileDropdownUrl) {
                    if (waTargetedProfileLoading) {
                        waTargetedProfileLoading.hidden = true;
                    }
                    return;
                }

                const dropdownMap = {
                    waTargetedGender: ['gender', 'profileGender', 'jenisKelamin'],
                    waTargetedAge: ['age', 'profileAge', 'rentangUmur'],
                    waTargetedOs: ['os', 'profileOs', 'operatingSystem'],
                    waTargetedDataPackage: ['dataPackage', 'packageData', 'paketData'],
                    waTargetedHouseholdSpend: ['householdSpend', 'householdExpense', 'pengeluaranRumahTangga'],
                    waTargetedCardType: ['cardType', 'profileCardType', 'card_type'],
                    waTargetedMaritalStatus: ['maritalStatus', 'marriage', 'statusPernikahan'],
                    waTargetedLifestyle: ['lifestyleseg', 'lifestyle', 'lifestyleSegment', 'profileLifestyle', 'profileLifestyleSegment'],
                    waTargetedReligion: ['religion', 'religions', 'profileReligion', 'agama'],
                };

                try {
                    const response = await fetch(profileDropdownUrl, {
                        headers: {
                            Accept: 'application/json',
                        },
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    const source = payload?.data?.data ?? payload?.data ?? payload;
                    profileDropdownSource = source;

                    Object.entries(dropdownMap).forEach(([fieldId, keys]) => {
                        setProfileDropdownOptions(fieldId, findProfileDropdownOptions(source, keys));
                    });
                } catch (error) {
                    console.warn('Profile dropdown API gagal dimuat', error);
                } finally {
                    if (waTargetedProfileLoading) {
                        waTargetedProfileLoading.hidden = true;
                    }
                }
            };

            const buildGatewaySelectOptions = (keys, placeholder, fallbackOptions = []) => {
                const options = findProfileDropdownOptions(profileDropdownSource, keys);
                const sourceOptions = options.length > 0
                    ? options
                    : fallbackOptions.map((item) => ({ value: item, label: item }));

                return [
                    `<option value="">${escapeHtml(placeholder)}</option>`,
                    ...sourceOptions.map((option) => `<option value="${escapeHtml(option.value)}">${escapeHtml(option.label)}</option>`),
                ].join('');
            };

            const syncTargetedProfileBuilder = () => {
                if (!targetedProfileBuilder || !targetedRecipient) {
                    return;
                }

                const shouldShow = targetedRecipient.value === 'profile-recipient';
                targetedProfileBuilder.hidden = !shouldShow;
                targetedProfileBuilder.classList.toggle('campaign-targeted-profile-builder--active', shouldShow);
            };

            const syncStepTwoValidity = () => {
                if (stepTwoNext) {
                    stepTwoNext.disabled = !targetedRecipient?.value;
                }
            };

            const locationOptionsTemplate = (type = 'home') => `
                <div class="campaign-targeted-location-grid">
                    <div class="field-group campaign-field-group--compact">
                        <label class="field-label">Provinsi</label>
                        <select class="text-input text-input--select" data-location-select="province">
                            ${buildGatewaySelectOptions(type === 'work' ? ['work_province', 'workProvince', 'profileWorkProvince'] : ['province', 'home_province', 'homeProvince', 'profileProvince'], 'Provinsi', ['DKI Jakarta', 'Jawa Barat', 'Banten', 'Jawa Tengah'])}
                        </select>
                    </div>
                    <div class="field-group campaign-field-group--compact">
                        <label class="field-label">Kota</label>
                        <select class="text-input text-input--select" data-location-select="city">
                            <option value="">Kota</option>
                        </select>
                    </div>
                    <div class="field-group campaign-field-group--compact">
                        <label class="field-label">Kecamatan</label>
                        <select class="text-input text-input--select" data-location-select="district">
                            <option value="">Kecamatan</option>
                        </select>
                    </div>
                    <div class="field-group campaign-field-group--compact">
                        <label class="field-label">Kelurahan</label>
                        <select class="text-input text-input--select" data-location-select="subdistrict">
                            <option value="">Kelurahan</option>
                        </select>
                    </div>
                </div>
                <button type="button" class="campaign-targeted-location-card__remove" data-remove-location>Hapus</button>
            `;

            const setLocationSelectOptions = (select, options, placeholder) => {
                if (!select) {
                    return;
                }

                select.innerHTML = [
                    `<option value="">${escapeHtml(placeholder)}</option>`,
                    ...options.map((option) => `<option value="${escapeHtml(option.value)}">${escapeHtml(option.label)}</option>`),
                ].join('');
            };

            const syncLocationDependentOptions = async (card, changedField) => {
                const province = card.querySelector('[data-location-select="province"]');
                const city = card.querySelector('[data-location-select="city"]');
                const district = card.querySelector('[data-location-select="district"]');
                const subdistrict = card.querySelector('[data-location-select="subdistrict"]');

                if (changedField === 'province') {
                    setLocationSelectOptions(city, [], 'Kota');
                    setLocationSelectOptions(district, [], 'Kecamatan');
                    setLocationSelectOptions(subdistrict, [], 'Kelurahan');

                    const provinceValue = province?.value || '';
                    if (provinceValue) {
                        const options = await fetchProfileDropdownOptions({ profile: 'KOTA', province: provinceValue }, ['kota', 'city']);
                        setLocationSelectOptions(city, options, 'Kota');
                    }
                    updateLocationRowValue(card);
                    return;
                }

                if (changedField === 'city') {
                    setLocationSelectOptions(district, [], 'Kecamatan');
                    setLocationSelectOptions(subdistrict, [], 'Kelurahan');

                    const provinceValue = province?.value || '';
                    const cityValue = city?.value || '';
                    if (provinceValue && cityValue) {
                        const options = await fetchProfileDropdownOptions({ profile: 'KECAMATAN', province: provinceValue, kota: cityValue }, ['kecamatan', 'district']);
                        setLocationSelectOptions(district, options, 'Kecamatan');
                    }
                    updateLocationRowValue(card);
                    return;
                }

                if (changedField === 'district') {
                    setLocationSelectOptions(subdistrict, [], 'Kelurahan');

                    const provinceValue = province?.value || '';
                    const cityValue = city?.value || '';
                    const districtValue = district?.value || '';
                    if (provinceValue && cityValue && districtValue) {
                        const options = await fetchProfileDropdownOptions({ profile: 'KELURAHAN', province: provinceValue, kota: cityValue, kecamatan: districtValue }, ['kelurahan', 'subdistrict']);
                        setLocationSelectOptions(subdistrict, options, 'Kelurahan');
                    }
                    updateLocationRowValue(card);
                }
            };

            const getLocationRowValue = (card) => {
                const subdistrict = card.querySelector('[data-location-select="subdistrict"]')?.value || '';
                const district = card.querySelector('[data-location-select="district"]')?.value || '';
                const city = card.querySelector('[data-location-select="city"]')?.value || '';
                const province = card.querySelector('[data-location-select="province"]')?.value || '';
                return subdistrict || district || city || province;
            };

            const updateLocationRowValue = (card) => {
                card.dataset.locationValue = getLocationRowValue(card);
            };

            const bindTargetedLocationRow = (card) => {
                card.addEventListener('change', (event) => {
                    const select = event.target.closest('[data-location-select]');
                    if (!select) {
                        return;
                    }

                    syncLocationDependentOptions(card, select.dataset.locationSelect);
                    updateLocationRowValue(card);
                });
            };

            const syncTargetedLocationRemoveState = (list) => {
                const items = list?.querySelectorAll('[data-location-row]') || [];
                items.forEach((item) => {
                    const removeButton = item.querySelector('[data-remove-location]');
                    if (removeButton) {
                        removeButton.hidden = false;
                    }
                });
            };

            const addTargetedLocationRow = (type) => {
                const list = type === 'home' ? homeLocationList : workLocationList;
                if (!list) {
                    return;
                }

                const card = document.createElement('article');
                card.className = 'campaign-targeted-location-card';
                card.dataset.locationRow = type;
                card.innerHTML = locationOptionsTemplate(type);
                list.appendChild(card);
                bindTargetedLocationRow(card);
                syncTargetedLocationRemoveState(list);
                syncStepTwoValidity();
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

            const buildMinuteOptions = (selectedValue) => Array.from({ length: 60 }, (_, minute) => String(minute).padStart(2, '0'))
                .map((value) => `<option value="${value}"${value === selectedValue ? ' selected' : ''}>${value}</option>`)
                .join('');

            const formatScheduleDate = (value) => {
                if (!value) {
                    return '--/--/--';
                }

                const [year, month, day] = value.split('-');
                return `${day}/${month}/${year.slice(-2)}`;
            };

            const todayDateValue = () => {
                const now = new Date();
                const pad = (value) => String(value).padStart(2, '0');
                return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
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
                const minDate = todayDateValue();
                const dateStart = document.querySelector(`[data-wa-targeted-schedule-date-start="${scheduleId}"]`);
                const dateEnd = document.querySelector(`[data-wa-targeted-schedule-date-end="${scheduleId}"]`);

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

            const initScheduleTimeSelects = (scheduleId, startValue = '09:30', endValue = '10:00') => {
                const [startHour, startMinute] = startValue.split(':');
                const [endHour, endMinute] = endValue.split(':');
                const timeStartHour = document.querySelector(`[data-wa-targeted-schedule-time-start-hour="${scheduleId}"]`);
                const timeStartMinute = document.querySelector(`[data-wa-targeted-schedule-time-start-minute="${scheduleId}"]`);
                const timeEndHour = document.querySelector(`[data-wa-targeted-schedule-time-end-hour="${scheduleId}"]`);
                const timeEndMinute = document.querySelector(`[data-wa-targeted-schedule-time-end-minute="${scheduleId}"]`);

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

            const syncSchedulePreview = (scheduleId) => {
                const dateStart = document.querySelector(`[data-wa-targeted-schedule-date-start="${scheduleId}"]`);
                const dateEnd = document.querySelector(`[data-wa-targeted-schedule-date-end="${scheduleId}"]`);
                const timeStartHour = document.querySelector(`[data-wa-targeted-schedule-time-start-hour="${scheduleId}"]`);
                const timeStartMinute = document.querySelector(`[data-wa-targeted-schedule-time-start-minute="${scheduleId}"]`);
                const timeEndHour = document.querySelector(`[data-wa-targeted-schedule-time-end-hour="${scheduleId}"]`);
                const timeEndMinute = document.querySelector(`[data-wa-targeted-schedule-time-end-minute="${scheduleId}"]`);
                const datePreview = document.getElementById(`waTargetedScheduleDatePreview${scheduleId}`);
                const timePreview = document.getElementById(`waTargetedScheduleTimePreview${scheduleId}`);

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

            const toggleTimePicker = (scheduleId, forceState = null) => {
                document.querySelectorAll('[data-wa-targeted-time-picker]').forEach((picker) => {
                    const isTarget = picker.dataset.waTargetedTimePicker === String(scheduleId);
                    const panel = picker.querySelector('[data-wa-targeted-time-panel]');
                    const nextState = isTarget ? (forceState === null ? panel.hidden : forceState) : false;
                    picker.classList.toggle('campaign-time-picker--open', !!nextState);
                    if (panel) {
                        panel.hidden = !nextState;
                    }
                });
            };

            const syncScheduleRemoveState = () => {
                const schedules = scheduleList?.querySelectorAll('[data-wa-targeted-schedule-item]') || [];
                schedules.forEach((item, index) => {
                    const title = item.querySelector('.campaign-schedule-card__header span');
                    const removeButton = item.querySelector('[data-wa-targeted-remove-schedule]');
                    if (title) {
                        title.textContent = `Jadwal Pengiriman ${index + 1}`;
                    }
                    if (removeButton) {
                        removeButton.hidden = schedules.length <= 1;
                    }
                });
            };

            const getScheduleDurationMinutes = (scheduleId) => {
                const startDate = document.querySelector(`[data-wa-targeted-schedule-date-start="${scheduleId}"]`)?.value || '';
                const endDate = document.querySelector(`[data-wa-targeted-schedule-date-end="${scheduleId}"]`)?.value || '';
                const startHour = Number(document.querySelector(`[data-wa-targeted-schedule-time-start-hour="${scheduleId}"]`)?.value || NaN);
                const startMinute = Number(document.querySelector(`[data-wa-targeted-schedule-time-start-minute="${scheduleId}"]`)?.value || NaN);
                const endHour = Number(document.querySelector(`[data-wa-targeted-schedule-time-end-hour="${scheduleId}"]`)?.value || NaN);
                const endMinute = Number(document.querySelector(`[data-wa-targeted-schedule-time-end-minute="${scheduleId}"]`)?.value || NaN);

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
                const startDate = document.querySelector(`[data-wa-targeted-schedule-date-start="${scheduleId}"]`)?.value || '';
                const startHour = Number(document.querySelector(`[data-wa-targeted-schedule-time-start-hour="${scheduleId}"]`)?.value || NaN);
                const startMinute = Number(document.querySelector(`[data-wa-targeted-schedule-time-start-minute="${scheduleId}"]`)?.value || NaN);

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
                const startHour = Number(document.querySelector(`[data-wa-targeted-schedule-time-start-hour="${scheduleId}"]`)?.value || NaN);
                const startMinute = Number(document.querySelector(`[data-wa-targeted-schedule-time-start-minute="${scheduleId}"]`)?.value || NaN);
                const endHour = Number(document.querySelector(`[data-wa-targeted-schedule-time-end-hour="${scheduleId}"]`)?.value || NaN);
                const endMinute = Number(document.querySelector(`[data-wa-targeted-schedule-time-end-minute="${scheduleId}"]`)?.value || NaN);

                if ([startHour, startMinute, endHour, endMinute].some(Number.isNaN)) {
                    return false;
                }

                return (startHour * 60 + startMinute) < (endHour * 60 + endMinute);
            };

            const syncRecipientScheduleValidity = () => {
                const recipientCount = Number(recipientCountInput?.value || 0);
                const isRecipientValid = recipientCount >= 5;
                const recipientAlert = document.querySelector('[data-wa-targeted-recipient-count-alert]');

                if (recipientAlert) {
                    recipientAlert.hidden = isRecipientValid;
                }

                let isScheduleValid = true;
                document.querySelectorAll('[data-wa-targeted-schedule-item]').forEach((item) => {
                    const scheduleId = item.dataset.waTargetedScheduleItem;
                    const alert = item.querySelector('[data-wa-targeted-schedule-duration-alert]');
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

                return isRecipientValid && isScheduleValid;
            };

            const syncStepThreeValidity = () => {
                if (stepThreeNext) {
                    const isRecipientScheduleValid = syncRecipientScheduleValidity();
                    stepThreeNext.disabled = !termsCheck?.checked || !isRecipientScheduleValid;
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

            const selectedValues = (selectId) => Array.from(document.getElementById(selectId)?.selectedOptions || [])
                .map((option) => option.value)
                .filter(Boolean);

            const pipeValues = (selectId, map = {}) => selectedValues(selectId)
                .map((value) => map[value] ?? value)
                .filter((value) => value !== '')
                .join('|');

            const setHiddenValue = (id, value) => {
                const input = document.getElementById(id);
                if (input) {
                    input.value = String(value ?? '');
                }
            };

            const locationRowsValue = (list) => Array.from(list?.querySelectorAll('[data-location-row]') || [])
                .map((card) => getLocationRowValue(card))
                .filter(Boolean)
                .join('|');

            const sleep = (ms) => new Promise((resolve) => window.setTimeout(resolve, ms));

            const setPaymentError = (message = '') => {
                if (!waTargetedPaymentError) {
                    return;
                }

                waTargetedPaymentError.textContent = message;
                waTargetedPaymentError.hidden = message === '';
            };

            const normalizePaymentErrorMessage = (message = '') => {
                return String(message).toLowerCase().includes('unauthorized')
                    ? 'Session expired. Silakan login ulang.'
                    : (message || 'Gagal memproses pembayaran.');
            };

            const getPaymentReadinessMessage = () => {
                const recipientCount = Number(recipientCountInput?.value || 0);
                const selectedTemplate = templates.find((template) => String(template.id) === String(templateSelect?.value));

                if (!selectedTemplate) {
                    return 'Pilih template pesan terlebih dahulu.';
                }
                if (!recipientCount || recipientCount < 5) {
                    return 'Jumlah penerima minimal 5 pesan.';
                }
                if (!testRecipientField?.value) {
                    return 'Tambahkan nomor test iklan terlebih dahulu.';
                }
                if (!syncRecipientScheduleValidity()) {
                    return 'Lengkapi jadwal pengiriman minimal 4 jam per hari.';
                }
                if (!termsCheck?.checked) {
                    return 'Centang persetujuan Syarat dan Ketentuan terlebih dahulu.';
                }

                return '';
            };

            const syncPaymentSubmitState = () => {
                const submitButton = document.getElementById('waTargetedGwSubmitButton');
                if (!submitButton) {
                    return;
                }

                const message = getPaymentReadinessMessage();
                submitButton.disabled = message !== '';
                submitButton.title = message;
                if (waTargetedPaymentHint) {
                    waTargetedPaymentHint.textContent = message;
                    waTargetedPaymentHint.hidden = message === '';
                }
            };

            const setPaymentUi = ({ status, transactionId, qrisUrl, forceSuccess } = {}) => {
                const paymentPanel = document.getElementById('waTargetedPaymentPanel');
                const paymentStatus = document.getElementById('waTargetedPaymentStatus');
                const paymentTransaction = document.getElementById('waTargetedPaymentTransaction');
                const paymentQr = document.getElementById('waTargetedPaymentQr');
                const paymentQrLink = document.getElementById('waTargetedPaymentQrLink');
                const paymentModal = paymentPanel?.closest('.campaign-payment-modal');
                const paymentQrDownload = paymentModal?.querySelector('[download]');
                const qrisPanel = document.getElementById('waTargetedPaymentPanel');
                const successPanel = document.getElementById('waTargetedPaymentPanelSuccess');
                const failurePanel = document.getElementById('waTargetedPaymentPanelFailure');

                if (paymentModal) {
                    paymentModal.hidden = false;
                    paymentModal.setAttribute('aria-hidden', 'false');
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
                        const msgEl = document.getElementById('waTargetedPaymentPanelFailureMsg');
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

                if (paymentStatus) {
                    paymentStatus.textContent = status || '-';
                }
                if (paymentTransaction) {
                    paymentTransaction.textContent = transactionId || '-';
                }
                if (paymentQr && qrisUrl) {
                    const cleanedUrl = qrisUrl.replace(/\\/g, '');
                    if (paymentQr.getAttribute('data-src-raw') !== cleanedUrl) {
                        paymentQr.src = qrisDownloadUrl + '?url=' + encodeURIComponent(cleanedUrl) + '&disposition=inline';
                        paymentQr.setAttribute('data-src-raw', cleanedUrl);
                    }
                }
                if (paymentQrLink) {
                    if (qrisUrl) {
                        const cleanedUrl = qrisUrl.replace(/\\/g, '');
                        paymentQrLink.href = qrisDownloadUrl + '?url=' + encodeURIComponent(cleanedUrl) + '&disposition=inline';
                        paymentQrLink.hidden = false;
                    }
                }
                if (paymentQrDownload) {
                    if (qrisUrl) {
                        const cleanedUrl = qrisUrl.replace(/\\/g, '');
                        paymentQrDownload.href = qrisDownloadUrl + '?url=' + encodeURIComponent(cleanedUrl) + '&filename=' + encodeURIComponent(`qris-${transactionId || 'payment'}.png`);
                        paymentQrDownload.download = `qris-${transactionId || 'payment'}.png`;
                        paymentQrDownload.hidden = false;
                    }
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
                            const successBtn = document.getElementById('waTargetedPaymentPanelSuccessBtn');
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
                if (!waTargetedPaymentInvoiceEmailCheck?.checked) {
                    return defaultInvoiceEmail;
                }

                const email = waTargetedPaymentInvoiceEmail?.value?.trim() || '';
                if (!email || !waTargetedPaymentInvoiceEmail?.checkValidity()) {
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

            const submitWabaTargetedAfterPayment = async () => {
                const submitButton = document.getElementById('waTargetedGwSubmitButton');
                const submitForm = document.getElementById('waTargetedGwSubmitForm');
                const paymentTransactionId = document.getElementById('waTargetedPaymentTransactionId');

                if (!submitButton || !submitForm || !paymentTransactionId) {
                    return;
                }

                setPaymentError('');

                if (!testRecipientField?.value && testRecipientInput?.value?.trim()) {
                    addTestRecipient();
                }

                const campaignName = String(document.getElementById('waTargetedCampaignName')?.textContent || '').trim();
                const recipientCount = Number(recipientCountInput?.value || 0);
                const controlNumbers = String(testRecipientField?.value || '').trim();
                const phone = paymentCustomerPhone;
                const selectedTemplate = templates.find((template) => String(template.id) === String(templateSelect?.value));
                const templateId = Number(selectedTemplate?.id || templateSelect?.value || 0);
                const totalCost = recipientCount * waTargetedUnitPrice;
                const scheduleId = '1';
                const deliveryDate = document.querySelector(`[data-wa-targeted-schedule-date-start="${scheduleId}"]`)?.value || '';
                const startHour = document.querySelector(`[data-wa-targeted-schedule-time-start-hour="${scheduleId}"]`)?.value || '';
                const startMinute = document.querySelector(`[data-wa-targeted-schedule-time-start-minute="${scheduleId}"]`)?.value || '';

                if (!campaignName) {
                    throw new Error('Nama iklan belum tersedia.');
                }
                if (!templateId) {
                    throw new Error('Template pesan belum dipilih.');
                }
                if (!phone) {
                    throw new Error('Nomor penerima tes belum diisi.');
                }
                if (!deliveryDate || !startHour || !startMinute) {
                    throw new Error('Jadwal pengiriman belum lengkap.');
                }
                if (!totalCost || totalCost <= 0) {
                    throw new Error('Total biaya tidak valid.');
                }

                submitButton.disabled = true;
                submitButton.classList.add('is-loading');
                submitButton.textContent = 'Menyiapkan pembayaran...';

                const invoiceEmail = resolvePaymentInvoiceEmail();
                const initiateResp = await initiatePayment(totalCost, phone, `WABA Targeted - ${campaignName}`);
                const transactionId = String(initiateResp?.data?.transaction_id || '').trim();
                const qrisUrl = String(initiateResp?.data?.qris_url || '').trim();

                if (!transactionId) {
                    throw new Error('Transaction ID tidak ditemukan dari response pembayaran.');
                }

                paymentTransactionId.value = transactionId;
                setPaymentUi({ status: 'PENDING', transactionId, qrisUrl });
                submitButton.textContent = 'Menunggu pembayaran...';

                await pollPaymentUntilSuccess(transactionId);

                submitButton.textContent = 'Pembayaran sukses. Mengirim campaign...';

                setHiddenValue('waTargetedGwCampaignName', campaignName);
                setHiddenValue('waTargetedGwTemplateId', templateId);
                setHiddenValue('waTargetedGwRecipientTotal', recipientCount);
                setHiddenValue('waTargetedGwDeliveryDate', deliveryDate);
                setHiddenValue('waTargetedGwDeliveryTime', `${startHour}:${startMinute}`);
                setHiddenValue('waTargetedGwControlNumber', controlNumbers);
                setHiddenValue('waTargetedGwCustomerEmail', invoiceEmail);
                setHiddenValue('waTargetedGwProfileGender', pipeValues('waTargetedGender', { M: 'm', F: 'f' }));
                setHiddenValue('waTargetedGwProfileAge', pipeValues('waTargetedAge'));
                setHiddenValue('waTargetedGwProfileMobileDataSubs', pipeValues('waTargetedDataPackage'));
                setHiddenValue('waTargetedGwProfileArpu', pipeValues('waTargetedHouseholdSpend'));
                setHiddenValue('waTargetedGwProfileOs', pipeValues('waTargetedOs'));
                setHiddenValue('waTargetedGwProfileMaritalStatus', pipeValues('waTargetedMaritalStatus'));
                setHiddenValue('waTargetedGwProfileLifestyleSegment', pipeValues('waTargetedLifestyle'));
                setHiddenValue('waTargetedGwProfileReligion', pipeValues('waTargetedReligion'));
                setHiddenValue('waTargetedGwProfileCardType', pipeValues('waTargetedCardType'));
                setHiddenValue('waTargetedGwProfileHometown', locationRowsValue(homeLocationList));
                setHiddenValue('waTargetedGwProfileWork', locationRowsValue(workLocationList));

                submitForm.submit();
            };

            const collapseReviewCards = () => {
                document.querySelectorAll('[data-wa-targeted-review-card]').forEach((card) => {
                    card.classList.remove('campaign-review-card--open');
                });

                document.querySelectorAll('[data-wa-targeted-review-body]').forEach((body) => {
                    body.hidden = true;
                });

                document.querySelectorAll('[data-wa-targeted-review-toggle]').forEach((toggle) => {
                    toggle.textContent = 'Tampilkan';
                });
            };

            const syncReviewSummary = () => {
                const selected = templates.find((template) => String(template.id) === String(templateSelect?.value));
                const recipientCount = Number(recipientCountInput?.value || 0);
                const totalCost = recipientCount * 1100;

                const setText = (id, value) => {
                    const target = document.getElementById(id);
                    if (target) {
                        target.textContent = value;
                    }
                };

                setText('waTargetedReviewTitle', String(document.getElementById('waTargetedCampaignName')?.textContent || '').trim() || 'Tanpa Judul');
                setText('waTargetedReviewTemplateName', selected?.name || '-');
                setText('waTargetedReviewSender', selected?.account_name || '-');
                setText('waTargetedReviewMessage', `${selected?.bubble_headline || ''}\n\n${selected?.bubble_message || '-'}`);
                setText('waTargetedReviewGender', getSelectedTexts('waTargetedGender'));
                setText('waTargetedReviewAge', getSelectedTexts('waTargetedAge'));
                setText('waTargetedReviewReligion', getSelectedTexts('waTargetedReligion'));
                setText('waTargetedReviewCardType', getSelectedTexts('waTargetedCardType'));
                setText('waTargetedReviewOs', getSelectedTexts('waTargetedOs'));
                setText('waTargetedReviewLifestyle', getSelectedTexts('waTargetedLifestyle'));
                setText('waTargetedReviewDeliveryMethod', document.getElementById('waTargetedDeliveryMethod')?.selectedOptions?.[0]?.text || 'Pengiriman Secepatnya');
                setText('waTargetedReviewTestRecipient', testRecipientField?.value || 'Belum dipilih');
                setText('waTargetedReviewRecipientCount', `${new Intl.NumberFormat('id-ID').format(recipientCount)} Pesan`);
                setText('waTargetedReviewPotentialRange', document.getElementById('waTargetedEstimate')?.textContent || '-');
                setText('waTargetedReviewGrandTotal', formatCurrency(totalCost));
                setText('waTargetedReviewPayment', formatCurrency(totalCost));

                if (reviewSchedules) {
                    const schedules = Array.from(scheduleList?.querySelectorAll('[data-wa-targeted-schedule-item]') || []).map((item, index) => {
                        const scheduleId = item.dataset.waTargetedScheduleItem;
                        const date = document.getElementById(`waTargetedScheduleDatePreview${scheduleId}`)?.textContent || '-';
                        const time = document.getElementById(`waTargetedScheduleTimePreview${scheduleId}`)?.textContent || '-';

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
                if (!testRecipientField) {
                    return;
                }

                testRecipientField.value = getSelectedTestRecipients().join(',');
            };

            const renderTestRecipientItem = (number) => {
                if (!testRecipientList || !testRecipientField) {
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
                item.dataset.waTargetedScheduleItem = String(scheduleIndex);
                item.innerHTML = `
                    <div class="campaign-schedule-card__header">
                        <span>Jadwal Pengiriman ${scheduleIndex}</span>
                        <button type="button" class="campaign-schedule-card__remove" data-wa-targeted-remove-schedule>Hapus</button>
                    </div>
                    <div class="campaign-schedule-card__grid">
                        <div class="field-group campaign-field-group--compact">
                            <label class="field-label" for="waTargetedScheduleDate${scheduleIndex}Start">Tgl Kirim</label>
                            <div class="campaign-range-field">
                                <input id="waTargetedScheduleDate${scheduleIndex}Start" type="date" class="text-input" value="${defaults.startDate}" min="${todayDateValue()}" data-wa-targeted-schedule-date-start="${scheduleIndex}">
                                <span class="campaign-range-field__separator">-</span>
                                <input id="waTargetedScheduleDate${scheduleIndex}End" type="date" class="text-input" value="${defaults.endDate}" min="${todayDateValue()}" data-wa-targeted-schedule-date-end="${scheduleIndex}">
                            </div>
                            <p class="campaign-range-preview" id="waTargetedScheduleDatePreview${scheduleIndex}">${formatScheduleDate(defaults.startDate)} - ${formatScheduleDate(defaults.endDate)}</p>
                        </div>
                        <div class="field-group campaign-field-group--compact">
                            <label class="field-label" for="waTargetedScheduleTime${scheduleIndex}">Jam Kirim</label>
                            <div class="campaign-time-picker" data-wa-targeted-time-picker="${scheduleIndex}">
                                <button type="button" class="campaign-time-picker__trigger" data-wa-targeted-time-trigger="${scheduleIndex}">
                                    <span class="campaign-time-picker__value" id="waTargetedScheduleTimePreview${scheduleIndex}">${defaults.startTime}-${defaults.endTime} WIB</span>
                                    <span class="campaign-time-picker__clock">i</span>
                                </button>
                                <div class="campaign-time-picker__panel" data-wa-targeted-time-panel="${scheduleIndex}" hidden>
                                    <div class="campaign-time-picker__columns">
                                        <div class="campaign-time-picker__column">
                                            <span class="campaign-time-picker__label">Jam Mulai</span>
                                            <div class="campaign-time-picker__inputs">
                                                <select id="waTargetedScheduleTime${scheduleIndex}StartHour" class="campaign-time-picker__select" data-wa-targeted-schedule-time-start-hour="${scheduleIndex}"></select>
                                                <span class="campaign-time-picker__colon">:</span>
                                                <select id="waTargetedScheduleTime${scheduleIndex}StartMinute" class="campaign-time-picker__select" data-wa-targeted-schedule-time-start-minute="${scheduleIndex}"></select>
                                                <span class="campaign-time-picker__suffix">WIB</span>
                                            </div>
                                        </div>
                                        <div class="campaign-time-picker__column">
                                            <span class="campaign-time-picker__label">Jam Berakhir</span>
                                            <div class="campaign-time-picker__inputs">
                                                <select id="waTargetedScheduleTime${scheduleIndex}EndHour" class="campaign-time-picker__select" data-wa-targeted-schedule-time-end-hour="${scheduleIndex}"></select>
                                                <span class="campaign-time-picker__colon">:</span>
                                                <select id="waTargetedScheduleTime${scheduleIndex}EndMinute" class="campaign-time-picker__select" data-wa-targeted-schedule-time-end-minute="${scheduleIndex}"></select>
                                                <span class="campaign-time-picker__suffix">WIB</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="campaign-time-picker__hint">Disarankan durasi pengiriman pesan lebih dari 4 jam agar pengiriman lebih optimal</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="campaign-step-two__alert" data-wa-targeted-schedule-duration-alert>Atur jadwal minimal 4 jam/hari untuk memastikan target anda tercapai minimal</p>
                `;

                scheduleList.appendChild(item);
                initScheduleTimeSelects(scheduleIndex, defaults.startTime, defaults.endTime);
                syncSchedulePreview(scheduleIndex);
                syncScheduleRemoveState();
                syncStepThreeValidity();
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
                        <button type="button" class="campaign-wa-lba-template__card-toggle" data-wa-targeted-card-toggle>
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
                    syncTargetedProfileBuilder();
                    syncStepTwoValidity();
                    syncTargetedStep(2);
                    
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
            stepTwoBack?.addEventListener('click', () => syncTargetedStep(1));
            stepTwoNext?.addEventListener('click', () => syncTargetedStep(3));
            stepThreeBack?.addEventListener('click', () => syncTargetedStep(2));
            stepThreeNext?.addEventListener('click', () => {
                syncReviewSummary();
                collapseReviewCards();
                syncPaymentSubmitState();
                syncTargetedStep(4);

                fetch("{{ route('campaign.track-event') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ event_type: 'campaign_delivery_setup' })
                }).catch(err => console.warn('Tracking failed:', err));
            });
            stepFourBack?.addEventListener('click', () => syncTargetedStep(3));
            document.addEventListener('click', (event) => {
                const toggleButton = event.target.closest('[data-wa-targeted-review-toggle]');
                if (toggleButton) {
                    const cardKey = toggleButton.dataset.waTargetedReviewToggle;
                    const card = document.querySelector(`[data-wa-targeted-review-card="${cardKey}"]`);
                    const body = document.querySelector(`[data-wa-targeted-review-body="${cardKey}"]`);
                    const isOpen = card?.classList.contains('campaign-review-card--open');

                    card?.classList.toggle('campaign-review-card--open', !isOpen);
                    if (body) {
                        body.hidden = isOpen;
                    }
                    toggleButton.textContent = isOpen ? 'Tampilkan' : 'Sembunyikan';
                }

                const editButton = event.target.closest('[data-wa-targeted-review-edit]');
                if (editButton) {
                    syncTargetedStep(Number(editButton.dataset.waTargetedReviewEdit));
                }
            });
            targetedRecipient?.addEventListener('change', () => {
                syncTargetedProfileBuilder();
                syncStepTwoValidity();
            });
            targetedProfileFields.forEach((field) => {
                field.addEventListener('change', syncStepTwoValidity);
            });
            addHomeLocationButton?.addEventListener('click', () => addTargetedLocationRow('home'));
            addWorkLocationButton?.addEventListener('click', () => addTargetedLocationRow('work'));
            [homeLocationList, workLocationList].forEach((list) => {
                list?.addEventListener('click', (event) => {
                    const removeButton = event.target.closest('[data-remove-location]');
                    if (!removeButton) {
                        return;
                    }

                    const item = removeButton.closest('[data-location-row]');
                    item?.remove();
                    syncTargetedLocationRemoveState(list);
                    syncStepTwoValidity();
                });
            });
            addScheduleButton?.addEventListener('click', addScheduleItem);
            recipientCountInput?.addEventListener('input', syncStepThreeValidity);
            termsCheck?.addEventListener('change', syncStepThreeValidity);
            testRecipientAdd?.addEventListener('click', addTestRecipient);
            testRecipientInput?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    addTestRecipient();
                }
            });
            scheduleList?.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-wa-targeted-time-trigger]');
                if (trigger) {
                    toggleTimePicker(trigger.dataset.waTargetedTimeTrigger);
                    return;
                }

                const removeButton = event.target.closest('[data-wa-targeted-remove-schedule]');
                if (removeButton) {
                    const item = removeButton.closest('[data-wa-targeted-schedule-item]');
                    item?.remove();
                    syncScheduleRemoveState();
                    syncStepThreeValidity();
                }
            });
            const handleScheduleChange = (event) => {
                const field = event.target.closest('[data-wa-targeted-schedule-date-start], [data-wa-targeted-schedule-date-end], [data-wa-targeted-schedule-time-start-hour], [data-wa-targeted-schedule-time-start-minute], [data-wa-targeted-schedule-time-end-hour], [data-wa-targeted-schedule-time-end-minute]');
                if (!field) {
                    return;
                }

                const scheduleId =
                    field.dataset.waTargetedScheduleDateStart ||
                    field.dataset.waTargetedScheduleDateEnd ||
                    field.dataset.waTargetedScheduleTimeStartHour ||
                    field.dataset.waTargetedScheduleTimeStartMinute ||
                    field.dataset.waTargetedScheduleTimeEndHour ||
                    field.dataset.waTargetedScheduleTimeEndMinute;

                if (scheduleId) {
                    syncSchedulePreview(scheduleId);
                    syncStepThreeValidity();
                }
            };
            scheduleList?.addEventListener('input', handleScheduleChange);
            scheduleList?.addEventListener('change', handleScheduleChange);

            const syncDraftScheduleState = () => {
                document.querySelectorAll('[data-wa-targeted-schedule-date-start]').forEach((field) => {
                    const scheduleId = field.dataset.waTargetedScheduleDateStart;
                    if (scheduleId) {
                        syncSchedulePreview(scheduleId);
                    }
                });
                syncScheduleRemoveState();
                syncStepTwoValidity();
                syncStepThreeValidity();
                syncReviewSummary();
                syncPaymentSubmitState();
            };

            document.addEventListener('campaign:draft-restored', syncDraftScheduleState);

            document.addEventListener('click', (event) => {
                if (!event.target.closest('[data-wa-targeted-time-picker]')) {
                    document.querySelectorAll('[data-wa-targeted-time-picker]').forEach((picker) => {
                        picker.classList.remove('campaign-time-picker--open');
                        const panel = picker.querySelector('[data-wa-targeted-time-panel]');
                        if (panel) {
                            panel.hidden = true;
                        }
                    });
                }
            });
            cardsAccordion?.addEventListener('click', (event) => {
                const toggle = event.target.closest('[data-wa-targeted-card-toggle]');
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
            document.getElementById('waTargetedGwSubmitButton')?.addEventListener('click', async () => {
                const submitButton = document.getElementById('waTargetedGwSubmitButton');

                try {
                    await submitWabaTargetedAfterPayment();
                } catch (error) {
                    const message = normalizePaymentErrorMessage(error?.message ? String(error.message) : 'Gagal mengirim campaign WABA Targeted.');
                    setPaymentUi({ status: 'FAILED' });
                    setPaymentError(message);
                    if (submitButton) {
                        submitButton.classList.remove('is-loading');
                        submitButton.disabled = false;
                        submitButton.textContent = 'Bayar & Kirim Iklan';
                    }
                }
            });

            syncTemplateDisplay();
            loadProfileDropdownOptions().then(() => {
                initCampaignMultiselects();
                syncTargetedProfileBuilder();
                syncStepTwoValidity();
            });
            applyDefaultDeliverySchedule(1);
            syncScheduleRemoveState();
            syncStepThreeValidity();
            syncTargetedStep(1);
            syncPaymentSubmitState();
        });
    </script>
    @include('partials.campaign-draft-state', [
        'storageKey' => "campaign-draft:{$channel}:{$menu}",
    ])
@endpush
