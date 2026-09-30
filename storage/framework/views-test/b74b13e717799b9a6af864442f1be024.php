<?php
    $title = 'Dashboard';
    $navMenus = [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'dashboard',
            'href' => route('dashboard'),
            'dashboard' => true,
            'interactive' => true,
        ],
        [
            'key' => 'sms',
            'label' => 'Daftar SMS',
            'icon' => 'sms',
            'children' => [
                [
                    'key' => 'sms-location-based-area',
                    'label' => 'Location Based Area',
                    'icon' => 'location',
                    'href' => route('campaign.menu', ['channel' => 'sms', 'menu' => 'location-based-area']),
                    'interactive' => true,
                ],
                [
                    'key' => 'sms-targeted',
                    'label' => 'Targeted',
                    'icon' => 'targeted',
                    'href' => route('campaign.menu', ['channel' => 'sms', 'menu' => 'targeted']),
                    'interactive' => true,
                ],
            ],
        ],
        [
            'key' => 'wa-business',
            'label' => 'Daftar WA Business',
            'icon' => 'wa',
            'children' => [
                [
                    'key' => 'wa-campaign-template',
                    'label' => 'Campaign Template',
                    'icon' => 'template',
                    'href' => route('campaign-template.index'),
                    'interactive' => true,
                ],
                [
                    'key' => 'wa-location-based-area',
                    'label' => 'Location Based Area',
                    'icon' => 'location',
                    'href' => route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'location-based-area']),
                    'interactive' => true,
                ],
                [
                    'key' => 'wa-targeted',
                    'label' => 'Targeted',
                    'icon' => 'targeted',
                    'href' => route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'targeted']),
                    'interactive' => true,
                ],
            ],
        ],
    ];
    $activeNav = 'dashboard';

    // Membership data
    $isMember = (bool) data_get($membership ?? [], 'is_member', false);
    $membershipData = data_get($membership ?? [], 'membership', null);
    $membershipPrice = (int) data_get($membership ?? [], 'price', 25000);
    $membershipTaxPercentage = (float) data_get($membership ?? [], 'tax_percentage', 11);
    $membershipTaxRate = $membershipTaxPercentage / 100;
    $membershipTaxAmount = (int) data_get($membership ?? [], 'tax_amount', round($membershipPrice * $membershipTaxRate));
    $membershipTotalPrice = (int) data_get($membership ?? [], 'total_price', $membershipPrice + $membershipTaxAmount);
    $membershipQuota = (int) data_get($membership ?? [], 'quota', 5000);
    $membershipRemainingQuota = $isMember ? (int) data_get($membershipData ?? [], 'remaining_quota', 0) : 0;
    $membershipExpiredAt = $isMember && $membershipData ? data_get($membershipData, 'expired_at') : null;
    $membershipExpiredLabel = null;
    if ($membershipExpiredAt) {
        try {
            $membershipExpiredLabel = \Carbon\Carbon::parse($membershipExpiredAt)->translatedFormat('d M Y');
        } catch (Throwable) {
            $membershipExpiredLabel = $membershipExpiredAt;
        }
    }
?>



<?php $__env->startSection('content'); ?>
    
    <?php if($isMember): ?>
        <div class="membership-banner membership-banner--active">
            <div class="membership-banner__left">
                <span class="membership-banner__icon">⭐</span>
                <div>
                    <p class="membership-banner__title">Member Aktif</p>
                    <p class="membership-banner__desc">
                        Benefit: <strong>Unlimited SMS (Maks. 5.000 penerima/campaign)</strong>
                        <?php if($membershipExpiredLabel): ?>
                            &nbsp;·&nbsp; Berlaku hingga <strong><?php echo e($membershipExpiredLabel); ?></strong>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <span class="membership-banner__badge">✦ Member</span>
        </div>
    <?php else: ?>
        <div class="membership-banner membership-banner--promo">
            <div class="membership-banner__left">
                <span class="membership-banner__icon">✨</span>
                <div>
                    <p class="membership-banner__title">ShopAds Membership</p>
                    <p class="membership-banner__desc">Kirim <strong>Unlimited SMS (Maks. 5.000 penerima/iklan)</strong> — hanya Rp <?php echo e(number_format($membershipPrice, 0, ',', '.')); ?>/bln (belum termasuk pajak)</p>
                </div>
            </div>
            <button
                type="button"
                class="membership-banner__cta"
                id="btn-join-membership"
                data-price="<?php echo e($membershipTotalPrice); ?>"
            >
                ⭐ Join Sekarang
            </button>
        </div>
    <?php endif; ?>
    
    <?php
    $expiryLabel = '-';

    if (!empty($balanceExpiry) && is_string($balanceExpiry)) {
        try {
            $expiryLabel = 'Exp. ' . \Carbon\Carbon::parse($balanceExpiry)->translatedFormat('d M Y');
        } catch (Throwable) {
            $expiryLabel = 'Exp. ' . $balanceExpiry;
        }
    }

    $campaignTotalLabel = $campaignTotal === null ? '-' : (string) $campaignTotal;
    $campaignSmsLabel = $campaignSmsTotal === null ? '-' : (string) $campaignSmsTotal;
    $campaignWaLabel = $campaignWaTotal === null ? '-' : (string) $campaignWaTotal;
    $gwStatusLabel = session()->has('myads.gw_token') ? 'OK' : '-';
    $clientId = (string) session()->get('myads.merchant_id', '');
    $clientLabel = $clientId !== '' ? 'Client ID: ' . $clientId : null;
    $formatPrice = static fn ($value): string => is_numeric($value)
        ? 'Rp ' . number_format((float) $value, 0, ',', '.') . ' / pesan'
        : 'Harga belum tersedia';
    $smsLbaPrice = $formatPrice(data_get($campaignPrices ?? [], 'sms_lba'));
    $smsTargetedPrice = $formatPrice(data_get($campaignPrices ?? [], 'sms_targeted'));
    $waLbaPrice = $formatPrice(data_get($campaignPrices ?? [], 'wa_lba'));
    $waTargetedPrice = $formatPrice(data_get($campaignPrices ?? [], 'wa_targeted'));
