<?php
    $formatPortalCampaignPrice = static fn ($value): string => is_numeric($value)
        ? 'Rp ' . number_format((float) $value, 0, ',', '.') . ' / pesan'
        : 'Harga belum tersedia';

    $portalSmsLbaPrice = $formatPortalCampaignPrice(data_get($campaignPrices ?? [], 'sms_lba'));
    $portalSmsTargetedPrice = $formatPortalCampaignPrice(data_get($campaignPrices ?? [], 'sms_targeted'));
    $portalWaLbaPrice = $formatPortalCampaignPrice(data_get($campaignPrices ?? [], 'wa_lba'));
    $portalWaTargetedPrice = $formatPortalCampaignPrice(data_get($campaignPrices ?? [], 'wa_targeted'));
?>

<div class="portal-category-modal" id="campaignCategoryModal" hidden aria-hidden="true" data-price-url="<?php echo e(route('campaign.price-view')); ?>">
    <div class="portal-category-modal__backdrop" data-campaign-category-close></div>
    <section class="portal-category-modal__card" role="dialog" aria-modal="true" aria-labelledby="campaignCategoryTitle">
        <div class="portal-category-modal__head">
            <div>
                <p class="portal-category-modal__eyebrow">Buat iklan baru</p>
                <h2 id="campaignCategoryTitle">Pilih Kategori Iklan</h2>
            </div>
            <button type="button" class="portal-category-modal__close" data-campaign-category-close aria-label="Tutup kategori iklan">&times;</button>
        </div>

        <div class="portal-category-grid">
            <a href="<?php echo e(route('campaign.menu', ['channel' => 'sms', 'menu' => 'location-based-area', 'view' => 'create'])); ?>" class="portal-category-card">
                <span class="portal-category-card__icon portal-category-card__icon--location">LBA</span>
                <span class="portal-category-card__channel">SMS</span>
                <strong>Location Based Area</strong>
                <small data-campaign-price-key="sms_lba"><?php echo e($portalSmsLbaPrice); ?></small>
            </a>
            <a href="<?php echo e(route('campaign.menu', ['channel' => 'sms', 'menu' => 'targeted', 'view' => 'create'])); ?>" class="portal-category-card">
                <span class="portal-category-card__icon portal-category-card__icon--target">TG</span>
                <span class="portal-category-card__channel">SMS</span>
                <strong>Targeted</strong>
                <small data-campaign-price-key="sms_targeted"><?php echo e($portalSmsTargetedPrice); ?></small>
            </a>
            <a href="<?php echo e(route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'location-based-area', 'view' => 'create'])); ?>" class="portal-category-card">
                <span class="portal-category-card__icon portal-category-card__icon--wa">LBA</span>
                <span class="portal-category-card__channel">WA Business</span>
                <strong>Location Based Area</strong>
                <small data-campaign-price-key="wa_lba"><?php echo e($portalWaLbaPrice); ?></small>
            </a>
            <a href="<?php echo e(route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'targeted', 'view' => 'create'])); ?>" class="portal-category-card">
                <span class="portal-category-card__icon portal-category-card__icon--wa">TG</span>
                <span class="portal-category-card__channel">WA Business</span>
                <strong>Targeted</strong>
                <small data-campaign-price-key="wa_targeted"><?php echo e($portalWaTargetedPrice); ?></small>
            </a>
        </div>
    </section>
</div>

<script>
    (() => {
        if (window.portalCampaignCategoryModalReady) {
            return;
        }

        window.portalCampaignCategoryModalReady = true;

        const categoryModal = document.getElementById('campaignCategoryModal');
        const categoryOpenButtons = document.querySelectorAll('[data-campaign-category-open]');
        const categoryCloseButtons = document.querySelectorAll('[data-campaign-category-close]');
        let pricesLoaded = false;

        const formatPrice = (value) => {
            const amount = Number(value);
            return Number.isFinite(amount)
                ? `Rp ${new Intl.NumberFormat('id-ID').format(amount)} / pesan`
                : 'Harga belum tersedia';
        };

        const loadPrices = async () => {
            if (pricesLoaded || !categoryModal?.dataset.priceUrl) {
                return;
            }

            pricesLoaded = true;

            try {
                const response = await fetch(categoryModal.dataset.priceUrl, {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                const prices = payload?.prices || {};
                document.querySelectorAll('[data-campaign-price-key]').forEach((node) => {
                    const key = node.dataset.campaignPriceKey;
                    if (Object.prototype.hasOwnProperty.call(prices, key)) {
                        node.textContent = formatPrice(prices[key]);
                    }
                });
            } catch (error) {
                console.warn('Harga campaign gagal dimuat', error);
            }
        };

        const openCategoryModal = () => {
            if (!categoryModal) {
                return;
            }

            categoryModal.hidden = false;
            categoryModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('portal-category-open');
            loadPrices();
        };

        const closeCategoryModal = () => {
            if (!categoryModal) {
                return;
            }

            categoryModal.hidden = true;
            categoryModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('portal-category-open');
        };

        categoryOpenButtons.forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                openCategoryModal();
            });
        });

        categoryCloseButtons.forEach((button) => {
            button.addEventListener('click', closeCategoryModal);
        });

        document.querySelectorAll('.portal-category-card').forEach((card) => {
            card.addEventListener('click', () => {
                const href = card.getAttribute('href') || '';
                let specificEvent = 'click_product_inventory';
                if (href.includes('sms') && href.includes('location-based-area')) specificEvent = 'click_product_sms_lba';
                else if (href.includes('sms') && href.includes('targeted')) specificEvent = 'click_product_sms_targeted';
                else if (href.includes('wa-business') && href.includes('location-based-area')) specificEvent = 'click_product_waba_lba';
                else if (href.includes('wa-business') && href.includes('targeted')) specificEvent = 'click_product_waba_targeted';

                fetch("<?php echo e(route('campaign.track-event')); ?>", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': "<?php echo e(csrf_token()); ?>" },
                    body: JSON.stringify({ event_type: specificEvent })
                }).catch(() => {});
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && categoryModal && !categoryModal.hidden) {
                closeCategoryModal();
            }
        });
    })();
</script>
<?php /**PATH D:\1 Kerja\shop-myads\resources\views/partials/portal-campaign-category-modal.blade.php ENDPATH**/ ?>