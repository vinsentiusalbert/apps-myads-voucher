@extends('layouts.voucher')

@section('title', 'Masuk Voucher - MyAds')
@section('body-class', 'voucher-mobile-preview')

@section('content')
    <span class="voucher-badge">Bundling MyAds</span>
    <h2>Masukkan kode voucher</h2>
    <p class="voucher-description">Akses paket Anda dengan kode voucher 6-8 karakter.</p>

    <form method="POST" action="{{ route('login.store') }}" class="login-form">
        @csrf
        <div class="field-group">
            <label for="code" class="field-label">Kode voucher</label>
            <input id="code" name="code" type="text" value="{{ old('code') }}"
                class="text-input voucher-code" placeholder="XXXXXXXX" minlength="6" maxlength="8"
                pattern="[A-Za-z0-9]{6,8}" title="Masukkan 6-8 karakter huruf atau angka"
                autocomplete="off" autocapitalize="characters" spellcheck="false" required autofocus
                aria-describedby="code-help{{ $errors->has('code') ? ' code-error' : '' }}"
                @if($errors->has('code')) aria-invalid="true" @endif>
            <p id="code-help" class="voucher-help">Gunakan kombinasi huruf dan angka. Huruf besar atau kecil diterima.</p>
            @error('code')
                <p id="code-error" class="error-text" role="alert">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="submit-btn">Masuk</button>
    </form>
    <p class="voucher-demo-note">Mode demo &middot; Gunakan kode dummy yang telah disediakan.</p>
@endsection