?>

    <section class="portal-hero">
        <div class="portal-welcome">
            <p class="portal-welcome__lead">Selamat Datang,</p>
            <h1 class="portal-welcome__name"><?php echo e(session('myads.user.name', 'User')); ?></h1>
            <?php if($clientLabel): ?>
                <p class="portal-welcome__meta"><?php echo e($clientLabel); ?></p>
            <?php endif; ?>
        </div>

        <article class="portal-balance">
            <div class="portal-balance__top">
                <div>
                    <p class="portal-balance__label">Saldo Refund</p>
                    <p class="portal-balance__value"><?php echo e($balanceFormatted ?? '-'); ?></p>
                </div>
            </div>

            <div class="portal-balance__cta-wrap">
                <button type="button" class="portal-balance__cta" data-campaign-category-open>
                    Buat Iklan
                </button>
            </div>

            
        </article>
    </section>

    <section class="portal-card portal-card--insight">
        <div class="portal-card__header">
            <h2>Ringkasan Dashboard</h2>
            
        </div>

        <div class="portal-stats">
            <div class="portal-stats__item">
                <p class="portal-stats__value"><?php echo e($campaignTotalLabel); ?></p>
                <p class="portal-stats__label">Total campaign</p>
            </div>
            <div class="portal-stats__item">
                <p class="portal-stats__value"><?php echo e($campaignSmsLabel); ?></p>
                <p class="portal-stats__label">Campaign channel SMS</p>
            </div>
            <div class="portal-stats__item">
                <p class="portal-stats__value"><?php echo e($campaignWaLabel); ?></p>
                <p class="portal-stats__label">Campaign channel WA Business</p>
            </div>
            
        </div>
    </section>

    <section class="portal-section">
        <h2 class="portal-section__title">Menu Campaign</h2>

        <div class="portal-service-grid">
            <article class="portal-service-card">
                <div class="portal-service-card__title-row">
                    <h3>SMS</h3>
                    <span><?php echo e($campaignSmsLabel); ?> campaign</span>
                </div>
                <div class="portal-service-list">
                    <a href="<?php echo e(route('campaign.menu', ['channel' => 'sms', 'menu' => 'location-based-area', 'view' => 'create'])); ?>" class="portal-service-list__item portal-service-list__item--campaign">
                        <span>Location Based Area</span>
                        <strong><?php echo e($smsLbaPrice); ?></strong>
                    </a>
                    <a href="<?php echo e(route('campaign.menu', ['channel' => 'sms', 'menu' => 'targeted', 'view' => 'create'])); ?>" class="portal-service-list__item portal-service-list__item--campaign">
                        <span>Targeted</span>
                        <strong><?php echo e($smsTargetedPrice); ?></strong>
                    </a>
                </div>
            </article>

            <article class="portal-service-card">
                <div class="portal-service-card__title-row">
                    <h3>WA Business</h3>
                    <span><?php echo e($campaignWaLabel); ?> campaign</span>
                </div>
                <div class="portal-service-list">
                    <a href="<?php echo e(route('campaign-template.index')); ?>" class="portal-service-list__item portal-service-list__item--campaign">
                        <span>Campaign Template</span>
                        <strong>Kelola template</strong>
                    </a>
                    <a href="<?php echo e(route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'location-based-area', 'view' => 'create'])); ?>" class="portal-service-list__item portal-service-list__item--campaign">
                        <span>Location Based Area</span>
                        <strong><?php echo e($waLbaPrice); ?></strong>
                    </a>
                    <a href="<?php echo e(route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'targeted', 'view' => 'create'])); ?>" class="portal-service-list__item portal-service-list__item--campaign">
                        <span>Targeted</span>
                        <strong><?php echo e($waTargetedPrice); ?></strong>
                    </a>
                </div>
            </article>
        </div>
    </section>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('after_shell'); ?>
