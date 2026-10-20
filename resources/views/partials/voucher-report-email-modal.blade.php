@php
    $reportEmailValue = old('report_email', $campaign['report_email'] ?? '');
    $reportEmailValue = is_string($reportEmailValue) ? $reportEmailValue : '';
@endphp
<dialog id="voucherReportEmailModal" class="voucher-email-modal"
    aria-labelledby="voucherReportEmailTitle" aria-describedby="voucherReportEmailDescription"
    data-auto-open="{{ session('prompt_report_email') || $errors->reportEmail->any() ? 'true' : 'false' }}">
    <div class="voucher-email-modal-header">
        <span class="voucher-badge">Campaign tersimpan</span>
        <button type="button" class="voucher-email-modal-close" data-close-report-email aria-label="Tutup popup email">&times;</button>
    </div>
    <h2 id="voucherReportEmailTitle">Kirim report ke email?</h2>
    <p id="voucherReportEmailDescription" class="voucher-description">Tambahkan alamat email untuk tujuan pengiriman report campaign Anda. Opsional, Anda bisa melewati langkah ini.</p>
    <form method="POST" action="{{ route('voucher.campaign.report-email') }}" class="voucher-email-form">
        @csrf
        <div class="field-group">
            <label for="report_email" class="field-label">Alamat email (opsional)</label>
            <input id="report_email" name="report_email" type="email" class="text-input" maxlength="254" autocomplete="email"
                value="{{ $reportEmailValue }}" placeholder="nama@contoh.com"
                aria-describedby="report-email-note{{ $errors->reportEmail->has('report_email') ? ' report-email-error' : '' }}"
                @if($errors->reportEmail->has('report_email')) aria-invalid="true" @endif>
            @error('report_email', 'reportEmail')
                <p id="report-email-error" class="error-text" role="alert">{{ $message }}</p>
            @enderror
        </div>
        <p id="report-email-note" class="voucher-help">Mode dummy: email disimpan bersama campaign. Report belum dikirim ke email.</p>
        <button type="submit" class="submit-btn">Simpan email</button>
        <button type="button" class="voucher-switch-button" data-close-report-email>Lewati</button>
    </form>
</dialog>
