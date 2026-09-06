@php
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
@endphp

@extends('layouts.portal')

@section('content')
    {{-- ── Membership Banner ───────────────────────────────────────────── --}}
    @if ($isMember)
        <div class="membership-banner membership-banner--active">
            <div class="membership-banner__left">
                <span class="membership-banner__icon">⭐</span>
                <div>
                    <p class="membership-banner__title">Member Aktif</p>
                    <p class="membership-banner__desc">
                        Sisa kuota: <strong>{{ number_format($membershipRemainingQuota, 0, ',', '.') }} SMS</strong>
                        @if ($membershipExpiredLabel)
                            &nbsp;·&nbsp; Berlaku hingga <strong>{{ $membershipExpiredLabel }}</strong>
                        @endif
                    </p>
                </div>
            </div>
            <span class="membership-banner__badge">✦ Member</span>
        </div>
    @else
        <div class="membership-banner membership-banner--promo">
            <div class="membership-banner__left">
                <span class="membership-banner__icon">✨</span>
                <div>
                    <p class="membership-banner__title">MyAds Membership</p>
                    <p class="membership-banner__desc">Kirim <strong>5.000 SMS gratis/bulan</strong> — hanya Rp {{ number_format($membershipPrice, 0, ',', '.') }}/bln</p>
                </div>
            </div>
            <button
                type="button"
                class="membership-banner__cta"
                id="btn-join-membership"
                data-price="{{ $membershipPrice }}"
            >
                ⭐ Join Sekarang
            </button>
        </div>
    @endif
    {{-- ── End Membership Banner ───────────────────────────────────────── --}}
    @php
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
@endphp

    <section class="portal-hero">
        <div class="portal-welcome">
            <p class="portal-welcome__lead">Selamat Datang,</p>
            <h1 class="portal-welcome__name">{{ session('myads.user.name', 'User') }}</h1>
            @if ($clientLabel)
                <p class="portal-welcome__meta">{{ $clientLabel }}</p>
            @endif
        </div>

        <article class="portal-balance">
            <div class="portal-balance__top">
                <div>
                    <p class="portal-balance__label">Saldo Refund</p>
                    <p class="portal-balance__value">{{ $balanceFormatted ?? '-' }}</p>
                </div>
            </div>

            <div class="portal-balance__cta-wrap">
                <button type="button" class="portal-balance__cta" data-campaign-category-open>
                    Buat Iklan
                </button>
            </div>

            {{-- <div class="portal-balance__bottom">
                <span>{{ $expiryLabel }}</span>
                <span>{{ $expiryLabel }}</span>
                <a href="#">Lihat Riwayat Saldo</a>
            </div> --}}
        </article>
    </section>

    <section class="portal-card portal-card--insight">
        <div class="portal-card__header">
            <h2>Ringkasan Dashboard</h2>
            {{-- <a href="{{ route('dashboard') }}">Refresh</a> --}}
        </div>

        <div class="portal-stats">
            <div class="portal-stats__item">
                <p class="portal-stats__value">{{ $campaignTotalLabel }}</p>
                <p class="portal-stats__label">Total campaign</p>
            </div>
            <div class="portal-stats__item">
                <p class="portal-stats__value">{{ $campaignSmsLabel }}</p>
                <p class="portal-stats__label">Campaign channel SMS</p>
            </div>
            <div class="portal-stats__item">
                <p class="portal-stats__value">{{ $campaignWaLabel }}</p>
                <p class="portal-stats__label">Campaign channel WA Business</p>
            </div>
            {{-- <div class="portal-stats__item">
                <p class="portal-stats__value">{{ $gwStatusLabel }}</p>
                <p class="portal-stats__label">Token gateway di session</p>
            </div> --}}
        </div>
    </section>

    <section class="portal-section">
        <h2 class="portal-section__title">Menu Campaign</h2>

        <div class="portal-service-grid">
            <article class="portal-service-card">
                <div class="portal-service-card__title-row">
                    <h3>SMS</h3>
                    <span>{{ $campaignSmsLabel }} campaign</span>
                </div>
                <div class="portal-service-list">
                    <a href="{{ route('campaign.menu', ['channel' => 'sms', 'menu' => 'location-based-area', 'view' => 'create']) }}" class="portal-service-list__item portal-service-list__item--campaign">
                        <span>Location Based Area</span>
                        <strong>{{ $smsLbaPrice }}</strong>
                    </a>
                    <a href="{{ route('campaign.menu', ['channel' => 'sms', 'menu' => 'targeted', 'view' => 'create']) }}" class="portal-service-list__item portal-service-list__item--campaign">
                        <span>Targeted</span>
                        <strong>{{ $smsTargetedPrice }}</strong>
                    </a>
                </div>
            </article>

            <article class="portal-service-card">
                <div class="portal-service-card__title-row">
                    <h3>WA Business</h3>
                    <span>{{ $campaignWaLabel }} campaign</span>
                </div>
                <div class="portal-service-list">
                    <a href="{{ route('campaign-template.index') }}" class="portal-service-list__item portal-service-list__item--campaign">
                        <span>Campaign Template</span>
                        <strong>Kelola template</strong>
                    </a>
                    <a href="{{ route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'location-based-area', 'view' => 'create']) }}" class="portal-service-list__item portal-service-list__item--campaign">
                        <span>Location Based Area</span>
                        <strong>{{ $waLbaPrice }}</strong>
                    </a>
                    <a href="{{ route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'targeted', 'view' => 'create']) }}" class="portal-service-list__item portal-service-list__item--campaign">
                        <span>Targeted</span>
                        <strong>{{ $waTargetedPrice }}</strong>
                    </a>
                </div>
            </article>
        </div>
    </section>

