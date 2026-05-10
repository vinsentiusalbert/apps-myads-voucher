<script>
    (() => {
        const templateBodyInput = document.getElementById('templateBodyInput');
        const templateBodyCount = document.getElementById('templateBodyCount');
        const templatePreviewBody = document.getElementById('templatePreviewBody');
        const templateAssetInput = document.getElementById('templateAssetInput');
        const templatePreviewImage = document.getElementById('templatePreviewImage');
        const templateAssetLabel = document.getElementById('templateAssetLabel');
        const templateAddButton = document.getElementById('templateAddButton');
        const templateButtonList = document.getElementById('templateButtonList');
        const templatePreviewActions = document.getElementById('templatePreviewActions');
        const templateButtonWarning = document.getElementById('templateButtonWarning');
        const templateTypeInputs = Array.from(document.querySelectorAll('input[name="template_type"]'));
        const isTemplateRead = @json($isWaTemplateRead);
        let templateButtonIndex = 0;

        const defaultPreviewBody = `Halo Pelanggan Setia ComboFit, kamu mendapatkan akses Exercise Plan GRATIS selama 30 hari

- Akses ke program latihan fisik oleh Coach profesional
- Panduan video latihan yang mudah diikuti
- Tips dan trik kebugaran

Aktifkan akunmu di link ini
https://bit.ly/Fita_Combofit

#SehatMakinNikmat bersama Fita!`;

        const syncTemplateChoiceState = () => {
            templateTypeInputs.forEach((input) => {
                input.closest('.template-builder-choice')?.classList.toggle('template-builder-choice--active', input.checked);
            });
        };

        const syncTemplateBodyPreview = () => {
            if (!templateBodyInput || !templatePreviewBody || !templateBodyCount) {
                return;
            }

            const value = templateBodyInput.value.trim();
            templatePreviewBody.textContent = value || defaultPreviewBody;
            templateBodyCount.textContent = `${templateBodyInput.value.length} / 1024`;
        };

        const syncTemplateAssetPreview = () => {
            if (!templateAssetInput || !templatePreviewImage || !templateAssetLabel) {
                return;
            }

            const [file] = templateAssetInput.files || [];
            if (!file) {
                templatePreviewImage.src = "{{ asset('assets/logo.png') }}";
                templateAssetLabel.textContent = 'Upload an asset';
                return;
            }

            templateAssetLabel.textContent = file.name;
            const reader = new FileReader();
            reader.onload = (event) => {
                if (typeof event.target?.result === 'string') {
                    templatePreviewImage.src = event.target.result;
                }
            };
            reader.readAsDataURL(file);
        };

        const syncTemplateButtons = () => {
            if (!templateButtonList || !templatePreviewActions || !templateAddButton || !templateButtonWarning) {
                return;
            }

            const cards = Array.from(templateButtonList.querySelectorAll('[data-template-button-card]'));
            templateButtonWarning.hidden = cards.length < 2;
            templateAddButton.disabled = cards.length >= 2;

            if (!cards.length) {
                templatePreviewActions.innerHTML = '<div class="template-builder-phone__cta">Coba Sekarang</div>';
                return;
            }

            templatePreviewActions.innerHTML = cards.map((card) => {
                const label = card.querySelector('[data-template-button-text]')?.value?.trim() || 'Visit website';
                return `<div class="template-builder-phone__cta">${label}</div>`;
            }).join('');
        };

        const bindTemplateButtonCard = (card) => {
            const textInput = card.querySelector('[data-template-button-text]');
            const urlInput = card.querySelector('[data-template-button-url]');
            const actionInput = card.querySelector('[data-template-button-action]');
            const replayInput = card.querySelector('[data-template-button-replay]');
            const removeButton = card.querySelector('[data-template-button-remove]');
            const textMeta = card.querySelector('[data-template-button-meta]');
            const urlMeta = card.querySelector('[data-template-url-meta]');

            const syncCard = () => {
                if (textMeta && textInput) {
                    textMeta.textContent = `${textInput.value.length} / 25`;
                }
                if (urlMeta && urlInput) {
                    urlMeta.textContent = `${urlInput.value.length} / 2000`;
                }
                if (replayInput && textInput) {
                    replayInput.value = textInput.value;
                }
                syncTemplateButtons();
            };

            textInput?.addEventListener('input', syncCard);
            urlInput?.addEventListener('input', syncCard);
            actionInput?.addEventListener('change', syncCard);
            removeButton?.addEventListener('click', () => {
                card.remove();
                syncTemplateButtons();
            });

            syncCard();
        };

        syncTemplateBodyPreview();
        syncTemplateChoiceState();

        templateBodyInput?.addEventListener('input', syncTemplateBodyPreview);
        templateTypeInputs.forEach((input) => {
            input.addEventListener('change', syncTemplateChoiceState);
        });

        if (!isTemplateRead) {
            syncTemplateAssetPreview();
            templateAssetInput?.addEventListener('change', syncTemplateAssetPreview);
        }

        templateAddButton?.addEventListener('click', () => {
            if (!templateButtonList || templateButtonList.querySelectorAll('[data-template-button-card]').length >= 2) {
                syncTemplateButtons();
                return;
            }

            templateButtonIndex += 1;
            const inputName = `buttons[${templateButtonIndex}]`;
            const card = document.createElement('article');
            card.className = 'template-builder-button-card';
            card.dataset.templateButtonCard = String(templateButtonIndex);
            card.innerHTML = `
                <div class="template-builder-button-card__head">
                    <h3>Call to Action</h3>
                </div>
                <div class="template-builder-button-card__grid">
                    <div class="template-builder-button-card__field">
                        <label>Type of action</label>
                        <select name="${inputName}[action]" data-template-button-action>
                            <option value="URL">Visit website</option>
                            <option value="QUICK_REPLY">Quick reply</option>
                        </select>
                    </div>
                    <div class="template-builder-button-card__field">
                        <label>Button Text</label>
                        <input type="text" name="${inputName}[label]" value="Visit website" maxlength="25" data-template-button-text>
                        <span class="template-builder-button-card__meta" data-template-button-meta>13 / 25</span>
                    </div>
                    <div class="template-builder-button-card__field">
                        <label>Url Type</label>
                        <select name="${inputName}[show_modal_short_url]">
                            <option value="0">static</option>
                            <option value="1">short URL</option>
                        </select>
                    </div>
                    <div class="template-builder-button-card__field">
                        <label>Website URL</label>
                        <input type="text" name="${inputName}[action_url_target]" value="" maxlength="2000" placeholder="Website URL" data-template-button-url>
                        <span class="template-builder-button-card__meta" data-template-url-meta>0 / 2000</span>
                    </div>
                    <input type="hidden" name="${inputName}[action_replay]" value="" data-template-button-replay>
                    <input type="hidden" name="${inputName}[action_number]" value="">
                    <input type="hidden" name="${inputName}[short_url]" value="">
                    <input type="hidden" name="${inputName}[action_voucher_code]" value="">
                    <button type="button" class="template-builder-button-card__remove" data-template-button-remove aria-label="Hapus tombol">x</button>
                </div>
                <div class="template-builder-button-card__alert">
                    <span>!</span>
                    <p>Direct WhatsApp links are not allowed in CTA buttons as per Meta's policy. Please place the link (e.g. wa.me/6281xxx) in the message body instead</p>
                </div>
            `;

            templateButtonList.appendChild(card);
            bindTemplateButtonCard(card);
            syncTemplateButtons();
        });

        syncTemplateButtons();
    })();
</script>
