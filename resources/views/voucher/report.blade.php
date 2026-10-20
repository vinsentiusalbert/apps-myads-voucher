@extends('layouts.voucher')

@section('title', 'Report Campaign SMS LBA - MyAds')
@section('body-class', 'voucher-mobile-preview voucher-campaign-page')

@section('content')
    <span class="voucher-badge">{{ $voucher['code'] }} &middot; Data dummy</span>
    <h2>Report SMS LBA</h2>
    <p class="voucher-description">{{ $campaign['campaign_name'] }}</p>
    <div class="voucher-report-status"><span>Campaign selesai</span><span>Report {{ $voucher['report_days'] }} hari</span></div>

    <dl class="voucher-report-metrics">
        <div><dt>Total dikirim</dt><dd>{{ $report['sent'] }}</dd><small>pesan</small></div>
        <div><dt>Berhasil diterima</dt><dd>{{ $report['delivered'] }}</dd><small>pesan</small></div>
        <div><dt>Gagal diterima</dt><dd>{{ $report['failed'] }}</dd><small>pesan</small></div>
        <div><dt>Tingkat keberhasilan</dt><dd>{{ $deliveryRate }}%</dd><small>dari total dikirim</small></div>
    </dl>

    <section class="voucher-report-section" aria-label="Hasil pengiriman">
        <h3>Hasil pengiriman</h3>
        <div class="voucher-report-bar" role="img" aria-label="{{ $report['delivered'] }} pesan berhasil, {{ $report['failed'] }} pesan gagal">
            <span class="voucher-report-bar-success" style="width: {{ $deliveryRate }}%"></span>
            <span class="voucher-report-bar-failed" style="width: {{ 100 - $deliveryRate }}%"></span>
        </div>
        <p class="voucher-help">Berhasil {{ $report['delivered'] }} &middot; Gagal {{ $report['failed'] }}</p>
    </section>

    <section class="voucher-report-section">
        <h3>Detail campaign</h3>
        <dl class="voucher-details voucher-report-details">
            <div><dt>Jenis campaign</dt><dd>SMS LBA</dd></div>
            <div><dt>Paket voucher</dt><dd>Rp {{ number_format($voucher['price'], 0, ',', '.') }}</dd></div>
            <div><dt>Lokasi target</dt><dd>{{ $campaign['location_name'] }}</dd></div>
            <div><dt>Radius</dt><dd>{{ number_format($campaign['location_radius'], 0, ',', '.') }} meter</dd></div>
            <div><dt>Periode pengiriman</dt><dd>{{ $campaign['delivery_start_date'] }} s/d {{ $campaign['delivery_end_date'] }}</dd></div>
            <div><dt>Jam pengiriman</dt><dd>{{ $campaign['delivery_start_time'] }}-{{ $campaign['delivery_end_time'] }} WIB</dd></div>
        </dl>
    </section>

    <section class="voucher-message-preview" aria-label="Pesan campaign">
        <span class="voucher-badge">Pesan SMS</span>
        <h3>{{ $campaign['sender_name'] }}</h3>
        <p>{{ $campaign['message'] }}</p>
    </section>

    <p class="voucher-demo-note">Diperbarui {{ $report['updated_at'] }} WIB.<br>Report tersedia hingga {{ $report['available_until'] }} ({{ $voucher['report_days'] }} hari sejak campaign dimulai). Semua angka adalah data simulasi; tidak ada SMS yang dikirim.</p>
    <form method="POST" action="{{ route('logout') }}" class="voucher-switch-form">
        @csrf
        <button type="submit" class="voucher-switch-button">Keluar / Ganti kode</button>
    </form>
@endsection
