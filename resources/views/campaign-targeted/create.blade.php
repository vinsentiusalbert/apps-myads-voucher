@php
    $title = $page['title'];
    $autoCampaignName = 'gotoads_sms_targeted_' . now()->format('YmdHis');
    $bodyClass = 'campaign-title-ready';
    $mainClass = 'campaign-main';
    $contentClass = 'campaign-content';
    $isTargeted = $menu === 'targeted';
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
    $activeSubnav = $channel === 'sms'
        ? ($isTargeted ? 'sms-targeted' : 'sms-location-based-area')
        : ($isTargeted ? 'wa-targeted' : 'wa-location-based-area');
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
    <section class="campaign-stepper-card">
        <div class="campaign-stepper">
            @foreach ($page['steps'] as $index => $step)
                <div class="campaign-step {{ $index === 0 ? 'campaign-step--active' : '' }}" data-campaign-step="{{ $index + 1 }}">
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

    @if (session('status'))
        <div class="status-box status-box--success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <p class="error-text">{{ $errors->first() }}</p>
    @endif

    <section class="campaign-compose-card" id="campaignComposeCard">
        <div class="campaign-compose-card__header">
            <div>
                <p class="campaign-section-kicker" id="campaignSectionKicker">Step 01</p>
                <h1 id="campaignSectionHeading">Konten Pesan Iklan</h1>
            </div>
        <div class="campaign-title-chip">
            <span>Judul Iklan</span>
            <strong id="campaignTitlePreview">{{ $autoCampaignName }}</strong>
        </div>
        </div>

        <div class="campaign-compose-grid campaign-compose-grid--active" data-campaign-panel="1">
            <div class="campaign-compose-form">
                <div class="campaign-radio-row">
                    <label class="campaign-radio">
                        {{-- <input type="radio" name="compose_type" checked> --}}
                        <span>Isi Konten Pesan Manual</span>
                    </label>
                </div>

                <div class="field-group">
                    <label for="campaign_sender" class="field-label">Nama Pengirim</label>
                    <div class="campaign-inline-field">
                        <input
                            type="text"
                            id="campaign_sender"
                            name="sender_name"
                            form="campaignGwSubmitForm"
                            class="text-input"
                            maxlength="255"
                            required
                        >
                    </div>
                </div>

                {{-- <a href="#" class="campaign-template-link">{{ $isTargeted ? 'Gunakan dari Template' : 'Gunakan dari Template' }}</a> --}}

                <div class="field-group">
                    <label for="campaign_message" class="field-label">Isi Konten</label>
                    <textarea id="campaign_message" name="message" form="campaignGwSubmitForm" class="text-input campaign-message-input"></textarea>
                    <div class="campaign-message-meta">
                        <span>Mohon pastikan pesan iklan Anda tidak mengandung unsur terlarang.</span>
                        <strong id="campaignMessageCount">{{ $isTargeted ? '0/160 karakter (1 SMS)' : '0/160 karakter' }}</strong>
                    </div>
                </div>

                <section class="campaign-block">
                    <div class="campaign-button-builder__head">
                        <div>
                            <h2 class="campaign-block__title">{{ $isTargeted ? 'Tambah Link Singkat' : 'Buttons' }}</h2>
                            <p class="campaign-block__copy">
                                {{ $isTargeted
                                    ? 'Tambahkan call to action ke pesan Anda dan lihat preview-nya langsung di sisi kanan.'
                                    : 'Tambahkan tombol aksi singkat ke pesan dan lihat preview-nya langsung di sisi kanan.' }}
                            </p>
                        </div>
                        <button type="button" class="campaign-outline-button campaign-outline-button--compact" id="campaignAddButton">
                            <span class="campaign-outline-button__icon">+</span>
                            <span>Add Button</span>
                        </button>
                    </div>
                    <p class="campaign-button-builder__warning" id="campaignButtonWarning" hidden>Button hanya bisa 2.</p>

                    <div class="campaign-button-builder" id="campaignButtonBuilder"></div>

                    <p class="campaign-block__copy">
                        Link Singkat adalah halaman situs yang ditampilkan otomatis saat gambar pada iklan diklik oleh penerima. Pastikan Link Singkat yang Anda masukkan benar.
                    </p>
                </section>

                @unless ($isTargeted)
                <section class="campaign-block">
                    <h2 class="campaign-block__title">Atur Lokasi Target</h2>
                    <div class="campaign-location-row campaign-location-row--single">
                        <button type="button" class="campaign-outline-button campaign-outline-button--wide" id="campaignOpenLocationModal" onclick="window.openCampaignLocationModal && window.openCampaignLocationModal()">
                            <span class="campaign-outline-button__icon" id="campaignLocationButtonIcon">+</span>
                            <span class="campaign-location-button__content" id="campaignLocationButtonContent">
                                <span class="campaign-location-button__title">Tambah Lokasi</span>
                            </span>
                        </button>
                    </div>
                </section>

                <section class="campaign-block">
                    <h2 class="campaign-block__title">Atur Profil Target</h2>
                    <p class="campaign-block__copy">
                        Anda dapat membuat target lebih spesifik dengan menentukan profil dan lokasi penerima. Semakin banyak profil dan lokasi yang dipilih akan mengurangi jumlah penerima iklan Anda, sehingga menurunkan jumlah iklan yang ditampilkan pada laman situs yang dikunjungi.
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

                        {{-- <a href="#" class="campaign-inline-link campaign-inline-link--strong campaign-inline-link--arrow">Atur Profil Lebih Spesifik</a> --}}
                    </div>

                    {{-- <a href="#" class="campaign-collapse-link campaign-collapse-link--subtle">
                        <span class="campaign-collapse-link__icon">+</span>
                        <span>Pilih Profil dari Daftar</span>
                    </a> --}}

                    {{-- <a href="#" class="campaign-inline-link campaign-inline-link--arrow campaign-inline-link--stacked">Buat Pesan yang Sama atau Berbeda ke Beberapa Profil dan Lokasi</a> --}}
                </section>
                @endunless

                <div class="campaign-form-actions">
                    {{-- <button type="button" class="campaign-draft-btn">
                        <span class="campaign-draft-btn__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                <path d="M14 3v5h5M9 12h6M9 16h6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>Simpan Iklan Sebagai Draft</span>
                    </button> --}}
                    <button type="button" class="submit-btn lba-primary-btn" id="campaignStepOneNext">Lanjutkan</button>
                </div>
            </div>

            <aside class="campaign-phone-preview">
                <div class="campaign-phone">
                    <div class="campaign-phone__notch"></div>
                    <div class="campaign-phone__top">
                        <span>9:41</span>
                        <span>{{ $channel === 'sms' ? 'SMS' : 'WA' }}</span>
                    </div>
                    <div class="campaign-phone__avatar"></div>
                    @if ($isTargeted)
                    <p class="campaign-phone__sender" id="campaignSenderPreview"></p>
                    @endif
                    <p class="campaign-phone__time">Hari ini 13:59</p>
                    <div class="campaign-phone__sheet">
                        <div class="campaign-phone__bubble" id="campaignMessagePreview">
                            
                        </div>
                        <div class="campaign-phone__actions" id="campaignPhoneActions"></div>
                    </div>
                </div>
            </aside>
        </div>

        <div class="campaign-step-panel" data-campaign-panel="2" hidden>
            <section class="campaign-targeted-step-two">
                <div class="campaign-targeted-step-two__section">
                    <h2 class="campaign-block__title">Target Penerima</h2>
                    <div class="field-group campaign-targeted-step-two__field">
                        <label for="campaignTargetRecipient" class="field-label">Target Penerima</label>
                        <select id="campaignTargetRecipient" class="text-input text-input--select">
                            <option value="profile-recipient" selected>Profil Penerima</option>
                        </select>
                    </div>
                    <div class="campaign-targeted-profile-builder" id="campaignTargetedProfileBuilder" hidden>
                        <div class="campaign-targeted-step-two__saved">
                            <h3>Buat Baru Profil Penerima</h3>
                            <div class="campaign-targeted-profile-grid">
                                <div class="field-group campaign-field-group--compact">
                                    <label for="campaignTargetGender" class="field-label">Jenis Kelamin</label>
                                    <select id="campaignTargetGender" name="profile_gender[]" form="campaignGwSubmitForm" class="campaign-search-multiselect" multiple data-placeholder="Cari jenis kelamin">
                                        <option value="M">Laki-laki</option>
                                        <option value="F">Perempuan</option>
                                    </select>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label for="campaignTargetCardType" class="field-label">Tipe Kartu</label>
                                    <select id="campaignTargetCardType" name="profile_card_type[]" form="campaignGwSubmitForm" class="campaign-search-multiselect" multiple data-placeholder="Cari tipe kartu">
                                        <option value="Telkomsel Halo">Telkomsel Halo</option>
                                        <option value="Telkomsel PraBayar">Telkomsel PraBayar</option>
                                    </select>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label for="campaignTargetAge" class="field-label">Rentang Umur</label>
                                    <select id="campaignTargetAge" name="profile_age[]" form="campaignGwSubmitForm" class="campaign-search-multiselect" multiple data-placeholder="Cari rentang umur">
                                        <option value="under-15">&lt; 15 tahun</option>
                                        <option value="15-24">15 - 24 tahun</option>
                                        <option value="25-34">25 - 34 tahun</option>
                                        <option value="35-44">35 - 44 tahun</option>
                                        <option value="45-plus">45+ tahun</option>
                                    </select>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label for="campaignTargetOs" class="field-label">Tipe OS Handphone</label>
                                    <select id="campaignTargetOs" name="profile_os[]" form="campaignGwSubmitForm" class="campaign-search-multiselect" multiple data-placeholder="Cari tipe OS handphone">
                                        <option value="Android">Android</option>
                                        <option value="iOS">iOS</option>
                                    </select>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label for="campaignTargetDataPackage" class="field-label">Penerima dengan Paket Data</label>
                                    <select id="campaignTargetDataPackage" name="profile_data_package[]" form="campaignGwSubmitForm" class="campaign-search-multiselect" multiple data-placeholder="Cari status paket data">
                                        <option value="Y">Aktif</option>
                                        <option value="N">Tidak Aktif</option>
                                    </select>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label for="campaignTargetHouseholdSpend" class="field-label">Tingkat Pengeluaran Rumah Tangga (per Bulan)</label>
                                    <select id="campaignTargetHouseholdSpend" name="profile_household_spend[]" form="campaignGwSubmitForm" class="campaign-search-multiselect" multiple data-placeholder="Cari tingkat pengeluaran rumah tangga">
                                        <option value="lt-2m">&lt; Rp 2.000.000</option>
                                        <option value="2m-5m">Rp 2.000.000 - Rp 5.000.000</option>
                                        <option value="5m-10m">Rp 5.000.000 - Rp 10.000.000</option>
                                        <option value="gt-10m">&gt; Rp 10.000.000</option>
                                    </select>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label for="campaignTargetMaritalStatus" class="field-label">Status Pernikahan</label>
                                    <select id="campaignTargetMaritalStatus" name="profile_marital_status[]" form="campaignGwSubmitForm" class="campaign-search-multiselect" multiple data-placeholder="Cari status pernikahan">
                                        <option value="SINGLE">Single</option>
                                        <option value="MARRIED">Married</option>
                                    </select>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label for="campaignTargetLifestyle" class="field-label">Lifestyle Segment</label>
                                    <select id="campaignTargetLifestyle" name="profile_lifestyle[]" form="campaignGwSubmitForm" class="campaign-search-multiselect" multiple data-placeholder="Cari lifestyle segment">
                                        <option value="FOODIES">Foodies</option>
                                        <option value="TRAVELER">Traveler</option>
                                        <option value="FAMILY">Family</option>
                                    </select>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label for="campaignTargetReligion" class="field-label">Agama</label>
                                    <select id="campaignTargetReligion" name="profile_religion[]" form="campaignGwSubmitForm" class="campaign-search-multiselect" multiple data-placeholder="Cari agama">
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
                        <a href="#" class="campaign-inline-link campaign-inline-link--strong campaign-inline-link--arrow campaign-inline-link--fit">Gunakan Pengaturan Audience</a>

                        <div class="campaign-targeted-location-group">
                            <div class="campaign-targeted-location-group__head">
                                <h3>Lokasi Tempat Tinggal</h3>
                                <button type="button" class="campaign-inline-action" id="campaignAddHomeLocation">
                                    <span class="campaign-inline-action__icon">+</span>
                                    <span>Tambah Lokasi Tempat Tinggal</span>
                                </button>
                            </div>
                            <div class="campaign-targeted-location-list" id="campaignHomeLocationList">
                            </div>
                        </div>

                        <div class="campaign-targeted-location-group">
                            <div class="campaign-targeted-location-group__head">
                                <h3>Lokasi Tempat Bekerja</h3>
                                <button type="button" class="campaign-inline-action" id="campaignAddWorkLocation">
                                    <span class="campaign-inline-action__icon">+</span>
                                    <span>Tambah Lokasi Tempat Bekerja</span>
                                </button>
                            </div>
                            <div class="campaign-targeted-location-list" id="campaignWorkLocationList">
                            </div>
                        </div>

                        <label class="campaign-terms campaign-terms--compact">
                            <input type="checkbox" id="campaignSaveTargetedProfile">
                            <span>Simpan Pengaturan Profil Penerima</span>
                        </label>

                    </div>
                </div>

                <div class="campaign-form-actions campaign-form-actions--step-two campaign-form-actions--targeted">
                    <button type="button" class="campaign-outline-button campaign-step-nav-btn" id="campaignStepTwoBack">Sebelumnya</button>
                    {{-- <button type="button" class="campaign-draft-btn">
                        <span class="campaign-draft-btn__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                <path d="M14 3v5h5M9 12h6M9 16h6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>Simpan Iklan Sebagai Draft</span>
                    </button> --}}
                    <button type="button" class="submit-btn lba-primary-btn campaign-step-two__next" id="campaignStepTwoNext" disabled>Lanjutkan</button>
                </div>
            </section>
        </div>

        <div class="campaign-step-panel" data-campaign-panel="3" hidden>
            <section class="campaign-step-two">
                <div class="campaign-step-two__section">
                    <h2 class="campaign-block__title">Tentukan Jumlah Penerima yang Akan Dikirim</h2>
                    <div class="campaign-send-grid">
                        <div class="field-group campaign-field-group--compact">
                            <label for="campaignRecipientCount" class="field-label">Jumlah Penerima</label>
                            <input id="campaignRecipientCount" name="recipient_total" form="campaignGwSubmitForm" type="number" class="text-input" min="5" value="5" placeholder="Jumlah Penerima">
                            <p class="campaign-step-two__alert" data-recipient-count-alert>Jumlah penerima minimal 5 perhari</p>
                        </div>
                        <strong id="campaignStepTwoEstimate" hidden>-</strong>
                    </div>
                </div>

                <div class="campaign-step-two__section">
                    <h2 class="campaign-block__title">Jadwal Pengiriman Pesan</h2>
                    <div class="campaign-schedule-list" id="campaignScheduleList">
                        <article class="campaign-schedule-card" data-schedule-item="1">
                            <div class="campaign-schedule-card__header">
                                <span>Jadwal Pengiriman 1</span>
                                <button type="button" class="campaign-schedule-card__remove" data-remove-schedule hidden>Hapus</button>
                            </div>
                            <div class="campaign-schedule-card__grid">
                                <div class="field-group campaign-field-group--compact">
                                    <label class="field-label" for="campaignScheduleDate1">Tgl Kirim</label>
                                    <div class="campaign-range-field">
                                        <input id="campaignScheduleDate1Start" name="delivery_date" form="campaignGwSubmitForm" type="date" class="text-input" value="2026-04-24" data-schedule-date-start="1">
                                        <span class="campaign-range-field__separator">-</span>
                                        <input id="campaignScheduleDate1End" type="date" class="text-input" value="{{ now()->toDateString() }}" data-schedule-date-end="1">
                                    </div>
                                    <p class="campaign-range-preview" id="campaignScheduleDatePreview1">{{ now()->format('d/m/y') }} - {{ now()->format('d/m/y') }}</p>
                                </div>
                                <div class="field-group campaign-field-group--compact">
                                    <label class="field-label" for="campaignScheduleTime1">Jam Kirim</label>
                                    <div class="campaign-time-picker" data-time-picker="1">
                                        <button type="button" class="campaign-time-picker__trigger" data-time-trigger="1">
                                            <span class="campaign-time-picker__value" id="campaignScheduleTimePreview1">09:30-10:00 WIB</span>
                                            <span class="campaign-time-picker__clock">◷</span>
                                        </button>
                                        <div class="campaign-time-picker__panel" data-time-panel="1" hidden>
                                            <div class="campaign-time-picker__columns">
                                                <div class="campaign-time-picker__column">
                                                    <span class="campaign-time-picker__label">Jam Mulai</span>
                                                    <div class="campaign-time-picker__inputs">
                                                        <select id="campaignScheduleTime1StartHour" class="campaign-time-picker__select" data-schedule-time-start-hour="1"></select>
                                                        <span class="campaign-time-picker__colon">:</span>
                                                        <select id="campaignScheduleTime1StartMinute" class="campaign-time-picker__select" data-schedule-time-start-minute="1"></select>
                                                        <span class="campaign-time-picker__suffix">WIB</span>
                                                    </div>
                                                </div>
                                                <div class="campaign-time-picker__column">
                                                    <span class="campaign-time-picker__label">Jam Berakhir</span>
                                                    <div class="campaign-time-picker__inputs">
                                                        <select id="campaignScheduleTime1EndHour" class="campaign-time-picker__select" data-schedule-time-end-hour="1"></select>
                                                        <span class="campaign-time-picker__colon">:</span>
                                                        <select id="campaignScheduleTime1EndMinute" class="campaign-time-picker__select" data-schedule-time-end-minute="1"></select>
                                                        <span class="campaign-time-picker__suffix">WIB</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="campaign-time-picker__hint">Disarankan durasi pengiriman pesan lebih dari 4 jam agar pengiriman lebih optimal</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="campaign-step-two__alert" data-schedule-duration-alert>Atur jadwal minimal 4 jam/hari untuk memastikan target anda tercapai minimal</p>
                        </article>
                    </div>
                    <div class="campaign-schedule-actions">
                        <div class="campaign-schedule-divider"></div>
                        <button type="button" class="campaign-inline-action" id="campaignAddSchedule">
                            <span class="campaign-inline-action__icon">+</span>
                            <span>Tambah Jadwal Pengirim</span>
                        </button>
                    </div>
                </div>

                <div class="campaign-step-two__section">
                    <div class="field-group">
                        <label for="campaignDeliveryMethod" class="field-label">Metode Pengiriman Pesan</label>
                        <select id="campaignDeliveryMethod" class="text-input text-input--select">
                            <option>Metode Pengiriman Pesan</option>
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
                    <div class="campaign-test-recipient" id="campaignTestRecipientBuilder">
                        <input type="hidden" id="campaignTestRecipient" name="control_number" form="campaignGwSubmitForm" value="">
                        <fieldset class="campaign-test-recipient__box">
                            <legend>Nomor Penerima Tes Iklan</legend>
                            <div class="campaign-test-recipient__entry">
                                <span class="campaign-test-recipient__prefix">+62</span>
                                <input id="campaignTestRecipientInput" type="text" inputmode="numeric" class="campaign-test-recipient__input" placeholder="Nomor Penerima Tes Iklan">
                                <button type="button" class="campaign-test-recipient__add" id="campaignTestRecipientAdd">Tambahkan</button>
                            </div>
                            <div class="campaign-test-recipient__list" id="campaignTestRecipientList"></div>
                        </fieldset>
                    </div>
                </div>

                {{-- <div class="campaign-step-two__section">
                    <a href="#" class="campaign-inline-link campaign-inline-link--strong campaign-inline-link--arrow">Gunakan Pengiriman Pesan Spesifik (Optional)</a>
                </div> --}}

                <div class="campaign-step-two__section">
                    <label class="campaign-terms">
                        <input type="checkbox" id="campaignTermsCheck">
                        <span>
                            Saya menyetujui <a href="#">Syarat dan Ketentuan</a> yang berlaku di website Telkomsel MyAds. Atas setiap pesan iklan yang dibuat oleh pengguna menggunakan produk dan/atau layanan melalui portal myAds, pengguna dilarang untuk mempergunakan kata-kata, komentar, gambar atau konten apapun yang mengandung unsur SARA atau diskriminasi terhadap pihak manapun, bersifat vulgar dan ancaman, atau hal-hal lain yang dapat dianggap tidak sesuai dengan nilai dan norma sosial.
                        </span>
                    </label>
                </div>

                <div class="campaign-form-actions campaign-form-actions--step-two">
                    <button type="button" class="campaign-outline-button campaign-step-nav-btn" id="campaignStepThreeBack">Sebelumnya</button>
                    {{-- <button type="button" class="campaign-draft-btn">
                        <span class="campaign-draft-btn__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                <path d="M14 3v5h5M9 12h6M9 16h6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>Simpan Iklan Sebagai Draft</span>
                    </button> --}}
                    <button type="button" class="submit-btn lba-primary-btn campaign-step-two__next" id="campaignStepThreeNext" disabled>Lanjutkan</button>
                </div>
            </section>
        </div>

        <div class="campaign-step-panel" data-campaign-panel="4" hidden>
            <section class="campaign-review">
                <div class="campaign-review__main">
                    <div class="campaign-review__hero">
                        <h2 class="campaign-review__title">Review</h2>
                    </div>

                    <div class="campaign-review__field">
                        <div class="campaign-review__field-head">
                            <span class="campaign-review__label">Judul Iklan</span>
                            <button type="button" class="campaign-review-card__link" data-review-edit="1">Ubah</button>
                        </div>
                        <strong id="campaignReviewTitle">-</strong>
                    </div>

                    <article class="campaign-review-card" data-review-card="content">
                        <div class="campaign-review-card__header">
                            <div class="campaign-review-card__head">
                                <span class="campaign-review-card__icon">💬</span>
                                <h3>Konten Iklan</h3>
                            </div>
                            <div class="campaign-review-card__actions">
                                <button type="button" class="campaign-review-card__link" data-review-edit="1">Ubah</button>
                                <button type="button" class="campaign-review-card__toggle" data-review-toggle="content">Tampilkan</button>
                            </div>
                        </div>
                        <div class="campaign-review-card__body" data-review-body="content" hidden>
                            <div class="campaign-review-grid">
                                <div>
                                    <span class="campaign-review-grid__label">Pengirim</span>
                                    <strong id="campaignReviewSender"></strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Konten Pesan</span>
                                    <strong id="campaignReviewMessage">-</strong>
                                </div>
                                <div class="campaign-review-grid__wide">
                                    <span class="campaign-review-grid__label">Lokasi</span>
                                    <strong id="campaignReviewLocation">-</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Radius</span>
                                    <strong id="campaignReviewRadius">300 meter</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Estimasi Penerima Potensial</span>
                                    <strong id="campaignReviewAudience">0</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Jenis Kelamin</span>
                                    <strong id="campaignReviewGender">Semua</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Rentang Umur</span>
                                    <strong id="campaignReviewAge">Semua</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Agama</span>
                                    <strong id="campaignReviewReligion">Semua</strong>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="campaign-review-card" data-review-card="delivery">
                        <div class="campaign-review-card__header">
                            <div class="campaign-review-card__head">
                                <span class="campaign-review-card__icon">⏰</span>
                                <h3>Waktu Pengiriman</h3>
                            </div>
                            <div class="campaign-review-card__actions">
                                <button type="button" class="campaign-review-card__link" data-review-edit="3">Ubah</button>
                                <button type="button" class="campaign-review-card__toggle" data-review-toggle="delivery">Tampilkan</button>
                            </div>
                        </div>
                        <div class="campaign-review-card__body" data-review-body="delivery" hidden>
                            <div class="campaign-review-schedule" id="campaignReviewSchedules"></div>
                            <div class="campaign-review-grid campaign-review-grid--delivery">
                                <div>
                                    <span class="campaign-review-grid__label">Metode Pengiriman</span>
                                    <strong id="campaignReviewDeliveryMethod">Secepatnya</strong>
                                </div>
                                <div>
                                    <span class="campaign-review-grid__label">Nomor Test Iklan</span>
                                    <strong id="campaignReviewTestRecipient">-</strong>
                                </div>
                            </div>
                            <div class="campaign-review-total">
                                <span>Total Pesan yang akan dikirim</span>
                                <strong id="campaignReviewRecipientCount">5 Pesan</strong>
                            </div>
                            <p class="campaign-review-note">dari estimasi <span id="campaignReviewPotentialRange">0</span> penerima potensial</p>
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
                                    <strong>TARGETED</strong>
                                </div>
                                <div class="campaign-cost-card__row">
                                    <span>Tipe Kanal</span>
                                    <strong>SMS</strong>
                                </div>
                                <div class="campaign-cost-card__row">
                                    <span>Harga Satuan</span>
                                    <strong id="campaignReviewUnitPrice">{{ isset($unitPrice) && is_numeric($unitPrice) && (int) $unitPrice > 0 ? 'Rp ' . number_format((int) $unitPrice, 0, ',', '.') : '-' }}</strong>
                                </div>
                            </div>
                            <div class="campaign-cost-card__section">
                                <div class="campaign-cost-card__row campaign-cost-card__row--total">
                                    <span>Grand Total</span>
                                    <strong id="campaignReviewGrandTotal">-</strong>
                                </div>
                            </div>
                            <div class="campaign-cost-card__section">
                                <span class="campaign-cost-card__section-title">Pembayaran Anda Menggunakan</span>
                                <div class="campaign-cost-card__row">
                                    <span>Saldo Umum</span>
                                    <strong class="campaign-cost-card__danger">-</strong>
                                </div>
                                <div class="campaign-cost-card__row">
                                    <span>Saldo Monetary</span>
                                    <strong id="campaignReviewPaymentMonetary">-</strong>
                                </div>
                            </div>
                            <button type="button" class="campaign-cost-card__cta" id="campaignGwSubmitButton">Bayar & Kirim Iklan</button>
                            <div class="campaign-cost-card__section" id="campaignPaymentPanel" hidden>
                                <span class="campaign-cost-card__section-title">Pembayaran QRIS</span>
                                <div class="campaign-cost-card__row">
                                    <span>Status</span>
                                    <strong id="campaignPaymentStatus">-</strong>
                                </div>
                                <div class="campaign-cost-card__row">
                                    <span>Transaction ID</span>
                                    <strong id="campaignPaymentTransaction">-</strong>
                                </div>
                                <div class="campaign-cost-card__row" style="display:block">
                                    <img id="campaignPaymentQr" alt="QRIS" style="max-width: 100%; height: auto; display: block; margin-top: 10px;" />
                                    <a id="campaignPaymentQrLink" class="campaign-inline-link campaign-inline-link--fit" href="#" target="_blank" rel="noreferrer" style="margin-top: 8px; display: inline-block">Buka QRIS</a>
                                </div>
                            </div>
                            <form id="campaignGwSubmitForm" method="POST" action="{{ route('campaign.targeted.send', ['channel' => $channel]) }}" hidden>
                                @csrf
                                <input type="hidden" name="campaign_name" id="campaign_title" value="{{ $autoCampaignName }}">
                                <input type="hidden" name="delivery_time" id="gwDeliveryTime">
                                <input type="hidden" name="payment_transaction_id" id="gwPaymentTransactionId" value="">
                            </form>
                            <p class="campaign-cost-card__footnote">
                                Apabila terdapat pesan yang tidak terkirim, maka biaya akan dikembalikan (refund) sesuai jumlah pesan yang tidak terkirim.
                            </p>
                        </div>
                    </div>
                </aside>
                <div class="campaign-form-actions campaign-form-actions--review campaign-form-actions--review-bottom">
                    <button type="button" class="campaign-outline-button campaign-step-nav-btn" id="campaignStepFourBack">Sebelumnya</button>
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

                    <label class="campaign-location-toggle">
                        <input type="checkbox" disabled>
                        <span>Gunakan Lokasi Saat Ini</span>
                        <strong>Lokasi tidak aktif, mohon aktifkan lokasi</strong>
                    </label>

                    <section class="campaign-map-settings">
                        <h3>Pengaturan</h3>
                        <div class="campaign-map-settings__grid">
                            <div class="field-group campaign-field-group--compact">
                                <label for="campaignLocationRadius" class="field-label">Radius (dalam Meter)</label>
                                <input id="campaignLocationRadius" type="number" class="text-input" min="0" step="100" value="300">
                            </div>
                        </div>

                        {{-- <div class="campaign-map-estimate">
                            <button type="button" class="campaign-outline-button campaign-map-estimate__button" id="campaignCalcAudience">
                                <span class="campaign-outline-button__icon">✓</span>
                                <span>Hitung Penerima Potensial</span>
                            </button>
                            <div class="campaign-map-estimate__result">
                                <span>Estimasi Penerima Potensial</span>
                                <strong id="campaignAudienceEstimate">0</strong>
                            </div>
                        </div> --}}

                        {{-- <label class="campaign-map-checkbox">
                            <input type="checkbox" id="campaignLocationRemember">
                            <span>Simpan pengaturan lokasi agar dapat digunakan kembali</span>
                        </label> --}}
                    </section>

                    <div class="campaign-map-actions">
                        <button type="button" class="campaign-modal-btn campaign-modal-btn--primary" id="campaignApplyLocation" onclick="window.applyCampaignLocationSelection && window.applyCampaignLocationSelection()">Gunakan Lokasi</button>
                        <button type="button" class="campaign-map-cancel" data-location-close onclick="window.closeCampaignLocationModal && window.closeCampaignLocationModal()">Batal</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.openCampaignLocationModal = function () {
            const body = document.body;
            const modal = document.getElementById('campaignMapModal');

            if (!modal) {
                return;
            }

            modal.hidden = false;
            modal.setAttribute('aria-hidden', 'false');
            body.classList.add('campaign-map-open');
        };

        window.closeCampaignLocationModal = function () {
            const body = document.body;
            const modal = document.getElementById('campaignMapModal');

            if (!modal) {
                return;
            }

            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
            body.classList.remove('campaign-map-open');
        };

        window.applyCampaignLocationSelection = function () {
            const button = document.getElementById('campaignOpenLocationModal');
            const buttonIcon = document.getElementById('campaignLocationButtonIcon');
            const buttonContent = document.getElementById('campaignLocationButtonContent');
            const radiusInput = document.getElementById('campaignLocationRadius');
            const state = window.campaignLocationState || {};

            if (!button || !buttonContent) {
                return;
            }

            const formatCoordinate = function (value) {
                const parsed = Number(value);
                return Number.isFinite(parsed) ? parsed.toFixed(6) : '-';
            };

            const formatNumber = function (value) {
                return new Intl.NumberFormat('id-ID').format(Number(value || 0));
            };

            button.classList.add('campaign-outline-button--applied', 'campaign-outline-button--location-summary');

            if (buttonIcon) {
                buttonIcon.textContent = '@';
            }

            buttonContent.innerHTML = `
                <span class="campaign-location-button__title">${state.name || 'Lokasi dipilih'}</span>
                <span class="campaign-location-button__meta">Longitude ${formatCoordinate(state.lng)} | Latitude ${formatCoordinate(state.lat)}</span>
                <span class="campaign-location-button__meta">Radius ${formatNumber(radiusInput?.value || 0)} meter</span>
            `;

            window.closeCampaignLocationModal && window.closeCampaignLocationModal();
        };
    </script>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script>
        (async () => {
            const isTargeted = "{{ $isTargeted ? 'true' : 'false' }}" === 'true';
            const body = document.body;
            const titleInput = document.getElementById('campaign_title');
            const titlePreview = document.getElementById('campaignTitlePreview');
            const senderInput = document.getElementById('campaign_sender');
            const senderPreview = document.getElementById('campaignSenderPreview');
            const messageInput = document.getElementById('campaign_message');
            const messagePreview = document.getElementById('campaignMessagePreview');
            const messageCount = document.getElementById('campaignMessageCount');
            const addButton = document.getElementById('campaignAddButton');
            const buttonBuilder = document.getElementById('campaignButtonBuilder');
            const phoneActions = document.getElementById('campaignPhoneActions');
            const buttonWarning = document.getElementById('campaignButtonWarning');
            const sectionKicker = document.getElementById('campaignSectionKicker');
            const sectionHeading = document.getElementById('campaignSectionHeading');
            const campaignPanels = document.querySelectorAll('[data-campaign-panel]');
            const campaignSteps = document.querySelectorAll('[data-campaign-step]');
            const stepOneNext = document.getElementById('campaignStepOneNext');
            const stepTwoBack = document.getElementById('campaignStepTwoBack');
            const stepTwoNext = document.getElementById('campaignStepTwoNext');
            const stepThreeBack = document.getElementById('campaignStepThreeBack');
            const stepThreeNext = document.getElementById('campaignStepThreeNext');
            const stepFourBack = document.getElementById('campaignStepFourBack');
            const targetedRecipient = document.getElementById('campaignTargetRecipient');
            const targetedProfileBuilder = document.getElementById('campaignTargetedProfileBuilder');
            const targetedProfileFields = Array.from(document.querySelectorAll('#campaignTargetedProfileBuilder select'));
            const homeLocationList = document.getElementById('campaignHomeLocationList');
            const workLocationList = document.getElementById('campaignWorkLocationList');
            const addHomeLocationButton = document.getElementById('campaignAddHomeLocation');
            const addWorkLocationButton = document.getElementById('campaignAddWorkLocation');
            const scheduleList = document.getElementById('campaignScheduleList');
            const addScheduleButton = document.getElementById('campaignAddSchedule');
            const termsCheck = document.getElementById('campaignTermsCheck');
            const recipientCountInput = document.getElementById('campaignRecipientCount');
            const testRecipientField = document.getElementById('campaignTestRecipient');
            const testRecipientInput = document.getElementById('campaignTestRecipientInput');
            const testRecipientAdd = document.getElementById('campaignTestRecipientAdd');
            const testRecipientList = document.getElementById('campaignTestRecipientList');
            const locationModal = document.getElementById('campaignMapModal');
            const openLocationModalButton = document.getElementById('campaignOpenLocationModal');
            const locationCloseButtons = document.querySelectorAll('[data-location-close]');
            const locationCanvas = document.getElementById('campaignMapCanvas');
            const locationSearchHost = document.getElementById('campaignLocationSearchHost');
            const mapFeedback = document.getElementById('campaignMapFeedback');
            const locationRadiusInput = document.getElementById('campaignLocationRadius');
            const calcAudienceButton = document.getElementById('campaignCalcAudience');
            const audienceEstimate = document.getElementById('campaignAudienceEstimate');
            const applyLocationButton = document.getElementById('campaignApplyLocation');
            const reviewSchedules = document.getElementById('campaignReviewSchedules');
            const profileDropdownUrl = "{{ route('campaign.targeted.profile-dropdown-list', ['channel' => $channel]) }}";

            let buttonIndex = 0;
            const locationState = {
                name: '',
                audience: 0,
                lat: -6.1944491,
                lng: 106.8229198,
            };
            window.campaignLocationState = locationState;
            let liveMap = null;
            let liveMarker = null;
            let liveCircle = null;
            let geocoderControl = null;
            let scheduleIndex = 1;
            let profileDropdownSource = null;

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

                    optionsWrap.addEventListener('click', (event) => {
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

            const syncButtonLimitState = () => {
                if (!buttonBuilder || !addButton || !buttonWarning) {
                    return;
                }

                const count = buttonBuilder.querySelectorAll('[data-button-card]').length;
                const reachedLimit = count >= 2;

                addButton.disabled = reachedLimit;
                addButton.classList.toggle('campaign-outline-button--disabled', reachedLimit);
                buttonWarning.hidden = !reachedLimit;
            };

            const syncCampaignStep = (stepNumber) => {
                campaignPanels.forEach((panel) => {
                    panel.hidden = panel.dataset.campaignPanel !== String(stepNumber);
                });

                campaignSteps.forEach((step) => {
                    const stepValue = Number(step.dataset.campaignStep);
                    step.classList.toggle('campaign-step--active', stepValue === stepNumber);
                    step.classList.toggle('campaign-step--completed', stepValue < stepNumber);
                });

                if (sectionKicker) {
                    sectionKicker.textContent = `Step ${String(stepNumber).padStart(2, '0')}`;
                }

                if (sectionHeading) {
                    sectionHeading.textContent =
                        stepNumber === 2 ? 'Atur Profil Penerima' :
                        stepNumber === 3 ? 'Atur Pengiriman' :
                        stepNumber === 4 ? 'Review & Pembayaran' :
                        'Konten Pesan Iklan';
                }
            };

            const syncScheduleRemoveState = () => {
                const items = scheduleList?.querySelectorAll('[data-schedule-item]') || [];
                items.forEach((item, index) => {
                    const title = item.querySelector('.campaign-schedule-card__header span');
                    const removeButton = item.querySelector('[data-remove-schedule]');

                    if (title) {
                        title.textContent = `Jadwal Pengiriman ${index + 1}`;
                    }

                    if (removeButton) {
                        removeButton.hidden = items.length === 1;
                    }
                });
            };

            const buildTimeOptions = (selectedValue) => {
                const options = [];

                for (let hour = 0; hour < 24; hour += 1) {
                    for (let minute = 0; minute < 60; minute += 30) {
                        const value = `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
                        options.push(`<option value="${value}"${value === selectedValue ? ' selected' : ''}>${value}</option>`);
                    }
                }

                return options.join('');
            };

            const buildHourOptions = (selectedValue) => {
                const options = [];

                for (let hour = 0; hour < 24; hour += 1) {
                    const value = String(hour).padStart(2, '0');
                    options.push(`<option value="${value}"${value === selectedValue ? ' selected' : ''}>${value}</option>`);
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

            const defaultDeliverySchedule = () => {
                const now = new Date();
                const start = new Date(now);
                start.setSeconds(0, 0);
                start.setMinutes(Math.ceil(start.getMinutes() / 30) * 30);

                const end = new Date(start);
                end.setHours(end.getHours() + 4);

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
                const dateStart = document.querySelector(`[data-schedule-date-start="${scheduleId}"]`);
                const dateEnd = document.querySelector(`[data-schedule-date-end="${scheduleId}"]`);

                if (dateStart) {
                    dateStart.value = defaults.startDate;
                }
                if (dateEnd) {
                    dateEnd.value = defaults.endDate;
                }

                initScheduleTimeSelects(scheduleId, defaults.startTime, defaults.endTime);
                syncSchedulePreview(scheduleId);
                return defaults;
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

            const syncSchedulePreview = (scheduleId) => {
                const dateStart = document.querySelector(`[data-schedule-date-start="${scheduleId}"]`);
                const dateEnd = document.querySelector(`[data-schedule-date-end="${scheduleId}"]`);
                const timeStartHour = document.querySelector(`[data-schedule-time-start-hour="${scheduleId}"]`);
                const timeStartMinute = document.querySelector(`[data-schedule-time-start-minute="${scheduleId}"]`);
                const timeEndHour = document.querySelector(`[data-schedule-time-end-hour="${scheduleId}"]`);
                const timeEndMinute = document.querySelector(`[data-schedule-time-end-minute="${scheduleId}"]`);
                const datePreview = document.getElementById(`campaignScheduleDatePreview${scheduleId}`);
                const timePreview = document.getElementById(`campaignScheduleTimePreview${scheduleId}`);

                if (datePreview && dateStart && dateEnd) {
                    datePreview.textContent = `${formatScheduleDate(dateStart.value)} - ${formatScheduleDate(dateEnd.value)}`;
                }

                if (timePreview && timeStartHour && timeStartMinute && timeEndHour && timeEndMinute) {
                    timePreview.textContent = `${timeStartHour.value}:${timeStartMinute.value}-${timeEndHour.value}:${timeEndMinute.value} WIB`;
                }
            };

            const formatCurrency = (value) => `Rp ${new Intl.NumberFormat('id-ID').format(Number(value || 0))}`;

            const unitPrice = Number("{{ (int) ($unitPrice ?? 0) }}") || 0;
            const formatUnitPrice = () => unitPrice > 0 ? formatCurrency(unitPrice) : '-';

            const hasTargetedProfileFilters = () => targetedProfileFields.some((field) => field?.value?.trim());

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
                    return;
                }

                const dropdownMap = {
                    campaignTargetGender: ['gender', 'profileGender', 'jenisKelamin'],
                    campaign_gender: ['gender', 'profileGender', 'jenisKelamin'],
                    campaignTargetAge: ['age', 'profileAge', 'rentangUmur'],
                    campaign_age: ['age', 'profileAge', 'rentangUmur'],
                    campaignTargetOs: ['os', 'profileOs', 'operatingSystem'],
                    campaignTargetDataPackage: ['dataPackage', 'packageData', 'paketData'],
                    campaignTargetHouseholdSpend: ['householdSpend', 'householdExpense', 'pengeluaranRumahTangga'],
                    campaignTargetCardType: ['cardType', 'profileCardType', 'card_type'],
                    campaignTargetMaritalStatus: ['maritalStatus', 'marriage', 'statusPernikahan'],
                    campaignTargetLifestyle: ['lifestyleseg', 'lifestyle', 'lifestyleSegment', 'profileLifestyle', 'profileLifestyleSegment'],
                    campaignTargetReligion: ['religion', 'religions', 'profileReligion', 'agama'],
                    campaign_religion: ['religion', 'religions', 'profileReligion', 'agama'],
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

            const locationOptionsTemplate = (type = 'home') => `
                <div class="campaign-targeted-location-grid">
                    <div class="field-group campaign-field-group--compact">
                        <label class="field-label">Provinsi</label>
                        <select class="text-input text-input--select" data-location-select="province" name="${type === 'work' ? 'profile_work[]' : 'profile_hometown[]'}" form="campaignGwSubmitForm">
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

            const syncTargetedProfileBuilder = () => {
                if (!targetedProfileBuilder || !targetedRecipient) {
                    return;
                }

                const shouldShow = targetedRecipient.value === 'profile-recipient';
                targetedProfileBuilder.hidden = !shouldShow;
                targetedProfileBuilder.classList.toggle('campaign-targeted-profile-builder--active', shouldShow);
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
                syncTargetedLocationRemoveState(list);
            };

            const syncReviewSummary = () => {
                const setText = (id, value) => {
                    const target = document.getElementById(id);
                    if (target) {
                        target.textContent = value;
                    }
                };

                const sender = document.getElementById('campaign_sender');
                const deliveryMethod = document.getElementById('campaignDeliveryMethod');
                const testRecipient = document.getElementById('campaignTestRecipient');
                const recipientCount = Number(document.getElementById('campaignRecipientCount')?.value || 0);
                const radius = Number(locationRadiusInput?.value || 0);
                const totalCost = unitPrice > 0 ? recipientCount * unitPrice : 0;
                const totalCostLabel = unitPrice > 0 ? formatCurrency(totalCost) : '-';

                setText('campaignReviewTitle', titleInput?.value?.trim() || 'Tanpa Judul');
                setText('campaignReviewSender', sender?.value || '');
                setText('campaignReviewMessage', messageInput?.value?.trim() || '-');
                setText('campaignReviewLocation', locationState.name || '-');
                setText('campaignReviewRadius', `${normalizeAudience(radius)} meter`);
                setText('campaignReviewAudience', normalizeAudience(locationState.audience));
                setText('campaignReviewGender', getSelectedTexts('campaign_gender'));
                setText('campaignReviewAge', getSelectedTexts('campaign_age'));
                setText('campaignReviewReligion', getSelectedTexts('campaign_religion'));
                if (isTargeted) {
                    const genderValue = getSelectedTexts('campaignTargetGender') || 'Semua';
                    const ageValue = getSelectedTexts('campaignTargetAge');
                    const religionValue = getSelectedTexts('campaignTargetReligion');

                    setText('campaignReviewGender', genderValue);
                    setText('campaignReviewAge', ageValue);
                    setText('campaignReviewReligion', religionValue);
                }
                setText('campaignReviewDeliveryMethod', deliveryMethod?.value || 'Metode Pengiriman Pesan');
                setText('campaignReviewTestRecipient', testRecipient?.value || 'Belum dipilih');
                setText('campaignReviewRecipientCount', `${normalizeAudience(recipientCount)} Pesan`);
                setText('campaignReviewPotentialRange', normalizeAudience(locationState.audience));
                setText('campaignReviewUnitPrice', formatUnitPrice());
                setText('campaignReviewGrandTotal', totalCostLabel);
                setText('campaignReviewPaymentMonetary', totalCostLabel);

                if (reviewSchedules) {
                    const schedules = Array.from(scheduleList?.querySelectorAll('[data-schedule-item]') || []).map((item, index) => {
                        const scheduleId = item.dataset.scheduleItem;
                        const date = document.getElementById(`campaignScheduleDatePreview${scheduleId}`)?.textContent || '-';
                        const time = document.getElementById(`campaignScheduleTimePreview${scheduleId}`)?.textContent || '-';

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

            const initScheduleTimeSelects = (scheduleId, startValue = '09:30', endValue = '10:00') => {
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

            const syncStepTwoValidity = () => {
                if (stepTwoNext) {
                    stepTwoNext.disabled = !targetedRecipient?.value || (targetedRecipient.value === 'profile-recipient' && !hasTargetedProfileFilters());
                }
            };

            const getScheduleDurationMinutes = (scheduleId) => {
                const startHour = Number(document.querySelector(`[data-schedule-time-start-hour="${scheduleId}"]`)?.value || NaN);
                const startMinute = Number(document.querySelector(`[data-schedule-time-start-minute="${scheduleId}"]`)?.value || NaN);
                const endHour = Number(document.querySelector(`[data-schedule-time-end-hour="${scheduleId}"]`)?.value || NaN);
                const endMinute = Number(document.querySelector(`[data-schedule-time-end-minute="${scheduleId}"]`)?.value || NaN);

                if ([startHour, startMinute, endHour, endMinute].some(Number.isNaN)) {
                    return 0;
                }

                return (endHour * 60 + endMinute) - (startHour * 60 + startMinute);
            };

            const syncRecipientScheduleValidity = () => {
                const recipientCount = Number(recipientCountInput?.value || 0);
                const isRecipientValid = recipientCount >= 5;
                const recipientAlert = document.querySelector('[data-recipient-count-alert]');
                if (recipientAlert) {
                    recipientAlert.hidden = isRecipientValid;
                }

                let isScheduleValid = true;
                document.querySelectorAll('[data-schedule-item]').forEach((item) => {
                    const scheduleId = item.dataset.scheduleItem;
                    const alert = item.querySelector('[data-schedule-duration-alert]');
                    const isItemValid = getScheduleDurationMinutes(scheduleId) >= 240;

                    if (alert) {
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

            const collapseReviewCards = () => {
                document.querySelectorAll('[data-review-card]').forEach((card) => {
                    card.classList.remove('campaign-review-card--open');
                });

                document.querySelectorAll('[data-review-body]').forEach((body) => {
                    body.hidden = true;
                });

                document.querySelectorAll('[data-review-toggle]').forEach((toggle) => {
                    toggle.textContent = 'Tampilkan';
                });
            };

            const addScheduleItem = () => {
                if (!scheduleList) {
                    return;
                }

                const defaults = defaultDeliverySchedule();

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
                            <label class="field-label" for="campaignScheduleDate${scheduleIndex}Start">Tgl Kirim</label>
                            <div class="campaign-range-field">
                                <input id="campaignScheduleDate${scheduleIndex}Start" type="date" class="text-input" value="${defaults.startDate}" data-schedule-date-start="${scheduleIndex}">
                                <span class="campaign-range-field__separator">-</span>
                                <input id="campaignScheduleDate${scheduleIndex}End" type="date" class="text-input" value="${defaults.endDate}" data-schedule-date-end="${scheduleIndex}">
                            </div>
                            <p class="campaign-range-preview" id="campaignScheduleDatePreview${scheduleIndex}">${formatScheduleDate(defaults.startDate)} - ${formatScheduleDate(defaults.endDate)}</p>
                        </div>
                        <div class="field-group campaign-field-group--compact">
                            <label class="field-label" for="campaignScheduleTime${scheduleIndex}Start">Jam Kirim</label>
                            <div class="campaign-time-picker" data-time-picker="${scheduleIndex}">
                                <button type="button" class="campaign-time-picker__trigger" data-time-trigger="${scheduleIndex}">
                                    <span class="campaign-time-picker__value" id="campaignScheduleTimePreview${scheduleIndex}">${defaults.startTime}-${defaults.endTime} WIB</span>
                                    <span class="campaign-time-picker__clock">◷</span>
                                </button>
                                <div class="campaign-time-picker__panel" data-time-panel="${scheduleIndex}" hidden>
                                    <div class="campaign-time-picker__columns">
                                        <div class="campaign-time-picker__column">
                                            <span class="campaign-time-picker__label">Jam Mulai</span>
                                            <div class="campaign-time-picker__inputs">
                                                <select id="campaignScheduleTime${scheduleIndex}StartHour" class="campaign-time-picker__select" data-schedule-time-start-hour="${scheduleIndex}"></select>
                                                <span class="campaign-time-picker__colon">:</span>
                                                <select id="campaignScheduleTime${scheduleIndex}StartMinute" class="campaign-time-picker__select" data-schedule-time-start-minute="${scheduleIndex}"></select>
                                                <span class="campaign-time-picker__suffix">WIB</span>
                                            </div>
                                        </div>
                                        <div class="campaign-time-picker__column">
                                            <span class="campaign-time-picker__label">Jam Berakhir</span>
                                            <div class="campaign-time-picker__inputs">
                                                <select id="campaignScheduleTime${scheduleIndex}EndHour" class="campaign-time-picker__select" data-schedule-time-end-hour="${scheduleIndex}"></select>
                                                <span class="campaign-time-picker__colon">:</span>
                                                <select id="campaignScheduleTime${scheduleIndex}EndMinute" class="campaign-time-picker__select" data-schedule-time-end-minute="${scheduleIndex}"></select>
                                                <span class="campaign-time-picker__suffix">WIB</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="campaign-time-picker__hint">Disarankan durasi pengiriman pesan lebih dari 4 jam agar pengiriman lebih optimal</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="campaign-step-two__alert" data-schedule-duration-alert>Atur jadwal minimal 4 jam/hari untuk memastikan target anda tercapai minimal</p>
                `;

                scheduleList.appendChild(item);
                initScheduleTimeSelects(scheduleIndex, defaults.startTime, defaults.endTime);
                syncScheduleRemoveState();
                syncSchedulePreview(scheduleIndex);
                syncStepThreeValidity();
            };

            const renderPhoneActions = () => {
                if (!buttonBuilder || !phoneActions) {
                    return;
                }

                phoneActions.innerHTML = '';
            };

            const escapeRegExp = (value) => String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

            const linkifyMessage = (value) => escapeHtml(value).replace(
                /(https?:\/\/[^\s<]+|www\.[^\s<]+)/g,
                '<span class="campaign-phone__url">$1</span>'
            );

            const syncUrlIntoMessage = (card) => {
                if (!messageInput) {
                    return;
                }

                const websiteInput = card.querySelector('[data-action-url]');
                const previousUrl = card.dataset.syncedUrl || '';
                const nextUrl = websiteInput?.value?.trim() || '';
                let message = messageInput.value;

                if (previousUrl !== '') {
                    message = message.replace(new RegExp(`\\n?${escapeRegExp(previousUrl)}`, 'g'), '');
                }

                if (nextUrl !== '') {
                    message = `${message.trimEnd()}${message.trim() === '' ? '' : '\n'}${nextUrl}`;
                }

                card.dataset.syncedUrl = nextUrl;
                messageInput.value = message;
                syncMessagePreview();
            };

            const updateButtonCardMeta = (card) => {
                const websiteInput = card.querySelector('[data-action-url]');
                const urlMeta = card.querySelector('[data-url-meta]');

                urlMeta.innerHTML = `${websiteInput.value.length} / 2000`;
                syncUrlIntoMessage(card);
                renderPhoneActions();
            };

            const createButtonCard = () => {
                if (!buttonBuilder) {
                    return;
                }

                if (buttonBuilder.querySelectorAll('[data-button-card]').length >= 2) {
                    if (buttonWarning) {
                        buttonWarning.hidden = false;
                    }
                    return;
                }

                buttonIndex += 1;

                const card = document.createElement('article');
                card.className = 'campaign-button-card';
                card.dataset.buttonCard = String(buttonIndex);
                card.innerHTML = `
                    <div class="campaign-button-card__header">
                        <h3>Call to Action</h3>
                        <button type="button" class="campaign-button-card__remove" data-remove-button aria-label="Hapus tombol">×</button>
                    </div>
                    <div class="campaign-button-card__fields">
                        <div class="campaign-button-card__field">
                            <label>Url Type</label>
                            <select class="text-input text-input--select" data-url-type>
                                <option value="static">static</option>
                            </select>
                        </div>
                        <div class="campaign-button-card__field">
                            <label>Website URL</label>
                            <input type="text" class="text-input" data-action-url maxlength="2000" value="">
                            <div class="campaign-button-card__meta" data-url-meta>0 / 2000</div>
                        </div>
                    </div>
                `;

                buttonBuilder.appendChild(card);

                const removeButton = card.querySelector('[data-remove-button]');
                const websiteInput = card.querySelector('[data-action-url]');

                const syncCard = () => updateButtonCardMeta(card);

                websiteInput.addEventListener('input', syncCard);
                removeButton.addEventListener('click', () => {
                    card.querySelector('[data-action-url]').value = '';
                    syncUrlIntoMessage(card);
                    card.remove();
                    renderPhoneActions();
                    syncButtonLimitState();
                });

                syncCard();
                syncButtonLimitState();
            };

            const syncMessagePreview = () => {
                const message = messageInput.value.trim();
                messagePreview.innerHTML = message ? linkifyMessage(message) : 'Preview pesan akan muncul di sini.';
                messageCount.textContent = isTargeted
                    ? `${messageInput.value.length}/160 karakter (1 SMS)`
                    : `${messageInput.value.length}/160 karakter`;
            };

            const syncSenderPreview = () => {
                if (senderPreview && senderInput) {
                    senderPreview.textContent = senderInput.value;
                }
            };

            const normalizeAudience = (value) => new Intl.NumberFormat('id-ID').format(value);
            const formatCoordinate = (value) => Number(value).toFixed(6);

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

            const renderTestRecipientItem = (number) => {
                if (!testRecipientList || !testRecipientField) {
                    return;
                }

                const existing = testRecipientList.querySelector(`[data-test-recipient="${number}"]`);
                if (existing) {
                    testRecipientList.querySelectorAll('input[type="checkbox"]').forEach((other) => {
                        other.checked = false;
                    });
                    existing.querySelector('input[type="checkbox"]').checked = true;
                    testRecipientField.value = number;
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
                    testRecipientList.querySelectorAll('input[type="checkbox"]').forEach((other) => {
                        if (other !== checkbox) {
                            other.checked = false;
                        }
                    });

                    testRecipientField.value = checkbox.checked ? number : '';
                });

                removeButton.addEventListener('click', (event) => {
                    event.preventDefault();
                    if (testRecipientField.value === number) {
                        testRecipientField.value = '';
                    }
                    item.remove();
                });

                testRecipientList.querySelectorAll('input[type="checkbox"]').forEach((other) => {
                    other.checked = false;
                });
                testRecipientList.appendChild(item);
                testRecipientField.value = number;
            };

            const addTestRecipient = () => {
                const number = normalizeTestRecipient(testRecipientInput?.value);
                if (number === '') {
                    return;
                }

                renderTestRecipientItem(number);
                testRecipientInput.value = '';
            };

            const syncLocationButtonSummary = () => {
                window.campaignLocationState = locationState;
                window.applyCampaignLocationSelection && window.applyCampaignLocationSelection();
            };

            const calculateAudience = () => {
                const radius = Number(locationRadiusInput.value || 0);
                const base = Math.max(0, radius) * 6;
                locationState.audience = Math.max(0, base);
                if (audienceEstimate) {
                    audienceEstimate.textContent = normalizeAudience(locationState.audience);
                }
            };

            const updateLiveRadius = () => {
                if (!liveCircle || !liveMarker) {
                    return;
                }

                liveCircle.setRadius(Number(locationRadiusInput.value || 0));
                liveCircle.setLatLng(liveMarker.getLatLng());
            };

            const updateLocationPoint = (latlng, label) => {
                if (!liveMap) {
                    return;
                }

                locationState.lat = latlng.lat;
                locationState.lng = latlng.lng;
                locationState.name = label || `${latlng.lat.toFixed(5)}, ${latlng.lng.toFixed(5)}`;

                if (!liveMarker) {
                    liveMarker = L.marker(latlng).addTo(liveMap);
                } else {
                    liveMarker.setLatLng(latlng);
                }

                if (!liveCircle) {
                    liveCircle = L.circle(latlng, {
                        radius: Number(locationRadiusInput.value || 0),
                        color: '#ff475d',
                        weight: 2,
                        fillColor: '#ff475d',
                        fillOpacity: 0.16,
                    }).addTo(liveMap);
                } else {
                    liveCircle.setLatLng(latlng);
                }

                updateLiveRadius();

                if (mapFeedback) {
                    mapFeedback.textContent = `Titik aktif: ${locationState.name}`;
                }
            };

            const initLiveMap = () => {
                if (liveMap || !locationCanvas || typeof window.L === 'undefined') {
                    if (!locationCanvas || typeof window.L !== 'undefined') {
                        return;
                    }

                    locationCanvas.innerHTML = '<div class="campaign-map-fallback">Peta tidak dapat dimuat saat ini. Periksa koneksi internet atau cache browser, lalu coba buka lagi popup lokasi.</div>';
                    return;
                }

                liveMap = L.map(locationCanvas, {
                    zoomControl: true,
                }).setView([locationState.lat, locationState.lng], 14);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(liveMap);

                updateLocationPoint(L.latLng(locationState.lat, locationState.lng), locationState.name);

                liveMap.on('click', (event) => {
                    updateLocationPoint(event.latlng);
                });

                if (locationSearchHost && window.L.Control?.Geocoder) {
                    geocoderControl = L.Control.geocoder({
                        defaultMarkGeocode: false,
                        placeholder: 'Masukkan lokasi pencarian',
                    })
                        .on('markgeocode', (event) => {
                            const center = event.geocode.center;
                            updateLocationPoint(center, event.geocode.name);
                            liveMap.setView(center, 15);
                        })
                        .addTo(liveMap);

                    const geocoderContainer = geocoderControl.getContainer?.();
                    if (geocoderContainer) {
                        locationSearchHost.appendChild(geocoderContainer);
                    }
                }
            };

            const openLocationModal = () => {
                locationModal.hidden = false;
                locationModal.setAttribute('aria-hidden', 'false');
                body.classList.add('campaign-map-open');
                initLiveMap();
                calculateAudience();
                updateLiveRadius();
                window.setTimeout(() => {
                    liveMap?.invalidateSize();
                }, 120);
            };

            const closeLocationModal = () => {
                locationModal.hidden = true;
                locationModal.setAttribute('aria-hidden', 'true');
                body.classList.remove('campaign-map-open');
            };

            window.openCampaignLocationModal = openLocationModal;
            window.closeCampaignLocationModal = closeLocationModal;

            syncMessagePreview();
            syncSenderPreview();
            senderInput?.addEventListener('input', syncSenderPreview);
            messageInput?.addEventListener('input', syncMessagePreview);
            addButton?.addEventListener('click', createButtonCard);
            initCampaignMultiselects();
            loadProfileDropdownOptions().then(() => {
                initCampaignMultiselects();
                syncTargetedProfileBuilder();
                syncStepTwoValidity();
            });
            syncButtonLimitState();
            calculateAudience();
            closeLocationModal();
            syncCampaignStep(1);
            syncScheduleRemoveState();
            applyDefaultDeliverySchedule(1);
            syncTargetedProfileBuilder();
            syncStepTwoValidity();
            syncStepThreeValidity();
            syncTargetedLocationRemoveState(homeLocationList);
            syncTargetedLocationRemoveState(workLocationList);

            openLocationModalButton?.addEventListener('click', openLocationModal);
            stepOneNext?.addEventListener('click', () => {
                syncCampaignStep(2);
            });
            stepTwoBack?.addEventListener('click', () => syncCampaignStep(1));
            stepTwoNext?.addEventListener('click', () => {
                syncCampaignStep(3);
            });
            stepThreeBack?.addEventListener('click', () => syncCampaignStep(2));
            stepThreeNext?.addEventListener('click', () => {
                syncReviewSummary();
                collapseReviewCards();
                syncCampaignStep(4);
            });
            stepFourBack?.addEventListener('click', () => syncCampaignStep(3));
            addScheduleButton?.addEventListener('click', addScheduleItem);
            termsCheck?.addEventListener('change', syncStepThreeValidity);
            recipientCountInput?.addEventListener('input', syncStepThreeValidity);
            testRecipientAdd?.addEventListener('click', addTestRecipient);
            testRecipientInput?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    addTestRecipient();
                }
            });
            targetedRecipient?.addEventListener('change', () => {
                syncTargetedProfileBuilder();
                syncStepTwoValidity();
            });
            targetedProfileFields.forEach((field) => {
                field.addEventListener('change', () => {
                    syncStepTwoValidity();
                });
            });
            addHomeLocationButton?.addEventListener('click', () => addTargetedLocationRow('home'));
            addWorkLocationButton?.addEventListener('click', () => addTargetedLocationRow('work'));
            [homeLocationList, workLocationList].forEach((list) => {
                list?.addEventListener('click', (event) => {
                    const removeButton = event.target.closest('[data-remove-location]');
                    if (!removeButton) {
                        return;
                    }

                    const row = removeButton.closest('[data-location-row]');
                    if (row) {
                        row.remove();
                        syncTargetedLocationRemoveState(list);
                    }
                });

            });
            scheduleList?.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-time-trigger]');
                if (trigger) {
                    toggleTimePicker(trigger.dataset.timeTrigger);
                    return;
                }

                const removeButton = event.target.closest('[data-remove-schedule]');
                if (!removeButton) {
                    return;
                }

                removeButton.closest('[data-schedule-item]')?.remove();
                syncScheduleRemoveState();
                syncStepThreeValidity();
            });
            document.addEventListener('click', (event) => {
                const editButton = event.target.closest('[data-review-edit]');
                if (editButton) {
                    const editStep = Number(editButton.dataset.reviewEdit);
                    syncCampaignStep(editStep);
                    return;
                }

                const toggleButton = event.target.closest('[data-review-toggle]');
                if (toggleButton) {
                    const cardKey = toggleButton.dataset.reviewToggle;
                    const card = document.querySelector(`[data-review-card="${cardKey}"]`);
                    const body = document.querySelector(`[data-review-body="${cardKey}"]`);
                    const isOpen = card?.classList.contains('campaign-review-card--open');

                    card?.classList.toggle('campaign-review-card--open', !isOpen);
                    if (body) {
                        body.hidden = isOpen;
                    }
                    toggleButton.textContent = isOpen ? 'Tampilkan' : 'Sembunyikan';
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
                    syncStepThreeValidity();
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
                    syncStepThreeValidity();
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
            locationCloseButtons.forEach((button) => {
                button.addEventListener('click', closeLocationModal);
            });
            locationRadiusInput?.addEventListener('input', () => {
                updateLiveRadius();
                calculateAudience();
            });
            calcAudienceButton?.addEventListener('click', calculateAudience);
            applyLocationButton?.addEventListener('click', () => {
                calculateAudience();
                syncLocationButtonSummary();
                closeLocationModal();
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !locationModal.hidden) {
                    closeLocationModal();
                }
            });

            const gwSubmitButton = document.getElementById('campaignGwSubmitButton');
            const gwSubmitForm = document.getElementById('campaignGwSubmitForm');
            const gwDeliveryTime = document.getElementById('gwDeliveryTime');
            const gwPaymentTransactionId = document.getElementById('gwPaymentTransactionId');

            const paymentInitiateUrl = "{{ route('campaign.payment.initiate') }}";
            const paymentStatusUrlTemplate = "{{ route('campaign.payment.status', ['transactionId' => '__TRANSACTION_ID__']) }}";
            const csrfToken = "{{ csrf_token() }}";

            const paymentPanel = document.getElementById('campaignPaymentPanel');
            const paymentStatusLabel = document.getElementById('campaignPaymentStatus');
            const paymentTransactionLabel = document.getElementById('campaignPaymentTransaction');
            const paymentQr = document.getElementById('campaignPaymentQr');
            const paymentQrLink = document.getElementById('campaignPaymentQrLink');

            const pickScheduleValue = (selector) => {
                const el = document.querySelector(selector);
                return el && typeof el.value === 'string' ? el.value : '';
            };

            const sleep = (ms) => new Promise((resolve) => window.setTimeout(resolve, ms));

            const setPaymentUi = ({ status, transactionId, qrisUrl } = {}) => {
                if (paymentPanel) {
                    paymentPanel.hidden = false;
                }
                if (paymentStatusLabel) {
                    paymentStatusLabel.textContent = status || '-';
                }
                if (paymentTransactionLabel) {
                    paymentTransactionLabel.textContent = transactionId || '-';
                }
                if (paymentQr && qrisUrl) {
                    paymentQr.src = qrisUrl;
                }
                if (paymentQrLink) {
                    paymentQrLink.href = qrisUrl || '#';
                    paymentQrLink.hidden = !qrisUrl;
                }
            };

            const pollPaymentUntilSuccess = async (transactionId) => {
                const statusUrl = paymentStatusUrlTemplate.replace('__TRANSACTION_ID__', encodeURIComponent(transactionId));
                const startedAt = Date.now();
                const timeoutMs = 10 * 60 * 1000;

                while ((Date.now() - startedAt) < timeoutMs) {
                    let status = '';

                    try {
                        const response = await fetch(statusUrl, {
                            headers: { Accept: 'application/json' },
                        });
                        const payload = await response.json().catch(() => null);
                        status = String(payload?.data?.status || '').toUpperCase();
                    } catch (error) {
                        // keep polling (network blips)
                    }

                    if (status) {
                        setPaymentUi({ status, transactionId });
                    }

                    if (status === 'SUCCESS') {
                        return;
                    }

                    if (status === 'FAILED' || status === 'EXPIRED') {
                        throw new Error('Pembayaran gagal. Status: ' + status);
                    }

                    await sleep(3000);
                }

                throw new Error('Timeout menunggu pembayaran. Silakan coba lagi.');
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
                        product_category: 'MYADS',
                        product_type: 'ADVERTISEMENT',
                        product_detail: detail,
                    }),
                });

                const payload = await response.json().catch(() => null);

                if (!response.ok || !payload || payload.success !== true) {
                    const message = payload?.message || 'Gagal initiate pembayaran.';
                    throw new Error(message);
                }

                return payload;
            };

            const submitCampaignAfterPayment = async () => {
                if (!gwSubmitForm || !gwDeliveryTime || !gwPaymentTransactionId) {
                    return;
                }

                if (!testRecipientField?.value && testRecipientInput?.value?.trim()) {
                    addTestRecipient();
                }

                const recipientCount = Number(document.getElementById('campaignRecipientCount')?.value || 0);
                const totalCost = unitPrice > 0 ? recipientCount * unitPrice : 0;
                const phone = String(document.getElementById('campaignTestRecipient')?.value || '').trim();
                const title = String(titleInput?.value || '').trim() || 'Tanpa Judul';

                if (!phone) {
                    throw new Error('Nomor penerima tes belum diisi.');
                }
                if (!totalCost || totalCost <= 0) {
                    throw new Error('Total biaya tidak valid.');
                }

                gwSubmitButton.disabled = true;
                gwSubmitButton.textContent = 'Menyiapkan pembayaran...';

                const initiateResp = await initiatePayment(totalCost, phone, `SMS TARGETED - ${title}`);
                const transactionId = String(initiateResp?.data?.transaction_id || '').trim();
                const qrisUrl = String(initiateResp?.data?.qris_url || '').trim();

                if (!transactionId) {
                    throw new Error('Transaction ID tidak ditemukan dari response pembayaran.');
                }

                gwPaymentTransactionId.value = transactionId;
                setPaymentUi({ status: 'PENDING', transactionId, qrisUrl });

                gwSubmitButton.textContent = 'Menunggu pembayaran...';

                await pollPaymentUntilSuccess(transactionId);

                gwSubmitButton.textContent = 'Pembayaran sukses. Mengirim campaign...';

                const scheduleId = '1';
                const hourValue = pickScheduleValue(`[data-schedule-time-start-hour="${scheduleId}"]`);
                const minuteValue = pickScheduleValue(`[data-schedule-time-start-minute="${scheduleId}"]`);
                gwDeliveryTime.value = hourValue && minuteValue ? `${hourValue}:${minuteValue}` : '';

                gwSubmitForm.submit();
            };

            gwSubmitButton?.addEventListener('click', async () => {
                try {
                    await submitCampaignAfterPayment();
                } catch (error) {
                    const message = error?.message ? String(error.message) : 'Gagal memproses pembayaran.';
                    setPaymentUi({ status: 'FAILED' });
                    gwSubmitButton.disabled = false;
                    gwSubmitButton.textContent = 'Bayar & Kirim Iklan';
                    window.alert(message);
                }
            });
        })();
    </script>
@endpush
