<div class="campaign-map-modal" id="campaignMapModal" hidden aria-hidden="true">
    <div class="campaign-map-modal__backdrop" data-location-close onclick="window.closeCampaignLocationModal && window.closeCampaignLocationModal()"></div>
    <div class="campaign-map-modal__card" role="dialog" aria-modal="true" aria-labelledby="campaignMapHeading">
        <div class="campaign-map-modal__layout">
            <div class="campaign-map-panel">
                <div class="campaign-map-panel__canvas campaign-map-panel__canvas--live" id="campaignMapCanvas"></div>
                <p class="campaign-map-panel__hint">Klik peta atau cari alamat untuk menentukan titik target.</p>
            </div>

            <div class="campaign-map-controls">
                <div class="campaign-map-controls__top">
                    <h2 id="campaignMapHeading">Tambah Lokasi</h2>
                    <button type="button" class="campaign-map-close" data-location-close aria-label="Tutup lokasi" onclick="window.closeCampaignLocationModal && window.closeCampaignLocationModal()">×</button>
                </div>

                <div class="campaign-map-search-host" id="campaignLocationSearchHost"></div>
                <p class="campaign-map-search-feedback" id="campaignMapFeedback">Cari lokasi atau klik langsung pada peta untuk menentukan area target.</p>

                <p class="campaign-block__copy">
                    Tentukan lokasi yang Anda inginkan dengan mengetik nama atau alamat lokasi pada kolom pencarian, lalu atur radius pengiriman.
                </p>

                    <section class="campaign-map-settings">
                        <h3>Pengaturan</h3>
                        <div class="campaign-map-settings__grid">
                            <div class="field-group campaign-field-group--compact">
                                <label for="campaignLocationRadius" class="field-label">Radius (dalam Meter)</label>
                                <input id="campaignLocationRadius" @unless($locationUseHiddenRadius ?? false) name="location_radius" form="campaignGwSubmitForm" @endunless type="number" class="text-input" min="0" step="100" value="{{ $locationInitialRadius ?? 300 }}">
                            </div>
                        </div>

                    {{-- <div class="campaign-map-estimate">
                            <button type="button" class="campaign-outline-button campaign-map-estimate__button" id="campaignCalcAudience">
                                <span class="campaign-outline-button__icon">✓</span>
                                <span>Hitung Penerima Potensial</span>
                            </button>
                            <div class="campaign-map-estimate__result">
                                <span>Estimasi Penerima Potensial</span>
                                <strong id="campaignAudienceEstimate">0</strong>
                            </div>
                        </div> --}}

                    {{-- <label class="campaign-map-checkbox">
                            <input type="checkbox" id="campaignLocationRemember">
                            <span>Simpan pengaturan lokasi agar dapat digunakan kembali</span>
                        </label> --}}
                </section>

                <div class="campaign-map-actions">
                    <button type="button" class="campaign-modal-btn campaign-modal-btn--primary" id="campaignApplyLocation" onclick="window.applyCampaignLocationSelection && window.applyCampaignLocationSelection()">Gunakan Lokasi</button>
                    <button type="button" class="campaign-map-cancel" data-location-close onclick="window.closeCampaignLocationModal && window.closeCampaignLocationModal()">Batal</button>
                </div>
            </div>
        </div>
    </div>
</div>
