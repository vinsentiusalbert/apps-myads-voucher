(() => {
    const modal = document.getElementById('campaignMapModal');
    const openButton = document.getElementById('voucherOpenLocation');
    if (!modal || !openButton) return;

    const shell = document.querySelector('.auth-shell');
    const canvas = document.getElementById('campaignMapCanvas');
    const feedback = document.getElementById('campaignMapFeedback');
    const radiusInput = document.getElementById('campaignLocationRadius');
    const applyButton = document.getElementById('campaignApplyLocation');
    const fields = Object.fromEntries(['name', 'lat', 'lng', 'radius'].map(key => [key, document.getElementById(`location_${key}`)]));
    let map;
    let marker;
    let circle;
    let draft;

    const readLocation = () => ({
        name: fields.name.value || 'Jakarta',
        lat: fields.lat.value !== '' ? Number(fields.lat.value) : -6.2,
        lng: fields.lng.value !== '' ? Number(fields.lng.value) : 106.816666,
        radius: Number(fields.radius.value) || 1000,
    });

    const updatePoint = (point, label) => {
        draft.lat = point.lat;
        draft.lng = point.lng;
        draft.name = String(label || `${point.lat.toFixed(6)}, ${point.lng.toFixed(6)}`).slice(0, 255);
        if (!marker) {
            marker = L.marker(point, { draggable: true }).addTo(map);
            marker.on('dragend', () => updatePoint(marker.getLatLng()));
            circle = L.circle(point, {
                radius: draft.radius, color: '#ff475d', weight: 2,
                fillColor: '#ff475d', fillOpacity: 0.16,
            }).addTo(map);
        }
        marker.setLatLng(point);
        circle.setLatLng(point).setRadius(draft.radius);
        feedback.textContent = `Titik aktif: ${draft.name}`;
    };

    const initMap = () => {
        if (map) return true;
        if (!window.L) {
            feedback.textContent = 'Peta belum dapat dimuat. Periksa koneksi internet lalu buka kembali popup lokasi.';
            return false;
        }
        map = L.map(canvas, { zoomControl: true }).setView([draft.lat, draft.lng], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19, attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);
        map.on('click', event => updatePoint(event.latlng));

        if (L.Control?.Geocoder && window.createIndonesiaGeocoder) {
            const geocoder = L.Control.geocoder({
                geocoder: window.createIndonesiaGeocoder(L),
                defaultMarkGeocode: false,
                collapsed: false,
                placeholder: 'Cari jalan atau alamat di Indonesia',
                queryMinLength: 3,
                suggestMinLength: 3,
                suggestTimeout: 250,
            }).on('markgeocode', event => {
                // Ignore delayed search results after the popup has closed.
                if (modal.hidden) return;
                updatePoint(event.geocode.center, event.geocode.name);
                map.setView(event.geocode.center, 15);
            }).addTo(map);
            document.getElementById('campaignLocationSearchHost').appendChild(geocoder.getContainer());
        }
        return true;
    };

    const closeModal = () => {
        if (modal.hidden) return;
        shell.inert = false;
        openButton.focus();
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('campaign-map-open');
    };

    const openModal = () => {
        draft = readLocation();
        radiusInput.value = draft.radius;
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('campaign-map-open');
        shell.inert = true;
        applyButton.disabled = !initMap();
        if (map) {
            updatePoint(L.latLng(draft.lat, draft.lng), draft.name);
            map.setView([draft.lat, draft.lng], 14);
            requestAnimationFrame(() => map.invalidateSize());
        }
        modal.querySelector('[data-location-close][aria-label]').focus();
    };

    const applyLocation = () => {
        if (!map || modal.hidden) return;
        if (!radiusInput.reportValidity()) return;
        draft.radius = Number(radiusInput.value);
        for (const key of Object.keys(fields)) fields[key].value = draft[key];
        document.getElementById('voucherLocationName').textContent = draft.name;
        document.getElementById('voucherLocationDetails').textContent = `${draft.lat.toFixed(6)}, ${draft.lng.toFixed(6)} · Radius ${draft.radius} meter`;
        openButton.textContent = 'Ubah lokasi di peta';
        closeModal();
    };

    radiusInput.min = '1';
    radiusInput.max = '50000';
    radiusInput.step = '1';
    radiusInput.required = true;
    radiusInput.addEventListener('input', () => {
        if (!radiusInput.validity.valid) return;
        draft.radius = Number(radiusInput.value);
        circle?.setRadius(draft.radius);
    });
    openButton.addEventListener('click', openModal);
    // The shared LBA popup buttons use these same handlers in the original form.
    window.openCampaignLocationModal = openModal;
    window.closeCampaignLocationModal = closeModal;
    window.applyCampaignLocationSelection = applyLocation;

    modal.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            event.preventDefault();
            closeModal();
        }
        if (event.key !== 'Tab') return;
        const focusable = [...modal.querySelectorAll('button, input, a[href], [tabindex]')]
            .filter(element => !element.disabled && element.tabIndex >= 0 && element.getClientRects().length);
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    document.getElementById('voucherCampaignForm').addEventListener('submit', event => {
        if (!fields.name.value || fields.lat.value === '' || fields.lng.value === '') {
            event.preventDefault();
            openModal();
            feedback.textContent = 'Pilih lokasi target lalu tekan Gunakan Lokasi sebelum menyimpan campaign.';
        }
    });
})();
