@php
    $title = 'Invoice - Telkomsel MyAds';
    
    $formatIDR = function ($val) {
        return 'Rp ' . number_format((float) $val, 0, ',', '.');
    };
@endphp

@extends('layouts.portal')

@push('styles')
<style>
    .invoice-view {
        max-width: 800px;
        margin: 24px auto;
        padding: 0 16px;
    }

    .invoice-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(225, 0, 12, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .invoice-header {
        background: linear-gradient(135deg, #E1000C 0%, #B0000A 100%);
        padding: 32px;
        color: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .invoice-header__brand h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .invoice-header__brand p {
        margin: 4px 0 0;
        font-size: 14px;
        opacity: 0.9;
    }

    .invoice-header__meta {
        text-align: right;
    }

    @media (max-width: 640px) {
        .invoice-header__meta {
            text-align: left;
        }
    }

    .invoice-header__meta h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        opacity: 0.95;
    }

    .invoice-header__meta p {
        margin: 4px 0 0;
        font-size: 12px;
        opacity: 0.8;
    }

    .invoice-body {
        padding: 32px;
    }

    .invoice-section-title {
        font-size: 14px;
        font-weight: 800;
        color: #E1000C;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 16px;
        display: block;
        border-bottom: 2px solid rgba(225, 0, 12, 0.1);
        padding-bottom: 6px;
    }

    .invoice-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 32px;
    }

    @media (max-width: 640px) {
        .invoice-grid {
            grid-template-columns: 1fr;
        }
    }

    .invoice-info-block p {
        margin: 0 0 6px;
        color: #666;
        font-size: 13px;
    }

    .invoice-info-block strong {
        color: #1a1a1a;
        font-size: 15px;
        word-break: break-all;
    }

    .invoice-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 32px;
    }

    .invoice-table th {
        background: #f8f9fa;
        color: #333;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        padding: 12px 16px;
        text-align: left;
        border-bottom: 2px solid #eaeaea;
    }

    .invoice-table td {
        padding: 16px;
        border-bottom: 1px solid #eaeaea;
        font-size: 14px;
        color: #333;
    }

    .invoice-summary {
        margin-left: auto;
        width: 320px;
        max-width: 100%;
    }

    .invoice-summary__row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 14px;
        color: #555;
    }

    .invoice-summary__row--bold {
        font-weight: 800;
        color: #1a1a1a;
        font-size: 16px;
        border-top: 2px dashed rgba(0,0,0,0.1);
        margin-top: 8px;
        padding-top: 12px;
    }

    .invoice-actions {
        display: flex;
        justify-content: flex-end;
        gap: 16px;
        margin-top: 32px;
    }

    .btn-download {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #E1000C;
        color: #ffffff;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        text-decoration: none;
        transition: background-color 0.2s ease, transform 0.2s ease;
    }

    .btn-download:hover {
        background: #B0000A;
        transform: translateY(-1px);
    }

    .btn-download:active {
        transform: translateY(0);
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        background: #ffffff;
        color: #333;
        border: 1px solid #ccc;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        text-decoration: none;
        transition: background-color 0.2s ease;
    }

    .btn-back:hover {
        background: #f8f9fa;
    }
</style>
@endpush

@section('content')
<div class="invoice-view">
    <div class="invoice-card">
        <!-- Header -->
        <div class="invoice-header">
            <div class="invoice-header__brand">
                <h1>Telkomsel MyAds</h1>
                <p>Official Invoice Notification</p>
            </div>
            <div class="invoice-header__meta">
                <h2>INVOICE</h2>
                <p>No: {{ $invoice['invoice_id'] }}</p>
                <p>Tanggal: {{ \Illuminate\Support\Carbon::parse($invoice['created_at'])->translatedFormat('d F Y') }}</p>
            </div>
        </div>

        <!-- Body -->
        <div class="invoice-body">
            <!-- Grid info -->
            <div class="invoice-grid">
                <div>
                    <span class="invoice-section-title">Ditagihkan Kepada</span>
                    <div class="invoice-info-block" style="margin-bottom: 12px;">
                        <p>Nama Pelanggan</p>
                        <strong>{{ $transaction['customer_name'] ?? 'N/A' }}</strong>
                    </div>
                    <div class="invoice-info-block" style="margin-bottom: 12px;">
                        <p>Email / No. Handphone</p>
                        <strong>{{ $transaction['customer_email'] ?? 'N/A' }} / {{ $transaction['customer_phone'] ?? 'N/A' }}</strong>
                    </div>
                    @if(!empty($invoice['address']))
                    <div class="invoice-info-block">
                        <p>Alamat</p>
                        <strong>{{ $invoice['address'] }}</strong>
                    </div>
                    @endif
                </div>

                <div>
                    <span class="invoice-section-title">Info Pembayaran</span>
                    <div class="invoice-info-block" style="margin-bottom: 12px;">
                        <p>Merchant ID</p>
                        <strong>{{ $invoice['merchant_id'] }}</strong>
                    </div>
                    <div class="invoice-info-block" style="margin-bottom: 12px;">
                        <p>ID Transaksi</p>
                        <strong>{{ $invoice['transaction_id'] }}</strong>
                    </div>
                    <div class="invoice-info-block">
                        <p>Status</p>
                        <strong style="color: #28a745; text-transform: uppercase;">{{ $transaction['status'] ?? 'SUCCESS' }}</strong>
                    </div>
                </div>
            </div>

            <!-- Table items -->
            <span class="invoice-section-title">Rincian Layanan</span>
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Item Deskripsi</th>
                        <th>Tipe / Kanal</th>
                        <th style="text-align: right;">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            @if($campaign)
                                {{ $campaign['campaign_name'] ?? $campaign['campaignName'] ?? 'Kampanye Iklan ShopAds' }}
                            @else
                                {{ $transaction['product_detail'] ?? 'Kampanye Iklan ShopAds' }}
                            @endif
                        </td>
                        <td>
                            {{ $transaction['product_type'] ?? 'ADVERTISEMENT' }} / {{ $campaign['channel'] ?? 'SMS' }}
                        </td>
                        <td style="text-align: right; font-weight: 700;">
                            {{ $formatIDR($invoice['payment_amount']) }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Summary -->
            <div class="invoice-summary">
                <div class="invoice-summary__row">
                    <span>Subtotal</span>
                    <span>{{ $formatIDR($invoice['payment_amount']) }}</span>
                </div>
                <div class="invoice-summary__row">
                    <span>Pajak ({{ number_format($invoice['tax_percentage'], 2) }}%)</span>
                    <span>{{ $formatIDR($invoice['tax_amount']) }}</span>
                </div>
                <div class="invoice-summary__row invoice-summary__row--bold">
                    <span>Total Akhir</span>
                    <span style="color: #E1000C;">{{ $formatIDR($invoice['total_amount']) }}</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="invoice-actions">
                <a href="{{ route('dashboard') }}" class="btn-back">Kembali</a>
                <a href="{{ route('invoice.pdf', ['invoiceId' => $invoice['invoice_id']]) }}" class="btn-download" target="_blank">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Unduh PDF
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
