@extends('layouts.voucher')

@section('title', 'Setup Campaign SMS LBA - MyAds')
@section('body-class', 'voucher-mobile-preview voucher-campaign-page')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css">
@endpush

@section('content')
    <span class="voucher-badge">Voucher {{ $voucher['code'] }} berhasil diredeem</span>
    <h2>Setup campaign SMS LBA</h2>
    <p class="voucher-description">Paket Rp {{ number_format($voucher['price'], 0, ',', '.') }}. Lengkapi pesan, lokasi target, dan jadwal campaign Anda.</p>

    <dl class="voucher-package-summary">
        <div><dt>Jenis campaign</dt><dd>SMS LBA</dd></div>
        <div><dt>Kuota paket</dt><dd>{{ number_format($voucher['sms_quota'], 0, ',', '.') }} pesan</dd></div>
        <div><dt>Durasi report</dt><dd>{{ $voucher['report_days'] }} hari</dd></div>
    </dl>

    @if(session('status'))
        <div class="voucher-success" role="status">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="error-text" role="alert">
            <p>Periksa kembali data campaign:</p>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="voucherCampaignForm" method="POST" action="{{ route('voucher.campaign.store') }}" class="voucher-campaign-form">
        @csrf
        <fieldset>
            <legend>1. Pesan campaign</legend>
            <div class="field-group">
                <label for="campaign_name" class="field-label">Nama campaign</label>
                <input id="campaign_name" name="campaign_name" class="text-input" value="{{ old('campaign_name', $campaign['campaign_name'] ?? '') }}" placeholder="Contoh: Promo toko akhir pekan" maxlength="100" required>
            </div>
            <div class="field-group">
                <label for="sender_name" class="field-label">Nama pengirim</label>
                <input id="sender_name" name="sender_name" class="text-input" value="{{ old('sender_name', $campaign['sender_name'] ?? '') }}" placeholder="Contoh: TOKOKITA" maxlength="11" pattern="[A-Za-z0-9 ]+" aria-describedby="sender-help" required>
                <p id="sender-help" class="voucher-help">Maksimal 11 karakter: huruf, angka, atau spasi.</p>
            </div>
            <div class="field-group">
                <label for="message" class="field-label">Pesan SMS</label>
                <textarea id="message" name="message" class="text-input" rows="4" maxlength="160" placeholder="Tulis penawaran untuk pelanggan Anda..." aria-describedby="message-help" required>{{ old('message', $campaign['message'] ?? '') }}</textarea>
                <p id="message-help" class="voucher-help">Maksimal 160 karakter untuk preview SMS dummy.</p>
            </div>
            <div class="field-group">
                <label for="recipient_total" class="field-label">Jumlah penerima</label>
                <input id="recipient_total" type="text" class="text-input" value="{{ number_format($voucher['sms_quota'], 0, ',', '.') }} penerima" readonly aria-describedby="recipient-help">
                <p id="recipient-help" class="voucher-help">Jumlah penerima dikunci sesuai paket voucher.</p>
            </div>
        </fieldset>

        <fieldset>
            <legend>2. Lokasi target LBA</legend>
            <input id="location_name" name="location_name" type="hidden" value="{{ old('location_name', $campaign['location_name'] ?? '') }}">
            <input id="location_lat" name="location_lat" type="hidden" value="{{ old('location_lat', $campaign['location_lat'] ?? '') }}">
            <input id="location_lng" name="location_lng" type="hidden" value="{{ old('location_lng', $campaign['location_lng'] ?? '') }}">
            <input id="location_radius" name="location_radius" type="hidden" value="{{ old('location_radius', $campaign['location_radius'] ?? 1000) }}">
            <div class="voucher-location-summary" aria-live="polite">
                <strong id="voucherLocationName">{{ old('location_name', $campaign['location_name'] ?? '') ?: 'Belum ada lokasi dipilih' }}</strong>
                <p id="voucherLocationDetails">@if(old('location_lat', $campaign['location_lat'] ?? '') !== ''){{ old('location_lat', $campaign['location_lat'] ?? '') }}, {{ old('location_lng', $campaign['location_lng'] ?? '') }} &middot; Radius {{ old('location_radius', $campaign['location_radius'] ?? 1000) }} meter @else Cari alamat atau pilih titik melalui peta. @endif</p>
            </div>
            <button type="button" id="voucherOpenLocation" class="campaign-outline-button" aria-haspopup="dialog" aria-controls="campaignMapModal">Atur lokasi di peta</button>
            <p class="voucher-help">Pilih titik target dan atur radius, lalu tekan Gunakan Lokasi.</p>
        </fieldset>

        <fieldset>
            <legend>3. Jadwal pengiriman</legend>
            @foreach(['delivery_start_date' => 'Tanggal mulai', 'delivery_end_date' => 'Tanggal selesai'] as $field => $label)
                <div class="field-group">
                    <label for="{{ $field }}" class="field-label">{{ $label }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" type="date" class="text-input" min="{{ $earliestStartDate }}" value="{{ old($field, $campaign[$field] ?? $scheduleDefaults[$field]) }}" required>
                </div>
            @endforeach
            <div class="voucher-coordinate-fields">
                @foreach(['delivery_start_time' => 'Jam mulai (WIB)', 'delivery_end_time' => 'Jam selesai (WIB)'] as $field => $label)
                    <div class="field-group">
                        <label for="{{ $field }}" class="field-label">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" type="time" class="text-input" min="06:00" max="20:00" step="60" value="{{ old($field, $campaign[$field] ?? $scheduleDefaults[$field]) }}" required>
                    </div>
                @endforeach
            </div>
            <p class="voucher-help">Jam pengiriman 06.00-20.00 WIB setiap hari dalam rentang tanggal pilihan. Rentang jam minimal 4 jam; waktu mulai minimal 4 jam dari sekarang.</p>
        </fieldset>

        <fieldset>
            <legend>4. Nomor testing</legend>
            @php
                $testingNumbers = old('control_numbers', $campaign['control_numbers'] ?? [$campaign['control_number'] ?? '']);
                $testingNumbers = is_array($testingNumbers) ? array_values(array_filter($testingNumbers, fn ($number) => is_string($number) && $number !== '')) : [];
            @endphp
            <div class="field-group">
                <label for="voucherTestingNumberInput" class="field-label">Nomor HP penerima testing</label>
                <div class="voucher-phone-input">
                    <span id="testing-prefix" class="voucher-phone-prefix">62</span>
                    <input id="voucherTestingNumberInput" type="tel" inputmode="numeric" class="text-input" placeholder="8xxxxxxxxxx" pattern="8[0-9]{8,11}" maxlength="12" autocomplete="tel-national" aria-describedby="testing-prefix testing-help" @if(!$testingNumbers) required @endif>
                </div>
            </div>
            <button type="button" id="voucherAddTestingNumber" class="campaign-outline-button">Tambah nomor testing</button>
            {{-- <p id="testing-help" class="voucher-help">Awalan 62 tetap dan tidak bisa dihapus. Isi nomor mulai dari 8, lalu tekan Tambah nomor testing. Nomor akan tampil dalam daftar di bawah.</p> --}}
            <ul id="voucherTestingNumbers" class="voucher-testing-numbers" aria-label="Daftar nomor testing">
                @foreach($testingNumbers as $index => $testingNumber)
                    @include('partials.voucher-testing-number', ['numberValue' => $testingNumber])
                @endforeach
            </ul>
            <p id="voucherTestingNumbersEmpty" class="voucher-help" @if($testingNumbers) hidden @endif>Belum ada nomor testing ditambahkan.</p>
            <template id="voucherTestingNumberTemplate">
                @include('partials.voucher-testing-number', ['numberValue' => ''])
            </template>
        </fieldset>

        <p class="voucher-demo-note">Mode dummy: setup disimpan selama sesi ini. Pengiriman SMS dan report belum aktif.</p>
        <button type="submit" class="submit-btn">{{ $campaign ? 'Simpan perubahan campaign' : 'Simpan setup campaign' }}</button>
    </form>

    @if($campaign)
        <section class="voucher-message-preview" aria-label="Preview pesan SMS">
            <span class="voucher-badge">Draft SMS LBA</span>
            <h3>{{ $campaign['sender_name'] }}</h3>
            <p>{{ $campaign['message'] }}</p>
            <small>{{ number_format($campaign['recipient_total'], 0, ',', '.') }} penerima &middot; Report {{ $campaign['report_days'] }} hari</small>
            @if(isset($campaign['delivery_start_date'], $campaign['delivery_end_date'], $campaign['delivery_start_time'], $campaign['delivery_end_time']))
                <p class="voucher-help">{{ $campaign['delivery_start_date'] }} s/d {{ $campaign['delivery_end_date'] }}<br>{{ $campaign['delivery_start_time'] }}-{{ $campaign['delivery_end_time'] }} WIB setiap hari</p>
            @endif
            @if(isset($campaign['control_numbers']))
                <p class="voucher-help">Nomor testing: {{ implode(', ', $campaign['control_numbers']) }}</p>
            @elseif(isset($campaign['control_number']))
                <p class="voucher-help">Nomor testing: {{ $campaign['control_number'] }}</p>
            @endif
        </section>
        <section class="voucher-report-email-summary" aria-label="Email report campaign">
            @if(!empty($campaign['report_email']))
                <p class="voucher-help">Email report: {{ $campaign['report_email'] }}</p>
            @else
                <p class="voucher-help">Email report belum diisi (opsional).</p>
            @endif
            <button type="button" class="voucher-switch-button" data-open-report-email>{{ empty($campaign['report_email']) ? 'Tambahkan email report' : 'Ubah email report' }}</button>
        </section>
    @endif

    <form method="POST" action="{{ route('logout') }}" class="voucher-switch-form">
        @csrf
        <button type="submit" class="voucher-switch-button">Keluar / Ganti kode</button>
    </form>
@endsection

@section('after_shell')
    @if($campaign)
        @include('partials.voucher-report-email-modal')
    @endif
    @include('partials.campaign-location-modal', [
        'locationUseHiddenRadius' => true,
        'locationInitialRadius' => old('location_radius', $campaign['location_radius'] ?? 1000),
    ])
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script src="{{ asset('js/indonesia-map-geocoder.js') }}?v={{ filemtime(public_path('js/indonesia-map-geocoder.js')) }}"></script>
    <script src="{{ asset('js/voucher-location-map.js') }}?v={{ filemtime(public_path('js/voucher-location-map.js')) }}"></script>
    <script src="{{ asset('js/voucher-testing-numbers.js') }}?v={{ filemtime(public_path('js/voucher-testing-numbers.js')) }}"></script>
    <script src="{{ asset('js/voucher-report-email.js') }}?v={{ filemtime(public_path('js/voucher-report-email.js')) }}"></script>
@endpush