<div id="membership-modal" class="membership-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="membership-modal-title" style="display:none"
    data-join-url="<?php echo e(route('membership.join')); ?>"
    data-membership-url="<?php echo e(route('membership.status')); ?>"
    data-status-url="<?php echo e(route('campaign.payment.status', ['transactionId' => '__TRANSACTION_ID__'])); ?>"
    data-qris-url="<?php echo e(route('campaign.payment.qris-download')); ?>"
    data-track-url="<?php echo e(route('campaign.track-event')); ?>">
    <div class="membership-modal__backdrop" id="membership-modal-backdrop"></div>
    <div class="membership-modal__box" id="membership-modal-box">
        <button type="button" class="membership-modal__close" id="membership-modal-close" aria-label="Tutup">
            <svg viewBox="0 0 24 24" width="20" height="20"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>

        
        <div id="membership-step-form">
            <div class="membership-modal__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3l-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></div>
            <h2 class="membership-modal__title" id="membership-modal-title" tabindex="-1">ShopAds Membership</h2>
            <p class="membership-modal__subtitle">Dapatkan keuntungan eksklusif untuk bisnis Anda</p>

            <ul class="membership-modal__benefits">
                <li>
                    <span class="membership-modal__benefit-icon">📨</span>
                    <span><strong>Unlimited SMS gratis</strong> per bulan (maks. 5.000 nomor penerima per 1 campaign)</span>
                </li>
                <li>
                    <span class="membership-modal__benefit-icon">🚀</span>
                    <span>Bebas buat campaign <strong>sebanyak mungkin</strong> tanpa batasan jumlah iklan (s.d 5.000 penerima/iklan)</span>
                </li>
                <li>
                    <span class="membership-modal__benefit-icon">⚡</span>
                    <span>Campaign SMS langsung diproses <strong>tanpa perlu pembayaran tambahan</strong></span>
                </li>
                <li>
                    <span class="membership-modal__benefit-icon">📅</span>
                    <span>Periode aktif <strong>30 hari</strong> sejak pembayaran berhasil</span>
                </li>
                <li>
                    <span class="membership-modal__benefit-icon">🏷️</span>
                    <span>Harga <strong>Rp <?php echo e(number_format($membershipPrice, 0, ',', '.')); ?></strong>/bulan (belum termasuk pajak / PPN <?php echo e(number_format($membershipTaxPercentage, $membershipTaxPercentage == (int) $membershipTaxPercentage ? 0 : 2, ',', '.')); ?>%)</span>
                </li>
            </ul>

            <div class="membership-modal__price-box">
                <span class="membership-modal__price">Rp <?php echo e(number_format($membershipPrice, 0, ',', '.')); ?></span>
                <span class="membership-modal__period">/ bulan (belum termasuk pajak)</span>
            </div>

            <form id="membership-join-form">
                <?php echo csrf_field(); ?>
                <div class="membership-modal__field">
                    <label for="membership-phone">Nomor Telepon <span style="color:#e74c3c">*</span></label>
                    <input
                        type="tel"
                        required inputmode="tel" autocomplete="tel" maxlength="32"
                        aria-describedby="membership-join-error"
                        id="membership-phone"
                        name="customer_phone"
                        value="<?php echo e(session('myads.user.msisdn') ?? session('myads.user.phone') ?? ''); ?>"
                        placeholder="08xxxxxxxxxx"
                        class="membership-modal__input"
                    >
                </div>

                <div id="membership-join-error" class="membership-modal__error" role="alert" hidden></div>

                <button type="submit" class="membership-modal__cta" id="membership-join-btn">
                    <span id="membership-join-btn-text">💳 Bayar &amp; Aktifkan Membership (Rp <?php echo e(number_format($membershipTotalPrice, 0, ',', '.')); ?>)</span>
                    <span id="membership-join-loading" hidden>⏳ Menyiapkan QRIS...</span>
                </button>
            </form>

            <p class="membership-modal__note">Total pembayaran Rp <?php echo e(number_format($membershipTotalPrice, 0, ',', '.')); ?> (termasuk PPN <?php echo e(number_format($membershipTaxPercentage, $membershipTaxPercentage == (int) $membershipTaxPercentage ? 0 : 2, ',', '.')); ?>%) via QRIS. Setelah pembayaran berhasil, membership langsung aktif.</p>
        </div>

        
        <div id="membership-step-qris" style="text-align:center" hidden>
            <div class="membership-modal__icon">📱</div>
            <h2 class="membership-modal__title" id="membership-qris-title" tabindex="-1">Scan QRIS</h2>
            <p class="membership-modal__subtitle">Scan kode di bawah dengan nominal <strong id="membership-qris-amount">Rp <?php echo e(number_format($membershipTotalPrice, 0, ',', '.')); ?></strong> menggunakan dompet digital / m-banking Anda</p>

            <div style="background:#fff;border-radius:12px;padding:12px;display:inline-block;margin:12px 0;box-shadow:0 2px 12px rgba(0,0,0,.15)">
                <img id="membership-qris-img" src="" alt="QRIS" style="width:220px;height:220px;display:block;border-radius:8px;object-fit:contain;margin:0 auto">
                <div style="margin-top:10px;display:flex;justify-content:center;gap:12px;font-size:12px">
                    <a id="membership-qris-link" href="#" target="_blank" rel="noreferrer" style="color:#d8232a;text-decoration:none;font-weight:600">Buka QRIS</a>
                    <span style="color:#ddd">|</span>
                    <a id="membership-qris-download" href="#" target="_blank" download="qris-membership.png" style="color:#071d3a;text-decoration:none;font-weight:600">Download QRIS</a>
                </div>
            </div>

            <div style="margin:8px 0">
                <p style="font-size:13px;opacity:.7;margin:0">Berlaku hingga</p>
                <p id="membership-qris-expire" style="font-size:13px;font-weight:600;margin:2px 0">-</p>
                <p id="membership-qris-countdown" style="font-size:20px;font-weight:700;color:var(--brand-dark);margin:4px 0">--:--</p>
            </div>

            <div id="membership-qris-status" class="membership-modal__qris-status" role="status" aria-live="polite">Menunggu pembayaran...</div>

            <button type="button" id="membership-paid-btn" class="membership-modal__cta" style="margin-top:8px">
                ✅ Saya Sudah Bayar
            </button>
            <button type="button" id="membership-qris-cancel" class="membership-modal__secondary">
                Kembali
            </button>
        </div>

        
        <div id="membership-step-success" style="padding:24px 12px;text-align:center" hidden>
            <div style="font-size:56px;color:#10b981;margin-bottom:12px;line-height:1">✓</div>
            <h2 class="membership-modal__title" id="membership-success-title" tabindex="-1">Membership Aktif</h2>
            <p class="membership-modal__subtitle" style="margin-bottom:20px;font-size:14px">Terima kasih! ShopAds Membership Anda telah aktif. Unlimited SMS gratis (maks. 5.000 penerima per campaign) siap digunakan.</p>
            <button type="button" id="membership-success-btn" class="membership-modal__cta" style="background:#10b981;border:none;color:#fff;cursor:pointer">
                Lanjutkan ke Dashboard
            </button>
        </div>

        
        <div id="membership-step-failure" style="padding:24px 12px;text-align:center" hidden>
            <div style="font-size:56px;color:#ef4444;margin-bottom:12px;line-height:1">✗</div>
            <h2 class="membership-modal__title" id="membership-failure-title" tabindex="-1">Pembayaran Belum Berhasil</h2>
            <p id="membership-failure-msg" class="membership-modal__subtitle" style="margin-bottom:20px;font-size:14px">Terjadi kesalahan atau waktu pembayaran telah habis. Silakan coba kembali.</p>
            <button type="button" id="membership-retry-btn" class="membership-modal__cta" style="background:#ef4444;border:none;color:#fff;cursor:pointer">
                Coba Lagi
            </button>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        (() => {
            const buatIklanBtn = document.querySelector('[data-campaign-category-open]');
            buatIklanBtn?.addEventListener('click', () => {
                fetch("<?php echo e(route('campaign.track-event')); ?>", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': "<?php echo e(csrf_token()); ?>"
                    },
                    body: JSON.stringify({ event_type: 'click_buat_iklan' })
                }).catch(err => console.warn('Tracking failed:', err));
            });

        })();
    </script>

    <script src="<?php echo e(asset('js/membership.js')); ?>?v=<?php echo e(filemtime(public_path('js/membership.js'))); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.portal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\1 Kerja\shop-myads\resources\views/dashboard.blade.php ENDPATH**/ ?>