<div class="campaign-payment-modal" id="<?php echo e($modalId); ?>" hidden aria-hidden="true">
    <div class="campaign-payment-modal__backdrop" data-payment-modal-close></div>
    <div class="campaign-payment-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo e($modalId); ?>Title">
        <div class="campaign-payment-modal__header">
            <h2 id="<?php echo e($modalId); ?>Title">Pembayaran QRIS</h2>
            <button type="button" class="campaign-payment-modal__close" data-payment-modal-close aria-label="Tutup">&times;</button>
        </div>
        <div class="campaign-cost-card__section campaign-payment-modal__body" id="<?php echo e($panelId); ?>" hidden>
            <span class="campaign-cost-card__section-title">Pembayaran QRIS</span>
            <div class="campaign-cost-card__row">
                <span>Status</span>
                <strong id="<?php echo e($statusId); ?>">-</strong>
            </div>
            <div class="campaign-cost-card__row">
                <span>Transaction ID</span>
                <strong id="<?php echo e($transactionId); ?>">-</strong>
            </div>
            <div class="campaign-payment-modal__qr">
                <img id="<?php echo e($qrId); ?>" alt="QRIS" />
                <div class="campaign-payment-modal__actions">
                    <a id="<?php echo e($qrLinkId); ?>" class="campaign-inline-link campaign-inline-link--fit" href="#" target="_blank" rel="noreferrer" hidden>Buka QRIS</a>
                    <a id="<?php echo e($downloadId); ?>" class="campaign-payment-modal__download" href="#" target="_blank" download="qris.png" hidden>Download QRIS</a>
                </div>
            </div>
        </div>

        <!-- Success State -->
        <div class="campaign-payment-modal__state-panel" id="<?php echo e($panelId); ?>Success" style="display: none; padding: 24px; text-align: center;">
            <div class="campaign-payment-modal__success-icon" style="font-size: 56px; color: #10b981; margin-bottom: 16px;">✓</div>
            <h3 style="font-size: 18px; font-weight: bold; color: #071d3a; margin-bottom: 8px;">Pembayaran Berhasil!</h3>
            <p style="font-size: 14px; color: #6b7280; margin-bottom: 24px;">Terima kasih, pembayaran Anda telah berhasil kami terima. Campaign Anda sedang diproses untuk dikirim.</p>
            <button type="button" class="campaign-modal-btn campaign-modal-btn--primary" style="background: #10b981; border: none; color: white; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%;" id="<?php echo e($panelId); ?>SuccessBtn">Lanjutkan</button>
        </div>

        <!-- Failure State -->
        <div class="campaign-payment-modal__state-panel" id="<?php echo e($panelId); ?>Failure" style="display: none; padding: 24px; text-align: center;">
            <div class="campaign-payment-modal__failure-icon" style="font-size: 56px; color: #ef4444; margin-bottom: 16px;">✗</div>
            <h3 style="font-size: 18px; font-weight: bold; color: #071d3a; margin-bottom: 8px;">Pembayaran Gagal</h3>
            <p style="font-size: 14px; color: #6b7280; margin-bottom: 16px;" id="<?php echo e($panelId); ?>FailureMsg">Terjadi kesalahan saat memproses pembayaran Anda. Silakan coba kembali.</p>
            <button type="button" class="campaign-modal-btn" style="background: #ef4444; border: none; color: white; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%;" data-payment-modal-close>Tutup</button>
        </div>

        <p class="campaign-cost-card__footnote campaign-payment-modal__footnote">
            <?php echo e($footnote ?? 'Apabila terdapat pesan yang tidak terkirim, maka biaya akan dikembalikan (refund) sesuai jumlah pesan yang tidak terkirim.'); ?>

        </p>
    </div>
</div>
<?php /**PATH D:\1 Kerja\shop-myads\resources\views/partials/payment-qris-modal.blade.php ENDPATH**/ ?>