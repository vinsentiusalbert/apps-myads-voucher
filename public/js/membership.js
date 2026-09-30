(() => {
    const modal = document.getElementById('membership-modal');
    if (!modal) return;
    const find = id => document.getElementById(`membership-${id}`);
    const form = find('join-form'), phone = find('phone'), error = find('join-error');
    const submit = find('join-btn'), paid = find('paid-btn');
    let step = 'form', payment = null, busy = false, checking = false, paidConfirmed = false;
    let countdown = null, poll = null, generation = 0, opener = null, previousOverflow = '';
    const active = value => value === true || value === 1 || value === '1' || value === 'true';
    const isOpen = () => modal.getAttribute('aria-hidden') === 'false';
    const safeUrl = value => {
        try {
            const url = new URL(String(value || '').replace(/\\/g, ''), location.origin);
            return value && ['http:', 'https:'].includes(url.protocol) ? url.href : null;
        } catch (_) { return null; }
    };
    const track = event_type => fetch(modal.dataset.trackUrl, {
        method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': form.elements._token.value},
        body: JSON.stringify({event_type}),
    }).catch(() => {});
    const stopTimers = () => { clearTimeout(poll); clearInterval(countdown); poll = countdown = null; };
    const showStep = value => {
        step = value;
        ['form', 'qris', 'success', 'failure'].forEach(name => { find(`step-${name}`).hidden = name !== value; });
        const title = find(`step-${value}`).querySelector('h2');
        modal.setAttribute('aria-labelledby', title.id);
        if (isOpen()) { find('modal-box').scrollTop = 0; title.focus({preventScroll: true}); }
    };
    const showError = message => { error.textContent = message; error.hidden = false; };
    const fail = message => {
        stopTimers(); generation++;
        find('failure-msg').textContent = message;
        showStep('failure');
    };
    const success = () => { stopTimers(); generation++; showStep('success'); };
    const read = async response => {
        const data = await response.json().catch(() => null);
        if (!response.ok || !data || data.success === false) {
            const validation = data?.errors && Object.values(data.errors).flat()[0];
            throw new Error(response.status === 401 || response.status === 419
                ? 'Sesi berakhir. Silakan masuk kembali sebelum melanjutkan.'
                : validation || data?.message || 'Permintaan belum berhasil. Silakan coba lagi.');
        }
        return data;
    };
    const refreshStatus = async () => {
        if (!payment?.transaction_id || checking || !isOpen() || step !== 'qris') return;
        checking = true;
        const version = generation;
        paid.disabled = true;
        paid.textContent = 'Mengecek pembayaran...';
        try {
            const response = await fetch(modal.dataset.statusUrl.replace('__TRANSACTION_ID__', encodeURIComponent(payment.transaction_id)), {headers: {Accept: 'application/json'}});
            const data = await read(response);
            if (version !== generation || !isOpen()) return;
            const status = String(data.data?.status || '').toUpperCase();
            if (['FAILED', 'EXPIRED', 'CANCELLED'].includes(status)) {
                fail('Pembayaran gagal atau sudah kedaluwarsa. Silakan buat pembayaran baru.');
                return;
            }
            paidConfirmed = status === 'SUCCESS' || paidConfirmed;
            const membership = await read(await fetch(modal.dataset.membershipUrl, {headers: {Accept: 'application/json'}}));
            if (version !== generation || !isOpen()) return;
            if (active(membership.data?.is_member)) { success(); return; }
            find('qris-status').textContent = paidConfirmed
                ? 'Pembayaran diterima. Aktivasi membership sedang diproses. Tidak perlu membayar kembali.'
                : 'Pembayaran belum diterima. Status akan diperiksa otomatis.';
        } catch (exception) {
            if (version === generation && isOpen()) find('qris-status').textContent = exception.message;
        } finally {
            checking = false;
            paid.disabled = false;
            paid.textContent = 'Cek Status Pembayaran';
            if (version === generation && isOpen() && step === 'qris') schedulePoll();
        }
    };
    const schedulePoll = () => { clearTimeout(poll); poll = setTimeout(refreshStatus, 4000); };
    const resumePayment = () => {
        stopTimers();
        const expiry = Date.parse(payment?.transaction_expire || '');
        const tick = () => {
            if (paidConfirmed) { find('qris-countdown').textContent = 'Pembayaran diterima'; clearInterval(countdown); return; }
            if (!Number.isFinite(expiry)) { find('qris-countdown').textContent = 'Ikuti masa berlaku pada QRIS'; return; }
            const seconds = Math.max(0, Math.ceil((expiry - Date.now()) / 1000));
            find('qris-countdown').textContent = `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
            if (seconds === 0) {
                clearInterval(countdown);
                find('qris-status').textContent = 'Masa berlaku QRIS berakhir. Memeriksa status pembayaran terakhir...';
                // Only the server can declare an expired or failed payment.
            }
        };
        if (Number.isFinite(expiry) && expiry > Date.now()) countdown = setInterval(tick, 1000);
        tick();
        schedulePoll();
    };
    const showPayment = value => {
        if (!value?.transaction_id) throw new Error('ID pembayaran tidak tersedia. Silakan hubungi administrator.');
        const url = safeUrl(value.qris_url);
        if (!url) throw new Error('Kode QRIS belum tersedia. Silakan cek kembali status membership sebelum mencoba lagi.');
        generation++; payment = value; paidConfirmed = String(value.status).toUpperCase() === 'SUCCESS';
        const proxy = `${modal.dataset.qrisUrl}?url=${encodeURIComponent(url)}`;
        find('qris-img').src = `${proxy}&disposition=inline`;
        find('qris-img').onerror = () => {
            find('qris-status').textContent = 'Gambar QRIS tidak dapat dimuat. Gunakan Buka QRIS atau Download QRIS.';
        };
        find('qris-link').href = url;
        find('qris-download').href = `${proxy}&filename=${encodeURIComponent(`qris-${value.transaction_id}.png`)}`;
        const expiry = Date.parse(value.transaction_expire || '');
        find('qris-expire').textContent = Number.isFinite(expiry) ? new Date(expiry).toLocaleString('id-ID') : 'Sesuai masa berlaku QRIS';
        const amount = Number(value.transaction_amount);
        if (Number.isFinite(amount) && amount > 0) find('qris-amount').textContent = `Rp ${new Intl.NumberFormat('id-ID').format(amount)}`;
        find('qris-status').textContent = 'Menunggu pembayaran...';
        showStep('qris');
        if (isOpen()) resumePayment();
    };
    const close = () => {
        if (!isOpen()) return;
        stopTimers(); generation++;
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = previousOverflow;
        if (opener && !opener.closest('[inert]') && opener.getClientRects().length) opener.focus();
        else document.querySelector('[data-menu-toggle]')?.focus();
    };
    const open = trigger => {
        if (isOpen()) return;
        opener = trigger || document.activeElement;
        document.dispatchEvent(new Event('portal:close-menu'));
        previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        showStep(step);
        if (step === 'qris') resumePayment();
    };
    document.getElementById('btn-join-membership')?.addEventListener('click', event => { track('click_membership_join'); open(event.currentTarget); });
    document.getElementById('sidebar-join-membership')?.addEventListener('click', event => { event.preventDefault(); track('click_membership_menu'); open(event.currentTarget); });
    ['modal-close', 'modal-backdrop', 'qris-cancel'].forEach(id => find(id)?.addEventListener('click', close));
    find('success-btn').addEventListener('click', () => location.reload());
    find('retry-btn').addEventListener('click', () => { payment = null; paidConfirmed = false; error.hidden = true; showStep('form'); });
    paid.addEventListener('click', refreshStatus);
    document.addEventListener('keydown', event => {
        if (!isOpen()) return;
        if (event.key === 'Escape') { close(); return; }
        if (event.key !== 'Tab') return;
        const items = [...modal.querySelectorAll('button:not(:disabled),a[href],input:not([type="hidden"])')].filter(el => el.getClientRects().length);
        const first = items[0], last = items[items.length - 1];
        if (event.shiftKey && (document.activeElement === first || document.activeElement.tagName === 'H2')) { event.preventDefault(); last?.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (busy) return;
        error.hidden = true;
        const normalized = phone.value.replace(/[\s()\-]/g, '');
        if (!/^(?:\+?62|0)?8[0-9]{8,11}$/.test(normalized)) {
            showError('Masukkan nomor telepon Indonesia yang valid, misalnya 081234567890.'); phone.focus(); return;
        }
        busy = true; submit.disabled = true;
        find('join-btn-text').hidden = true; find('join-loading').hidden = false;
        try {
            const data = await read(await fetch(modal.dataset.joinUrl, {
                method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': form.elements._token.value},
                body: JSON.stringify({customer_phone: normalized}),
            }));
            const value = data.data?.payment;
            // An empty payment object is not evidence of an active membership.
            if (!value || String(value.status).toUpperCase() === 'SUCCESS') {
                const status = await read(await fetch(modal.dataset.membershipUrl, {headers: {Accept: 'application/json'}}));
                if (active(status.data?.is_member)) { success(); return; }
                if (!value?.qris_url) throw new Error('Membership belum terkonfirmasi aktif. Periksa status pembayaran atau hubungi administrator sebelum membayar kembali.');
            }
            if (value.qris_url) { showPayment(value); return; }
            const redirect = safeUrl(value.redirect_url);
            if (redirect && isOpen()) { location.assign(redirect); return; }
            throw new Error('Detail pembayaran belum lengkap. Silakan cek status membership sebelum mencoba lagi.');
        } catch (exception) { showError(exception.message); }
        finally {
            busy = false; submit.disabled = false;
            find('join-btn-text').hidden = false; find('join-loading').hidden = true;
        }
    });
    window.addEventListener('pagehide', stopTimers);
    window.addEventListener('hashchange', () => { if (location.hash === '#membership') open(); });
    if (location.hash === '#membership') open();
})();