@endsection

{{-- ============================================================
     MEMBERSHIP MODAL (Join Membership)
     ============================================================ --}}
<div id="membership-modal" class="membership-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="membership-modal-title" style="display:none">
    <div class="membership-modal__backdrop" id="membership-modal-backdrop"></div>
    <div class="membership-modal__box" id="membership-modal-box">
        <button type="button" class="membership-modal__close" id="membership-modal-close" aria-label="Tutup">
            <svg viewBox="0 0 24 24" width="20" height="20"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>

        {{-- Step 1: Form --}}
        <div id="membership-step-form">
            <div class="membership-modal__icon">⭐</div>
            <h2 class="membership-modal__title" id="membership-modal-title">MyAds Membership</h2>
            <p class="membership-modal__subtitle">Dapatkan keuntungan eksklusif untuk bisnis Anda</p>

            <ul class="membership-modal__benefits">
                <li>
                    <span class="membership-modal__benefit-icon">📨</span>
                    <span><strong>5.000 penerima SMS gratis</strong> per bulan tanpa biaya tambahan</span>
                </li>
                <li>
                    <span class="membership-modal__benefit-icon">⚡</span>
                    <span>Campaign SMS langsung diproses <strong>tanpa perlu pembayaran</strong> (kuota berlaku)</span>
                </li>
                <li>
                    <span class="membership-modal__benefit-icon">📅</span>
                    <span>Periode aktif <strong>30 hari</strong> sejak pembayaran berhasil</span>
                </li>
            </ul>

            <div class="membership-modal__price-box">
                <span class="membership-modal__price">Rp {{ number_format($membershipPrice, 0, ',', '.') }}</span>
                <span class="membership-modal__period">/ bulan</span>
            </div>

            <form id="membership-join-form">
                @csrf
                <div class="membership-modal__field">
                    <label for="membership-phone">Nomor Telepon <span style="color:#e74c3c">*</span></label>
                    <input
                        type="tel"
                        id="membership-phone"
                        name="customer_phone"
                        placeholder="08xxxxxxxxxx"
                        required
                        class="membership-modal__input"
                    >
                </div>

                <div id="membership-join-error" class="membership-modal__error" style="display:none"></div>

                <button type="submit" class="membership-modal__cta" id="membership-join-btn">
                    <span id="membership-join-btn-text">💳 Bayar &amp; Aktifkan Membership</span>
                    <span id="membership-join-loading" style="display:none">Memproses...</span>
                </button>
            </form>

            <p class="membership-modal__note">Pembayaran via QRIS. Setelah pembayaran berhasil, membership langsung aktif.</p>
        </div>

        {{-- Step 2: QRIS display (hidden initially) --}}
        <div id="membership-step-qris" style="display:none;text-align:center">
            <div class="membership-modal__icon">📱</div>
            <h2 class="membership-modal__title">Scan QRIS</h2>
            <p class="membership-modal__subtitle">Scan kode di bawah dengan aplikasi dompet digital Anda</p>

            <div style="background:#fff;border-radius:12px;padding:12px;display:inline-block;margin:12px 0;box-shadow:0 2px 12px rgba(0,0,0,.15)">
                <img id="membership-qris-img" src="" alt="QRIS" style="width:220px;height:220px;display:block;border-radius:8px">
            </div>

            <div style="margin:8px 0">
                <p style="font-size:13px;opacity:.7;margin:0">Berlaku hingga</p>
                <p id="membership-qris-expire" style="font-size:13px;font-weight:600;margin:2px 0">-</p>
                <p id="membership-qris-countdown" style="font-size:22px;font-weight:700;color:#f39c12;margin:4px 0">--:--</p>
            </div>

            <div id="membership-qris-status" style="margin:8px 0;font-size:13px;opacity:.7">Menunggu pembayaran...</div>

            <button type="button" id="membership-paid-btn" class="membership-modal__cta" style="margin-top:8px">
                ✅ Saya Sudah Bayar
            </button>
            <button type="button" id="membership-qris-cancel" style="margin-top:8px;background:none;border:none;color:inherit;opacity:.5;font-size:13px;cursor:pointer;display:block;width:100%;text-align:center">
                Batalkan
            </button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (() => {
            const dashboardLink = document.querySelector('[data-dashboard-link]');
            const subnavLinks = document.querySelectorAll('[data-subnav-link]');

            const clearNavSelection = () => {
                document.querySelectorAll('.portal-nav__item').forEach((item) => {
                    item.classList.remove('portal-nav__item--active', 'portal-nav__item--selected');
                });

                subnavLinks.forEach((link) => {
                    link.classList.remove('portal-subnav__item--active');
                });
            };

            dashboardLink?.addEventListener('click', () => {
                clearNavSelection();
                dashboardLink.closest('.portal-nav__item')?.classList.add('portal-nav__item--active');
            });

            const buatIklanBtn = document.querySelector('[data-campaign-category-open]');
            buatIklanBtn?.addEventListener('click', () => {
                fetch("{{ route('campaign.track-event') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ event_type: 'click_buat_iklan' })
                }).catch(err => console.warn('Tracking failed:', err));
            });

            subnavLinks.forEach((link) => {
                link.addEventListener('click', (event) => {
                    const isPlaceholder = link.getAttribute('href') === '#';

                    if (isPlaceholder) {
                        event.preventDefault();
                    }

                    const parentGroup = link.closest('[data-nav-group]');
                    const parentToggle = parentGroup?.querySelector('[data-nav-toggle]');

                    clearNavSelection();
                    link.classList.add('portal-subnav__item--active');
                    parentGroup?.classList.add('portal-nav__item--selected', 'portal-nav__item--open');
                    parentToggle?.setAttribute('aria-expanded', 'true');
                });
            });
        })();
    </script>

    {{-- Membership Modal JS --}}
    <script>
    (() => {
        const modal        = document.getElementById('membership-modal');
        const backdrop     = document.getElementById('membership-modal-backdrop');
        const closeBtn     = document.getElementById('membership-modal-close');
        const joinBtn      = document.getElementById('btn-join-membership');
        const form         = document.getElementById('membership-join-form');
        const errorDiv     = document.getElementById('membership-join-error');
        const submitBtn    = document.getElementById('membership-join-btn');
        const submitText   = document.getElementById('membership-join-btn-text');
        const submitLoader = document.getElementById('membership-join-loading');
        const stepForm     = document.getElementById('membership-step-form');
        const stepQris     = document.getElementById('membership-step-qris');
        const qrisImg      = document.getElementById('membership-qris-img');
        const qrisExpire   = document.getElementById('membership-qris-expire');
        const qrisCountdown = document.getElementById('membership-qris-countdown');
        const qrisStatus   = document.getElementById('membership-qris-status');
        const paidBtn      = document.getElementById('membership-paid-btn');
        const cancelBtn    = document.getElementById('membership-qris-cancel');

        if (!modal) return; // Already a member — modal not rendered

        let countdownTimer  = null;
        let pollingTimer    = null;
        let currentTxnId    = null;

        // ── Countdown timer ─────────────────────────────────────────────────
        const stopCountdown = () => { clearInterval(countdownTimer); countdownTimer = null; };

        const startCountdown = (expireIso) => {
            stopCountdown();
            const expireMs = expireIso ? new Date(expireIso).getTime() : 0;
            const tick = () => {
                const diff = expireMs - Date.now();
                if (diff <= 0) {
                    qrisCountdown.textContent = '00:00';
                    qrisCountdown.style.color = '#e74c3c';
                    stopCountdown();
                    return;
                }
                const m = String(Math.floor(diff / 60000)).padStart(2, '0');
                const s = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
                qrisCountdown.textContent = `${m}:${s}`;
                qrisCountdown.style.color = diff < 60000 ? '#e74c3c' : '#f39c12';
            };
            tick();
            countdownTimer = setInterval(tick, 1000);
        };

        // ── Payment status polling ───────────────────────────────────────────
        const stopPolling = () => { clearInterval(pollingTimer); pollingTimer = null; };

        const checkPaymentStatus = async () => {
            if (!currentTxnId) return;
            try {
                const r = await fetch('{{ route("membership.status") }}', {
                    headers: { 'Accept': 'application/json' }
                });
                const d = await r.json();
                if (d?.data?.is_member) {
                    stopPolling();
                    stopCountdown();
                    qrisStatus.textContent = '✅ Pembayaran berhasil!';
                    qrisStatus.style.color  = '#2ecc71';
                    setTimeout(() => { closeModal(); location.reload(); }, 1500);
                }
            } catch (_) { /* ignore */ }
        };

        const startPolling = () => {
            stopPolling();
            pollingTimer = setInterval(checkPaymentStatus, 5000);
        };

        // ── Open / Close ────────────────────────────────────────────────────
        const showStep = (step) => {
            stepForm.style.display = step === 'form' ? '' : 'none';
            stepQris.style.display = step === 'qris' ? '' : 'none';
        };

        const openModal = () => {
            showStep('form');
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        };

        const closeModal = () => {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            if (errorDiv) { errorDiv.style.display = 'none'; errorDiv.textContent = ''; }
            stopPolling();
            stopCountdown();
            showStep('form');
        };

        joinBtn?.addEventListener('click', openModal);
        closeBtn?.addEventListener('click', closeModal);
        backdrop?.addEventListener('click', closeModal);
        cancelBtn?.addEventListener('click', closeModal);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

        // ── "Sudah bayar" manual check ────────────────────────────────────
        paidBtn?.addEventListener('click', async () => {
            paidBtn.disabled = true;
            paidBtn.textContent = 'Mengecek...';
            await checkPaymentStatus();
            paidBtn.disabled = false;
            paidBtn.textContent = '✅ Saya Sudah Bayar';
        });

        // ── Show QRIS step ───────────────────────────────────────────────────
        const showQris = (payment) => {
            qrisImg.src = payment.qris_url;
            qrisImg.onerror = () => {
                qrisImg.style.display = 'none';
                qrisImg.insertAdjacentHTML('afterend', '<p style="color:#e74c3c;font-size:13px">Gagal memuat QR. Coba refresh.</p>');
            };

            if (payment.transaction_expire) {
                const expireDate = new Date(payment.transaction_expire);
                qrisExpire.textContent = expireDate.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                startCountdown(payment.transaction_expire);
            } else {
                qrisExpire.textContent = '-';
            }

            currentTxnId = payment.transaction_id;
            qrisStatus.textContent = 'Menunggu pembayaran...';
            qrisStatus.style.color  = '';
            showStep('qris');
            startPolling();
        };

        // ── Form submit ──────────────────────────────────────────────────────
        form?.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (errorDiv) { errorDiv.style.display = 'none'; errorDiv.textContent = ''; }

            submitBtn.disabled = true;
            submitText.style.display = 'none';
            submitLoader.style.display = 'inline';

            const phone = form.querySelector('#membership-phone')?.value || '';
            const csrf  = form.querySelector('[name="_token"]')?.value || '';

            try {
                const resp = await fetch('{{ route("membership.join") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({ customer_phone: phone })
                });

                const data = await resp.json();

                if (data.success) {
                    const payment = data.data?.payment;

                    // Langsung SUCCESS (force success / refund)
                    if (!payment || payment.status === 'SUCCESS') {
                        closeModal();
                        alert('🎉 Membership berhasil diaktifkan!');
                        location.reload();
                        return;
                    }

                    // Ada QRIS — tampilkan inline
                    if (payment.qris_url) {
                        showQris(payment);
                        return;
                    }

                    // Ada redirect url (non-QRIS gateway)
                    if (payment.redirect_url) {
                        window.location.href = payment.redirect_url;
                        return;
                    }

                    // Fallback
                    alert('Pembayaran dimulai. Silakan selesaikan dan refresh halaman.');
                    closeModal();
                } else {
                    const msg = data.message || 'Gagal memproses permintaan membership.';
                    errorDiv.textContent = msg;
                    errorDiv.style.display = 'block';
                }
            } catch (err) {
                errorDiv.textContent = 'Terjadi kesalahan jaringan. Coba lagi.';
                errorDiv.style.display = 'block';
            } finally {
                submitBtn.disabled = false;
                submitText.style.display = 'inline';
                submitLoader.style.display = 'none';
            }
        });
    })();
    </script>
@endpush
