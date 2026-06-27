<div class="campaign-cost-card__section">
    <label class="field-label" for="{{ $inputId }}">Email Invoice</label>
    <input
        id="{{ $inputId }}"
        type="email"
        class="text-input"
        value=""
        placeholder="Email invoice"
    >
    <label class="checkbox-label" for="{{ $checkboxId }}">
        <input id="{{ $checkboxId }}" type="checkbox">
        <span>Centang untuk mengirim invoice ke email ini.</span>
    </label>
</div>
