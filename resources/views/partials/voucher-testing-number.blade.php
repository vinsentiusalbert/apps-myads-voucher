@php
    $numberValue = is_string($numberValue) ? preg_replace('/\A(?:\+?62|0)/', '', $numberValue) : '';
@endphp
<li class="voucher-testing-number" data-testing-number>
    <span data-testing-number-label>62{{ $numberValue }}</span>
    <input name="control_numbers[]" type="hidden" value="{{ $numberValue }}">
    <button type="button" class="voucher-remove-number" data-remove-testing-number aria-label="Hapus nomor testing 62{{ $numberValue }}">Hapus</button>
</li>
