@php
    $title = $page['title'];
    $autoCampaignName = 'gotoads_sms_lba_' . now()->format('YmdHis');
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
    crossorigin="">
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css">
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
    <div class="status-box status-box--error">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
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
                            maxlength="11"
                            required
                        >
                    </div>
                    <div class="campaign-message-meta">
                        <span>Nama pengirim maksimal 11 karakter.</span>
                        <strong id="campaignSenderCount">0/11</strong>
                    </div>
                </div>

            {{-- <a href="#" class="campaign-template-link">{{ $isTargeted ? 'Gunakan dari Template' : 'Gunakan dari Template' }}</a> --}}

                <div class="field-group">
                    <label for="campaign_message" class="field-label">Isi Konten</label>
                    <textarea id="campaign_message" name="message" form="campaignGwSubmitForm" class="text-input campaign-message-input"></textarea>
                    <div class="campaign-message-meta">
                        <span>Mohon pastikan pesan iklan Anda tidak mengandung unsur terlarang.</span>
                        <strong id="campaignMessageCount">0/160 karakter (1SMS)</strong>
                    </div>
                </div>

            <section class="campaign-block">
                <div class="campaign-button-builder__head">
                    <div>
                        <h2 class="campaign-block__title">Tambah Link Singkat</h2>
                        <p class="campaign-block__copy">
                            {{ $isTargeted
                                    ? 'Tambahkan call to action ke pesan Anda dan lihat preview-nya langsung di sisi kanan.'
                                    : 'Tambahkan tombol aksi singkat ke pesan dan lihat preview-nya langsung di sisi kanan.' }}
                        </p>
                    </div>
                    <button type="button" class="campaign-outline-button campaign-outline-button--compact" id="campaignAddButton">
                        <span class="campaign-outline-button__icon">+</span>
                        <span>Tambah Link Singkat</span>
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

            <section class="campaign-block" hidden aria-hidden="true">
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

    @if ($isTargeted)
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
                        <h3>Gunakan Profil yang Tersimpan</h3>
                        <div class="campaign-targeted-step-two__saved-row">
                            <div class="field-group campaign-targeted-step-two__saved-field">
                                <label for="campaignRecipientProfile" class="field-label">Pilih Nama Profil</label>
                                <select id="campaignRecipientProfile" class="text-input text-input--select">
                                    <option value="">Belum ada profil tersimpan</option>
                                </select>
                            </div>
                            <button type="button" class="campaign-outline-button campaign-targeted-step-two__apply" id="campaignApplyAudience" disabled>Terapkan</button>
                        </div>
                    </div>

                    <div class="campaign-targeted-step-two__saved">
                        <h3>Buat Baru Profil Penerima</h3>
                        <div class="campaign-targeted-profile-grid">
                            <div class="field-group campaign-field-group--compact">
                                <label for="campaignTargetGender" class="field-label">Jenis Kelamin</label>
                                <select id="campaignTargetGender" class="text-input text-input--select">
                                    <option value="">Jenis Kelamin</option>
                                    <option>Laki-laki</option>
                                    <option>Perempuan</option>
                                    <option>Semua</option>
                                </select>
                            </div>
                            <div class="field-group campaign-field-group--compact">
                                <label for="campaignTargetAge" class="field-label">Rentang Umur</label>
                                <select id="campaignTargetAge" class="text-input text-input--select">
                                    <option value="">Rentang Umur</option>
                                    <option>&lt; 15 tahun</option>
                                    <option>15 - 24 tahun</option>
                                    <option>25 - 34 tahun</option>
                                    <option>35 - 44 tahun</option>
                                    <option>45+ tahun</option>
                                </select>
                            </div>
                            <div class="field-group campaign-field-group--compact">
                                <label for="campaignTargetOs" class="field-label">Tipe OS Handphone</label>
                                <select id="campaignTargetOs" class="text-input text-input--select">
                                    <option value="">Tipe OS Handphone</option>
                                    <option>Android</option>
                                    <option>iOS</option>
                                    <option>Semua</option>
                                </select>
                            </div>
                            <div class="field-group campaign-field-group--compact">
                                <label for="campaignTargetDataPackage" class="field-label">Penerima dengan Paket Data</label>
                                <select id="campaignTargetDataPackage" class="text-input text-input--select">
                                    <option value="">Penerima dengan Paket Data</option>
                                    <option>Aktif</option>
                                    <option>Tidak Aktif</option>
                                    <option>Semua</option>
                                </select>
                            </div>
                            <div class="field-group campaign-field-group--compact">
                                <label for="campaignTargetHouseholdSpend" class="field-label">Tingkat Pengeluaran Rumah Tangga (per Bulan)</label>
                                <select id="campaignTargetHouseholdSpend" class="text-input text-input--select">
                                    <option value="">Tingkat Pengeluaran Rumah Tangga</option>
                                    <option>&lt; Rp 2.000.000</option>
                                    <option>Rp 2.000.000 - Rp 5.000.000</option>
                                    <option>Rp 5.000.000 - Rp 10.000.000</option>
                                    <option>&gt; Rp 10.000.000</option>
                                </select>
                            </div>
                            <div class="field-group campaign-field-group--compact">
                                <label for="campaignTargetMaritalStatus" class="field-label">Status Pernikahan</label>
                                <select id="campaignTargetMaritalStatus" class="text-input text-input--select">
                                    <option value="">Marital Status</option>
                                    <option>Single</option>
                                    <option>Married</option>
                                    <option>Semua</option>
                                </select>
                            </div>
                            <div class="field-group campaign-field-group--compact">
                                <label for="campaignTargetLifestyle" class="field-label">Lifestyle Segment</label>
                                <select id="campaignTargetLifestyle" class="text-input text-input--select">
                                    <option value="">Lifestyle Segment</option>
                                    <option>Foodies</option>
                                    <option>Traveler</option>
                                    <option>Family</option>
                                    <option>Semua</option>
                                </select>
                            </div>
                            <div class="field-group campaign-field-group--compact">
                                <label for="campaignTargetReligion" class="field-label">Agama</label>
                                <select id="campaignTargetReligion" class="text-input text-input--select">
                                    <option value="">Agama</option>
                                    <option>Islam</option>
                                    <option>Kristen</option>
                                    <option>Katolik</option>
                                    <option>Hindu</option>
                                    <option>Budha</option>
                                    <option>Konghucu</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    {{-- <a href="#" class="campaign-inline-link campaign-inline-link--strong campaign-inline-link--arrow campaign-inline-link--fit">Gunakan Pengaturan Audience</a> --}}
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
        @else
        <div class="campaign-step-panel" data-campaign-panel="2" hidden>
            <section class="campaign-step-two">
                <div class="campaign-step-two__section">
                    <h2 class="campaign-block__title">Tentukan Jumlah Penerima yang Akan Dikirim</h2>
                    <div class="campaign-send-grid">
                        <div class="field-group campaign-field-group--compact">
                            <label for="campaignRecipientCount" class="field-label">Jumlah Penerima</label>
                            <input id="campaignRecipientCount" name="recipient_total" form="campaignGwSubmitForm" type="number" class="text-input" min="5" value="0" placeholder="Jumlah Penerima">
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
                                        <input id="campaignScheduleDate1Start" name="delivery_start_date" form="campaignGwSubmitForm" type="date" class="text-input" value="{{ now()->addDay()->toDateString() }}" min="{{ now()->toDateString() }}" data-schedule-date-start="1">
                                        <span class="campaign-range-field__separator">-</span>
                                        <input id="campaignScheduleDate1End" name="delivery_end_date" form="campaignGwSubmitForm" type="date" class="text-input" value="{{ now()->addDay()->toDateString() }}" min="{{ now()->toDateString() }}" data-schedule-date-end="1">
                                    </div>
                                    <p class="campaign-range-preview" id="campaignScheduleDatePreview1">{{ now()->addDay()->format('d/m/y') }} - {{ now()->addDay()->format('d/m/y') }}</p>
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
                        Saya menyetujui <a href="{{ route('campaign.terms') }}" data-campaign-terms-trigger>Syarat dan Ketentuan</a> yang berlaku di website Telkomsel MyAds. Atas setiap pesan iklan yang dibuat oleh pengguna menggunakan produk dan/atau layanan melalui portal myAds, pengguna dilarang untuk mempergunakan kata-kata, komentar, gambar atau konten apapun yang mengandung unsur SARA atau diskriminasi terhadap pihak manapun, bersifat vulgar dan ancaman, atau hal-hal lain yang dapat dianggap tidak sesuai dengan nilai dan norma sosial.
                    </span>
                </label>
            </div>

            @include('partials.campaign-terms-modal')

            <div class="campaign-form-actions campaign-form-actions--step-two">
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
    @endif

    <div class="campaign-step-panel" data-campaign-panel="3" hidden>
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
                            <button type="button" class="campaign-review-card__link" data-review-edit="2">Ubah</button>
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
                            <strong id="campaignReviewRecipientCount">0 Pesan</strong>
                        </div>
                        <p class="campaign-review-note">dari estimasi <span id="campaignReviewPotentialRange">-</span> penerima potensial</p>
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
                                    <strong>LBA</strong>
                                </div>
                                <div class="campaign-cost-card__row">
                                    <span>Tipe Kanal</span>
                                    <strong>SMS</strong>
                                </div>
                                <div class="campaign-cost-card__row">
                                    <span>Harga Satuan</span>
                                    <strong id="campaignReviewUnitPrice">-</strong>
                                </div>
                                <div class="campaign-cost-card__row">
                                    <span>Subtotal</span>
                                    <strong id="campaignReviewSubtotal">-</strong>
                                </div>
                                <div class="campaign-cost-card__row">
                                    <span>PPN (11%)</span>
                                    <strong id="campaignReviewTax">-</strong>
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
                                    <span>Saldo Refund</span>
                                    <strong class="campaign-cost-card__danger">Rp 0</strong>
                                </div>
                            </div>
                            @include('partials.payment-invoice-email', [
                                'inputId' => 'campaignPaymentInvoiceEmail',
                                'checkboxId' => 'campaignPaymentInvoiceEmailCheck',
                            ])
                            @if($errors->has('gw'))
                                <div class="alert alert-danger" style="color: #ff1f3d; background: #ffeef0; padding: 12px; border-radius: 8px; margin-bottom: 16px; border: 1px solid #ffaeb5; font-size: 14px; font-weight: bold; text-align: left;">
                                    {{ $errors->first('gw') }}
                                </div>
                            @endif
                            <p class="campaign-payment-error" id="campaignPaymentError" hidden></p>
                            <button type="button" class="campaign-cost-card__cta" id="campaignGwSubmitButton">Bayar & Kirim Iklan</button>
                            @include('partials.payment-qris-modal', [
                                'modalId' => 'campaignPaymentModal',
                                'panelId' => 'campaignPaymentPanel',
                                'statusId' => 'campaignPaymentStatus',
                                'transactionId' => 'campaignPaymentTransaction',
                                'qrId' => 'campaignPaymentQr',
                                'qrLinkId' => 'campaignPaymentQrLink',
                                'downloadId' => 'campaignPaymentQrDownload',
                            ])
                            <form id="campaignGwSubmitForm" method="POST" action="{{ route('campaign.lba.send', ['channel' => $channel]) }}" hidden>
                                @csrf
                                <input type="hidden" name="campaign_name" id="campaign_title" value="{{ $autoCampaignName }}">
                                <input type="hidden" name="content_name" id="gwContentName" value="{{ $autoCampaignName }}">
                                <input type="hidden" name="location_type" id="gwLocationType" value="ALL">
                                <input type="hidden" name="delivery_start_time" id="gwDeliveryStartTime">
                                <input type="hidden" name="delivery_end_time" id="gwDeliveryEndTime">
                                <input type="hidden" name="location_name" id="gwLocationName">
                                <input type="hidden" name="location_lat" id="gwLocationLat">
                                <input type="hidden" name="location_lng" id="gwLocationLng">
                                <input type="hidden" name="email_invoice" id="gwCustomerEmail" value="">
                                <input type="hidden" name="payment_transaction_id" id="gwPaymentTransactionId" value="">
                            </form>
                        </div>
                    </div>
                </aside>
                <div class="campaign-form-actions campaign-form-actions--review campaign-form-actions--review-bottom">
                    <button type="button" class="campaign-outline-button campaign-step-nav-btn" id="campaignStepThreeBack">Sebelumnya</button>
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
                                <input id="campaignLocationRadius" name="location_radius" form="campaignGwSubmitForm" type="number" class="text-input" min="0" step="100" value="300">
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
    window.openCampaignLocationModal = function() {
        const body = document.body;
        const modal = document.getElementById('campaignMapModal');

        if (!modal) {
            return;
        }

        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        body.classList.add('campaign-map-open');
    };

    window.closeCampaignLocationModal = function() {
        const body = document.body;
        const modal = document.getElementById('campaignMapModal');

        if (!modal) {
            return;
        }

        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        body.classList.remove('campaign-map-open');
    };

    window.applyCampaignLocationSelection = function() {
        const button = document.getElementById('campaignOpenLocationModal');
        const buttonIcon = document.getElementById('campaignLocationButtonIcon');
        const buttonContent = document.getElementById('campaignLocationButtonContent');
        const radiusInput = document.getElementById('campaignLocationRadius');
        const state = window.campaignLocationState || {};

        if (!button || !buttonContent) {
            return;
        }

        const formatCoordinate = function(value) {
            const parsed = Number(value);
            return Number.isFinite(parsed) ? parsed.toFixed(6) : '-';
        };

        const formatNumber = function(value) {
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
        window.paymentQrisRedirectUrl = "{{ route('campaign.menu', ['channel' => $channel, 'menu' => $menu]) }}";
        const isTargeted = "{{ $isTargeted ? 'true' : 'false' }}" === 'true';
        const body = document.body;
        const titleInput = document.getElementById('campaign_title');
        const titlePreview = document.getElementById('campaignTitlePreview');
        const senderInput = document.getElementById('campaign_sender');
        const senderPreview = document.getElementById('campaignSenderPreview');
        const senderCount = document.getElementById('campaignSenderCount');
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
        const targetedRecipient = document.getElementById('campaignTargetRecipient');
        const targetedProfileBuilder = document.getElementById('campaignTargetedProfileBuilder');
        const recipientProfile = document.getElementById('campaignRecipientProfile');
        const applyAudienceButton = document.getElementById('campaignApplyAudience');
        const targetedProfileFields = Array.from(document.querySelectorAll('#campaignTargetedProfileBuilder select')).filter((field) => field.id !== 'campaignRecipientProfile');
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
        let profileDropdownSource = null;

        const getGatewayOptionValue = (option) => {
            if (option && typeof option === 'object') {
                const value = option.value ?? option.code ?? option.id;
                return value === null || value === undefined ? '' : String(value);
            }
            return option === null || option === undefined ? '' : String(option);
        };

        const getGatewayOptionLabel = (option) => {
            if (option && typeof option === 'object') {
                const label = option.label ?? option.name ?? option.text;
                const value = option.value ?? option.code ?? option.id;
                if (label !== null && label !== undefined && String(label).trim() !== '') {
                    return String(label);
                }
                return value === null || value === undefined ? '' : String(value);
            }
            return option === null || option === undefined ? '' : String(option);
        };

        const findProfileDropdownOptions = (source, keys = []) => {
            if (!source || typeof source !== 'object') {
                return [];
            }

            for (const key of keys) {
                const raw = source?.[key];
                if (Array.isArray(raw) && raw.length > 0) {
                    return raw
                        .map((item) => ({
                            value: getGatewayOptionValue(item),
                            label: getGatewayOptionLabel(item),
                        }))
                        .filter((item) => item.value !== '' || item.label !== '');
                }
            }

            return [];
        };

        const setSelectOptions = (selectId, options, { keepFirstEmpty = false } = {}) => {
            const select = document.getElementById(selectId);
            if (!select || !Array.isArray(options) || options.length === 0) {
                return;
            }

            const selectedValues = new Set(Array.from(select.selectedOptions || []).map((opt) => opt.value));
            const placeholder = keepFirstEmpty && select.options?.length && select.options[0].value === ''
                ? { value: '', label: select.options[0].textContent || '' }
                : null;

            select.innerHTML = '';
            if (placeholder) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = placeholder.label;
                select.appendChild(opt);
            }

            options.forEach((item) => {
                const opt = document.createElement('option');
                opt.value = item.value;
                opt.textContent = item.label;
                if (selectedValues.has(item.value)) {
                    opt.selected = true;
                }
                select.appendChild(opt);
            });
        };

        const loadProfileDropdownOptions = async () => {
            if (!profileDropdownUrl) {
                return;
            }

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

                setSelectOptions('campaign_gender', findProfileDropdownOptions(source, ['gender', 'profileGender', 'jenisKelamin']));
                setSelectOptions('campaign_age', findProfileDropdownOptions(source, ['age', 'profileAge', 'rentangUmur']));
                setSelectOptions('campaign_religion', findProfileDropdownOptions(source, ['religion', 'religions', 'profileReligion', 'agama']));

                setSelectOptions('campaignTargetGender', findProfileDropdownOptions(source, ['gender', 'profileGender', 'jenisKelamin']), { keepFirstEmpty: true });
                setSelectOptions('campaignTargetAge', findProfileDropdownOptions(source, ['age', 'profileAge', 'rentangUmur']), { keepFirstEmpty: true });
                setSelectOptions('campaignTargetOs', findProfileDropdownOptions(source, ['os', 'profileOs', 'operatingSystem']), { keepFirstEmpty: true });
                setSelectOptions('campaignTargetDataPackage', findProfileDropdownOptions(source, ['dataPackage', 'packageData', 'paketData']), { keepFirstEmpty: true });
                setSelectOptions('campaignTargetHouseholdSpend', findProfileDropdownOptions(source, ['householdSpend', 'householdExpense', 'pengeluaranRumahTangga']), { keepFirstEmpty: true });
                setSelectOptions('campaignTargetMaritalStatus', findProfileDropdownOptions(source, ['maritalStatus', 'marriage', 'statusPernikahan']), { keepFirstEmpty: true });
                setSelectOptions('campaignTargetLifestyle', findProfileDropdownOptions(source, ['lifestyleseg', 'lifestyle', 'lifestyleSegment', 'profileLifestyle', 'profileLifestyleSegment']), { keepFirstEmpty: true });
                setSelectOptions('campaignTargetReligion', findProfileDropdownOptions(source, ['religion', 'religions', 'profileReligion', 'agama']), { keepFirstEmpty: true });
            } catch (error) {
                console.warn('Profile dropdown API gagal dimuat', error);
            }
        };

        let buttonIndex = 0;
        const locationState = {
            name: '',
            audience: null,
            lat: -6.1944491,
            lng: 106.8229198,
        };
        window.campaignLocationState = locationState;
        let liveMap = null;
        let liveMarker = null;
        let liveCircle = null;
        let geocoderControl = null;
        let scheduleIndex = 1;

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
            const visualStepNumber = isTargeted && stepNumber === 3 ? 4 : stepNumber;

            campaignPanels.forEach((panel) => {
                panel.hidden = panel.dataset.campaignPanel !== String(stepNumber);
            });

            campaignSteps.forEach((step) => {
                const stepValue = Number(step.dataset.campaignStep);
                step.classList.toggle('campaign-step--active', stepValue === visualStepNumber);
                step.classList.toggle('campaign-step--completed', stepValue < visualStepNumber);
            });

            if (sectionKicker) {
                sectionKicker.textContent = `Step ${String(stepNumber).padStart(2, '0')}`;
            }

            if (sectionHeading) {
                sectionHeading.textContent =
                    stepNumber === 2 ? 'Atur Pengiriman' :
                    stepNumber === 3 ? 'Review & Pembayaran' :
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

            for (let hour = 8; hour <= 18; hour += 1) {
                for (let minute = 0; minute < 60; minute += 30) {
                    const value = `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
                    options.push(`<option value="${value}"${value === selectedValue ? ' selected' : ''}>${value}</option>`);
                }
            }

            return options.join('');
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

        const formatCurrency = (value) => `Rp ${new Intl.NumberFormat('id-ID').format(Number(value || 0))}`;

        const unitPrice = Number("{{ (int) ($unitPrice ?? 0) }}") || 0;
        const formatUnitPrice = () => unitPrice > 0 ? formatCurrency(unitPrice) : '-';
        const getSmsSegmentCount = () => {
            const messageLength = sanitizeSmsBody(messageInput?.value || '').length;
            return Math.max(1, Math.ceil(messageLength / 160));
        };

        const hasTargetedProfileFilters = () => targetedProfileFields.some((field) => field?.value?.trim());

        // NOTE: Saved profile presets are not wired to an API yet.
        // Keep empty to avoid showing/applying dummy preset data.
        const profileTemplates = {};

        const syncTargetedProfileBuilder = () => {
            if (!targetedProfileBuilder || !targetedRecipient) {
                return;
            }

            const shouldShow = targetedRecipient.value === 'profile-recipient';
            targetedProfileBuilder.hidden = !shouldShow;
            targetedProfileBuilder.classList.toggle('campaign-targeted-profile-builder--active', shouldShow);
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
            const totalCost = unitPrice > 0 ? recipientCount * unitPrice * getSmsSegmentCount() : 0;
            const taxPercentage = 11.00;
            const taxAmount = Number((totalCost * (taxPercentage / 100)).toFixed(2));
            const grandTotal = Number((totalCost + taxAmount).toFixed(2));

            const totalCostLabel = unitPrice > 0 ? formatCurrency(totalCost) : '-';
            const taxLabel = unitPrice > 0 ? formatCurrency(taxAmount) : '-';
            const grandTotalLabel = unitPrice > 0 ? formatCurrency(grandTotal) : '-';

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
                const genderValue = document.getElementById('campaignTargetGender')?.value || recipientProfile?.selectedOptions?.[0]?.text || 'Semua';
                const ageValue = document.getElementById('campaignTargetAge')?.value || 'Semua';
                const religionValue = document.getElementById('campaignTargetReligion')?.value || 'Semua';

                setText('campaignReviewGender', genderValue);
                setText('campaignReviewAge', ageValue);
                setText('campaignReviewReligion', religionValue);
            }
            setText('campaignReviewDeliveryMethod', deliveryMethod?.value || 'Metode Pengiriman Pesan');
            setText('campaignReviewTestRecipient', testRecipient?.value || 'Belum dipilih');
            setText('campaignReviewRecipientCount', `${normalizeAudience(recipientCount)} Pesan`);
            setText('campaignReviewPotentialRange', normalizeAudience(locationState.audience));
            setText('campaignReviewUnitPrice', formatUnitPrice());
            setText('campaignReviewSubtotal', totalCostLabel);
            setText('campaignReviewTax', taxLabel);
            setText('campaignReviewGrandTotal', grandTotalLabel);
            setText('campaignReviewPaymentMonetary', grandTotalLabel);

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

        const hasSelectedLocation = () => openLocationModalButton?.classList.contains('campaign-outline-button--applied');

        const syncStepOneValidity = () => {
            if (!stepOneNext) {
                return;
            }

            stepOneNext.disabled = !isTargeted && !hasSelectedLocation();
        };

        const syncStepTwoValidity = () => {
            if (stepTwoNext) {
                const isRecipientScheduleValid = syncRecipientScheduleValidity();
                stepTwoNext.disabled = isTargeted ?
                    !targetedRecipient?.value :
                    (!termsCheck?.checked || !isRecipientScheduleValid);
            }
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
                                <input id="campaignScheduleDate${scheduleIndex}Start" type="date" class="text-input" value="${defaults.startDate}" min="${toDateInputValue(new Date())}" data-schedule-date-start="${scheduleIndex}">
                                <span class="campaign-range-field__separator">-</span>
                                <input id="campaignScheduleDate${scheduleIndex}End" type="date" class="text-input" value="${defaults.endDate}" min="${toDateInputValue(new Date())}" data-schedule-date-end="${scheduleIndex}">
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
            syncStepTwoValidity();
        };

        const renderPhoneActions = () => {
            if (!buttonBuilder || !phoneActions) {
                return;
            }

            phoneActions.innerHTML = '';
        };

        const escapeHtml = (value) => String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const escapeRegExp = (value) => String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

        const linkifyMessage = (value) => escapeHtml(value).replace(
            /(https?:\/\/[^\s<]+|www\.[^\s<]+)/g,
            '<span class="campaign-phone__url">$1</span>'
        );

        const defaultCtaLink = @json($defaultCtaLink ?? '');
        const normalizeCtaUrl = (value) => String(value || '').trim();
        const buildCtaUrl = (value) => normalizeCtaUrl(value);

        const syncUrlIntoMessage = (card) => {
            if (!messageInput) {
                return;
            }

            const websiteInput = card.querySelector('[data-action-url]');
            const previousUrl = card.dataset.syncedUrl || '';
            const nextUrl = buildCtaUrl(websiteInput?.value);
            let message = messageInput.value;

            if (previousUrl !== '') {
                message = message.replace(new RegExp(`\\s*${escapeRegExp(previousUrl)}`, 'g'), '');
            }

            if (nextUrl !== '') {
                message = `${message.trimEnd()}${message.trim() === '' ? '' : ' '}${nextUrl}`;
            }

            card.dataset.syncedUrl = nextUrl;
            messageInput.value = message;
            syncMessagePreview();
        };

        const updateButtonCardMeta = (card) => {
            const websiteInput = card.querySelector('[data-action-url]');
            const urlMeta = card.querySelector('[data-url-meta]');
            const normalizedUrl = normalizeCtaUrl(websiteInput.value);

            if (websiteInput.value !== normalizedUrl) {
                websiteInput.value = normalizedUrl;
            }

            const fullUrlLength = buildCtaUrl(normalizedUrl).length;
            urlMeta.innerHTML = `${fullUrlLength} / 2000`;
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

            const defaultUrl = normalizeCtaUrl(defaultCtaLink);

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
                                <option value="short_url">Short URL</option>
                            </select>
                        </div>
                        <div class="campaign-button-card__field">
                            <label>Website URL</label>
                            <input type="url" class="text-input" data-action-url maxlength="2000" value="${defaultUrl}" placeholder="https://gofood.link/..." readonly>
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

        const sanitizeSmsBody = (value) => String(value || '').replace(/[\r\n\t]+/g, ' ');

        const syncMessagePreview = () => {
            const sanitizedValue = sanitizeSmsBody(messageInput.value);
            if (messageInput.value !== sanitizedValue) {
                messageInput.value = sanitizedValue;
            }

            const message = sanitizedValue.trim();
            messagePreview.innerHTML = message ? linkifyMessage(message) : 'Preview pesan akan muncul di sini.';
            const messageLength = sanitizedValue.length;
            const smsCount = getSmsSegmentCount();
            messageCount.textContent = `${messageLength}/${smsCount * 160} karakter (${smsCount}SMS)`;
        };

        const syncSenderPreview = () => {
            if (senderPreview && senderInput) {
                senderPreview.textContent = senderInput.value;
            }

            if (senderCount && senderInput) {
                senderCount.textContent = `${senderInput.value.length}/11`;
            }
        };

        const normalizeAudience = (value) => {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            const amount = Number(value);
            return Number.isFinite(amount) ? new Intl.NumberFormat('id-ID').format(amount) : '-';
        };
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

        const getSelectedTestRecipients = () => Array.from(testRecipientList?.querySelectorAll('input[type="checkbox"]:checked') || [])
            .map((checkbox) => checkbox.closest('[data-test-recipient]')?.dataset.testRecipient || '')
            .filter(Boolean);

        const updateTestRecipientField = () => {
            if (!testRecipientField) {
                return;
            }

            testRecipientField.value = getSelectedTestRecipients().join(',');
            syncStepTwoValidity();
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

        const syncLocationButtonSummary = () => {
            window.campaignLocationState = locationState;
            window.applyCampaignLocationSelection && window.applyCampaignLocationSelection();
        };

        const calculateAudience = () => {
            // Audience estimation API is not available; avoid showing dummy numbers.
            locationState.audience = null;
            if (audienceEstimate) {
                audienceEstimate.textContent = '-';
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

        await loadProfileDropdownOptions();
        syncMessagePreview();
        syncSenderPreview();
        initCampaignMultiselects();
        calculateAudience();
        closeLocationModal();
        syncCampaignStep(1);
        syncScheduleRemoveState();
        applyDefaultDeliverySchedule(1);
        syncTargetedProfileBuilder();
        syncStepOneValidity();
        syncStepTwoValidity();

        openLocationModalButton?.addEventListener('click', openLocationModal);
        stepOneNext?.addEventListener('click', () => {
            if (isTargeted) {
                syncCampaignStep(2);
                return;
            }

            if (!hasSelectedLocation()) {
                window.alert('Pilih lokasi target terlebih dahulu.');
                syncStepOneValidity();
                return;
            }

            syncCampaignStep(2);

            fetch("{{ route('campaign.track-event') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ event_type: 'campaign_setup_page' })
            }).catch(err => console.warn('Tracking failed:', err));
        });
        stepTwoBack?.addEventListener('click', () => syncCampaignStep(1));
        stepTwoNext?.addEventListener('click', () => {
            syncReviewSummary();
            collapseReviewCards();
            syncCampaignStep(3);

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
        stepThreeBack?.addEventListener('click', () => syncCampaignStep(isTargeted ? 1 : 2));
        addScheduleButton?.addEventListener('click', addScheduleItem);
        termsCheck?.addEventListener('change', syncStepTwoValidity);
        recipientCountInput?.addEventListener('input', syncStepTwoValidity);
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
        recipientProfile?.addEventListener('change', syncStepTwoValidity);
        applyAudienceButton?.addEventListener('click', () => {
            const selectedTemplate = profileTemplates[recipientProfile?.value];

            if (!selectedTemplate) {
                syncStepTwoValidity();
                return;
            }

            const fieldMap = {
                campaignTargetGender: selectedTemplate.gender,
                campaignTargetAge: selectedTemplate.age,
                campaignTargetOs: selectedTemplate.os,
                campaignTargetDataPackage: selectedTemplate.dataPackage,
                campaignTargetHouseholdSpend: selectedTemplate.householdSpend,
                campaignTargetMaritalStatus: selectedTemplate.maritalStatus,
                campaignTargetLifestyle: selectedTemplate.lifestyle,
                campaignTargetReligion: selectedTemplate.religion,
            };

            Object.entries(fieldMap).forEach(([fieldId, fieldValue]) => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.value = fieldValue;
                }
            });

            syncStepTwoValidity();
        });
        targetedProfileFields.forEach((field) => {
            field.addEventListener('change', syncStepTwoValidity);
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
            syncStepTwoValidity();
        });
        document.addEventListener('click', (event) => {
            const editButton = event.target.closest('[data-review-edit]');
            if (editButton) {
                const editStep = Number(editButton.dataset.reviewEdit);
                syncCampaignStep(isTargeted && editStep === 2 ? 1 : editStep);
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
                syncStepTwoValidity();
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
                syncStepTwoValidity();
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
            syncStepTwoValidity();
            syncReviewSummary();
        };

        document.addEventListener('campaign:draft-restored', syncDraftScheduleState);

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
            syncStepOneValidity();
            syncStepTwoValidity();
            closeLocationModal();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !locationModal.hidden) {
                closeLocationModal();
            }
        });

        senderInput?.addEventListener('input', syncSenderPreview);
        messageInput?.addEventListener('input', syncMessagePreview);
        addButton?.addEventListener('click', createButtonCard);

            const gwSubmitButton = document.getElementById('campaignGwSubmitButton');
            const gwSubmitForm = document.getElementById('campaignGwSubmitForm');
            const gwDeliveryStartTime = document.getElementById('gwDeliveryStartTime');
            const gwDeliveryEndTime = document.getElementById('gwDeliveryEndTime');
            const gwLocationName = document.getElementById('gwLocationName');
            const gwLocationLat = document.getElementById('gwLocationLat');
            const gwLocationLng = document.getElementById('gwLocationLng');
            const gwCustomerEmail = document.getElementById('gwCustomerEmail');
            const gwPaymentTransactionId = document.getElementById('gwPaymentTransactionId');

            const paymentInitiateUrl = "{{ route('campaign.payment.initiate') }}";
            const paymentStatusUrlTemplate = "{{ route('campaign.payment.status', ['transactionId' => '__TRANSACTION_ID__']) }}";
            const qrisDownloadUrl = "{{ route('campaign.payment.qris-download') }}";
            const csrfToken = "{{ csrf_token() }}";
            const paymentCustomerPhone = '6285129640197';
            const defaultInvoiceEmail = 'myads@telkomsel.com';
            const paymentInvoiceEmail = document.getElementById('campaignPaymentInvoiceEmail');
            const paymentInvoiceEmailCheck = document.getElementById('campaignPaymentInvoiceEmailCheck');
            const paymentError = document.getElementById('campaignPaymentError');

            const paymentPanel = document.getElementById('campaignPaymentPanel');
            const paymentStatusLabel = document.getElementById('campaignPaymentStatus');
            const paymentTransactionLabel = document.getElementById('campaignPaymentTransaction');
            const paymentQr = document.getElementById('campaignPaymentQr');
            const paymentQrLink = document.getElementById('campaignPaymentQrLink');

            const toIsoStamp = () => {
                const d = new Date();
                const pad = (n) => String(n).padStart(2, '0');
                return `${d.getFullYear()}${pad(d.getMonth() + 1)}${pad(d.getDate())}${pad(d.getHours())}${pad(d.getMinutes())}${pad(d.getSeconds())}`;
            };

        const pickScheduleValue = (selector) => {
            const el = document.querySelector(selector);
            return el && typeof el.value === 'string' ? el.value : '';
        };

        const sleep = (ms) => new Promise((resolve) => window.setTimeout(resolve, ms));

        const normalizePaymentErrorMessage = (message = '') => {
            return String(message).toLowerCase().includes('unauthorized')
                ? 'Session expired. Silakan login ulang.'
                : (message || 'Gagal memproses pembayaran.');
        };

        const setPaymentError = (message = '') => {
            if (!paymentError) {
                return;
            }

            paymentError.textContent = message;
            paymentError.hidden = message === '';
        };

        const resolvePaymentInvoiceEmail = () => {
            if (!paymentInvoiceEmailCheck?.checked) {
                return defaultInvoiceEmail;
            }

            const email = paymentInvoiceEmail?.value?.trim() || '';
            if (!email || !paymentInvoiceEmail?.checkValidity()) {
                throw new Error('Email invoice tidak valid.');
            }

            return email;
        };

        const setPaymentUi = ({ status, transactionId, qrisUrl, forceSuccess } = {}) => {
            const paymentModal = paymentPanel?.closest('.campaign-payment-modal');
            const paymentQrDownload = paymentModal?.querySelector('[download]');
            const qrisPanel = document.getElementById('campaignPaymentPanel');
            const successPanel = document.getElementById('campaignPaymentPanelSuccess');
            const failurePanel = document.getElementById('campaignPaymentPanelFailure');

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
                    const msgEl = document.getElementById('campaignPaymentPanelFailureMsg');
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

            if (paymentStatusLabel) {
                paymentStatusLabel.textContent = status || '-';
            }
            if (paymentTransactionLabel) {
                paymentTransactionLabel.textContent = transactionId || '-';
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
                        const successBtn = document.getElementById('campaignPaymentPanelSuccessBtn');
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
                const message = payload?.message || 'Gagal initiate pembayaran.';
                throw new Error(message);
            }

            return payload;
        };

        const submitCampaignAfterPayment = async () => {
            if (!gwSubmitForm || !gwDeliveryStartTime || !gwDeliveryEndTime || !gwLocationName || !gwLocationLat || !gwLocationLng || !gwCustomerEmail || !gwPaymentTransactionId) {
                return;
            }

            setPaymentError('');

            if (!testRecipientField?.value && testRecipientInput?.value?.trim()) {
                addTestRecipient();
            }

            const recipientCount = Number(document.getElementById('campaignRecipientCount')?.value || 0);
            const totalCost = unitPrice > 0 ? recipientCount * unitPrice * getSmsSegmentCount() : 0;
            const controlNumbers = String(document.getElementById('campaignTestRecipient')?.value || '').trim();
            const phone = paymentCustomerPhone;
            const title = String(titleInput?.value || '').trim() || 'Tanpa Judul';

            if (!phone) {
                throw new Error('Nomor penerima tes belum diisi.');
            }
            if (!totalCost || totalCost <= 0) {
                throw new Error('Total biaya tidak valid.');
            }

            gwSubmitButton.disabled = true;
            gwSubmitButton.textContent = 'Menyiapkan pembayaran...';

            const invoiceEmail = resolvePaymentInvoiceEmail();
            const initiateResp = await initiatePayment(totalCost, phone, `SMS LBA - ${title}`);
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
            const startHour = pickScheduleValue(`[data-schedule-time-start-hour="${scheduleId}"]`);
            const startMinute = pickScheduleValue(`[data-schedule-time-start-minute="${scheduleId}"]`);
            const endHour = pickScheduleValue(`[data-schedule-time-end-hour="${scheduleId}"]`);
            const endMinute = pickScheduleValue(`[data-schedule-time-end-minute="${scheduleId}"]`);

            if (titleInput && titleInput.value.trim() === '') {
                titleInput.value = `UAT SMS LBA ${toIsoStamp()}`;
            }

            gwDeliveryStartTime.value = startHour && startMinute ? `${startHour}:${startMinute}` : '';
            gwDeliveryEndTime.value = endHour && endMinute ? `${endHour}:${endMinute}` : '';

            const state = window.campaignLocationState || {};
            gwLocationName.value = String(state.name || 'Lokasi dipilih');
            gwLocationLat.value = String(state.lat ?? '');
            gwLocationLng.value = String(state.lng ?? '');
            gwCustomerEmail.value = invoiceEmail;

            gwSubmitForm.submit();
        };

        gwSubmitButton?.addEventListener('click', async () => {
            try {
                await submitCampaignAfterPayment();
            } catch (error) {
                const message = normalizePaymentErrorMessage(error?.message ? String(error.message) : '');
                setPaymentUi({ status: 'FAILED' });
                setPaymentError(message);
                gwSubmitButton.disabled = false;
                gwSubmitButton.textContent = 'Bayar & Kirim Iklan';
            }
        });
    })();
</script>
@include('partials.campaign-draft-state', [
    'storageKey' => "campaign-draft:{$channel}:{$menu}",
])
@endpush
