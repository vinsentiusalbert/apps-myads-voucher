<div class="campaign-payment-modal" id="{{ $modalId }}" hidden aria-hidden="true">
    <div class="campaign-payment-modal__backdrop" data-payment-modal-close></div>
    <div class="campaign-payment-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}Title">
        <div class="campaign-payment-modal__header">
            <h2 id="{{ $modalId }}Title">Pembayaran QRIS</h2>
            <button type="button" class="campaign-payment-modal__close" data-payment-modal-close aria-label="Tutup">&times;</button>
        </div>
        <div class="campaign-cost-card__section campaign-payment-modal__body" id="{{ $panelId }}" hidden>
            <span class="campaign-cost-card__section-title">Pembayaran QRIS</span>
            <div class="campaign-cost-card__row">
                <span>Status</span>
                <strong id="{{ $statusId }}">-</strong>
            </div>
            <div class="campaign-cost-card__row">
                <span>Transaction ID</span>
                <strong id="{{ $transactionId }}">-</strong>
            </div>
            <div class="campaign-payment-modal__qr">
                <img id="{{ $qrId }}" alt="QRIS" />
                <div class="campaign-payment-modal__actions">
                    <a id="{{ $qrLinkId }}" class="campaign-inline-link campaign-inline-link--fit" href="#" target="_blank" rel="noreferrer" hidden>Buka QRIS</a>
                    <a id="{{ $downloadId }}" class="campaign-payment-modal__download" href="#" download="qris.png" hidden>Download QRIS</a>
                </div>
            </div>
        </div>
        <p class="campaign-cost-card__footnote campaign-payment-modal__footnote">
            {{ $footnote ?? 'Apabila terdapat pesan yang tidak terkirim, maka biaya akan dikembalikan (refund) sesuai jumlah pesan yang tidak terkirim.' }}
        </p>
    </div>
</div>
