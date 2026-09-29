<script>
    (() => {
        const templateBodyInput = document.getElementById('templateBodyInput');
        const templateBodyCount = document.getElementById('templateBodyCount');
        const templatePreviewHeaderText = document.getElementById('templatePreviewHeaderText');
        const templatePreviewBody = document.getElementById('templatePreviewBody');
        const templateAssetInput = document.getElementById('templateAssetInput');
        const templatePreviewImage = document.getElementById('templatePreviewImage');
        const templateAssetLabel = document.getElementById('templateAssetLabel');
        const templateAddButton = document.getElementById('templateAddButton');
        const templateButtonList = document.getElementById('templateButtonList');
        const templatePreviewActions = document.getElementById('templatePreviewActions');
        const templateButtonWarning = document.getElementById('templateButtonWarning');
        const templateHeaderTypeSelect = document.getElementById('templateHeaderTypeSelect');
        const templateHeaderLabelField = document.querySelector('[data-template-header-label-field]');
        const templateHeaderLabelInput = document.querySelector('[data-template-header-label-input]');
        const templateHeaderLabelCount = document.querySelector('[data-template-header-label-count]');
        const templateAssetField = document.querySelector('[data-template-asset-field]');
        const templateCreateForm = document.getElementById('templateCreateForm');
        const templateSaveButton = document.getElementById('templateSaveButton');
        const templateSubmitLoader = document.getElementById('templateSubmitLoader');
        const wabaDisplayNameField = document.querySelector('[data-waba-display-name-field]');
        const wabaDisplayNameSelect = document.getElementById('wabaDisplayNameSelect');
        const wabaAccountIdInput = document.getElementById('wabaAccountIdInput');
        const phoneNumberIdInput = document.getElementById('phoneNumberIdInput');
        const oldWabaAccountId = <?php echo json_encode(old('waba_account_id'), 15, 512) ?>;
        const oldPhoneNumberId = <?php echo json_encode(old('phone_number_id'), 15, 512) ?>;
        const oldDisplayName = <?php echo json_encode(old('display_name'), 15, 512) ?>;
        const isTemplateRead = <?php echo json_encode($isWaTemplateRead, 15, 512) ?>;
        const defaultCtaLink = <?php echo json_encode($defaultCtaLink ?? '', 15, 512) ?>;
        const getDefaultCtaUrl = () => String(defaultCtaLink || '').trim();
        const normalizeCtaUrl = (value) => String(value || '').trim();
        const removeEmoticons = (value) => String(value || '')
            .replace(/[\p{Symbol}\u{FE0F}\u{200D}\u{20E3}]/gu, '');
        let templateButtonIndex = 0;

        const syncTemplateBodyPreview = () => {
            if (!templateBodyInput || !templatePreviewBody || !templateBodyCount) {
                if (templateBodyInput && templateBodyCount) {
                    templateBodyCount.textContent = `${templateBodyInput.value.length}/550 Karakter`;
                }
                return;
            }
            if (isTemplateRead) {
                templateBodyCount.textContent = `${templateBodyInput.value.length}/550 Karakter`;
                return;
            }

            const value = templateBodyInput.value.trim();
            templatePreviewBody.textContent = value;
            templateBodyCount.textContent = `${templateBodyInput.value.length}/550 Karakter`;
        };

        const syncTemplateAssetPreview = () => {
            if (!templateAssetInput || !templatePreviewImage || !templateAssetLabel) {
                return;
            }

            const [file] = templateAssetInput.files || [];
            if (!file) {
                templateAssetLabel.textContent = 'Upload an asset';
                templatePreviewImage.hidden = true;
                return;
            }

            templateAssetLabel.textContent = file.name;
            const reader = new FileReader();
            reader.onload = (event) => {
                if (typeof event.target?.result === 'string') {
                    templatePreviewImage.src = event.target.result;
                    templatePreviewImage.hidden = templateHeaderTypeSelect?.value !== 'IMAGE';
                }
            };
            reader.readAsDataURL(file);
        };

        const syncTemplateButtons = () => {
            if (!templateButtonList || !templateAddButton || !templateButtonWarning) {
                return;
            }
            if (isTemplateRead) {
                return;
            }

            const cards = Array.from(templateButtonList.querySelectorAll('[data-template-button-card]'));
            templateButtonWarning.hidden = cards.length < 10;
            templateAddButton.disabled = cards.length >= 10;

            if (!templatePreviewActions) {
                return;
            }

            if (!cards.length) {
                templatePreviewActions.innerHTML = '';
                return;
            }

            const escapeHtml = (value) => String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            templatePreviewActions.innerHTML = cards.map((card) => {
                const label = card.querySelector('[data-template-button-text]')?.value?.trim() || 'Quick Reply';
                return `<div class="template-builder-phone__cta">${escapeHtml(label)}</div>`;
            }).join('');
        };

        const bindTemplateButtonCard = (card) => {
            const textInput = card.querySelector('[data-template-button-text]');
            const urlInput = card.querySelector('[data-template-button-url]');
            const urlSuffixInput = card.querySelector('[data-template-button-url-suffix]');
            const actionInput = card.querySelector('[data-template-button-action]');
            const replayInput = card.querySelector('[data-template-button-replay]');
            const removeButton = card.querySelector('[data-template-button-remove]');
            const textMeta = card.querySelector('[data-template-button-meta]');
            const urlMeta = card.querySelector('[data-template-url-meta]');
            const urlField = card.querySelector('[data-template-url-field]');
            const urlTypeField = card.querySelector('[data-template-url-type-field]');

            const syncCard = () => {
                if (textMeta && textInput) {
                    textMeta.textContent = `${textInput.value.length} / 25`;
                }
                if (replayInput && textInput) {
                    replayInput.value = textInput.value;
                }
                if (actionInput && urlField && urlTypeField) {
                    const isUrl = actionInput.value === 'URL';
                    urlField.hidden = !isUrl;
                    urlTypeField.hidden = !isUrl;
                    if (urlInput && urlSuffixInput) {
                        const normalizedUrl = normalizeCtaUrl(urlSuffixInput.value);
                        if (urlSuffixInput.value !== normalizedUrl) {
                            urlSuffixInput.value = normalizedUrl;
                        }
                        urlInput.value = isUrl ? normalizedUrl : '';
                    }
                }
                if (urlMeta && urlInput) {
                    urlMeta.textContent = `${urlInput.value.length} / 2000`;
                }
                syncTemplateButtons();
            };

            textInput?.addEventListener('input', syncCard);
            urlSuffixInput?.addEventListener('input', syncCard);
            actionInput?.addEventListener('change', syncCard);
            removeButton?.addEventListener('click', () => {
                card.remove();
                syncTemplateButtons();
            });

            syncCard();
        };

        const syncHeaderFields = (resetHeader = false) => {
            if (!templateHeaderTypeSelect || !templateHeaderLabelField || !templateAssetField) {
                return;
            }

            if (resetHeader) {
                if (templateHeaderLabelInput) {
                    templateHeaderLabelInput.value = '';
                }
                if (templateAssetInput) {
                    templateAssetInput.value = '';
                }
                if (templateAssetLabel) {
                    templateAssetLabel.textContent = 'Upload an asset';
                }
                if (templatePreviewImage) {
                    templatePreviewImage.removeAttribute('src');
                    templatePreviewImage.hidden = true;
                }
            }

            if (!isTemplateRead && templateHeaderLabelInput) {
                const sanitizedHeader = removeEmoticons(templateHeaderLabelInput.value);
                if (templateHeaderLabelInput.value !== sanitizedHeader) {
                    templateHeaderLabelInput.value = sanitizedHeader;
                }
            }

            const headerType = templateHeaderTypeSelect.value;
            templateHeaderLabelField.hidden = headerType !== 'TEXT';
            templateAssetField.hidden = headerType !== 'IMAGE';

            if (templateHeaderLabelInput && templateHeaderLabelCount) {
                templateHeaderLabelCount.textContent = `${templateHeaderLabelInput.value.length}/60 Karakter`;
            }

            if (templatePreviewHeaderText) {
                const headerText = templateHeaderLabelInput?.value?.trim() || '';
                templatePreviewHeaderText.textContent = headerText;
                templatePreviewHeaderText.hidden = headerType !== 'TEXT' || headerText === '';
            }

            if (templatePreviewImage) {
                const hasImage = Boolean(templateAssetInput?.files?.length);
                templatePreviewImage.hidden = headerType !== 'IMAGE' || (!isTemplateRead && !hasImage);
            }
        };

        const valueFrom = (row, keys) => {
            for (const key of keys) {
                if (row && row[key] !== undefined && row[key] !== null && String(row[key]).trim() !== '') {
                    return String(row[key]).trim();
                }
            }

            return '';
        };

        const extractWabaDisplayNameRows = (payload) => {
            const candidates = [
                payload?.list,
                payload?.data?.data,
                payload?.data?.data?.data,
                payload?.data?.data?.list,
                payload?.data?.data?.items,
                payload?.data?.list,
                payload?.data?.items,
                payload?.data,
                payload,
            ];

            for (const candidate of candidates) {
                if (Array.isArray(candidate)) {
                    return candidate.filter((row) => row && typeof row === 'object');
                }
            }

            return [];
        };

        const syncSelectedWabaDisplayName = () => {
            if (!wabaDisplayNameSelect || !wabaAccountIdInput || !phoneNumberIdInput) {
                return;
            }

            const selected = wabaDisplayNameSelect.selectedOptions[0];
            wabaAccountIdInput.value = selected?.dataset.wabaAccountId || '';
            phoneNumberIdInput.value = selected?.dataset.phoneNumberId || '';
        };

        const applyWabaDisplayNameOptions = (rows, placeholder = 'Pilih display name') => {
            if (!wabaDisplayNameSelect) {
                return;
            }

            const placeholderOption = new Option(placeholder, '');
            placeholderOption.disabled = true;
            placeholderOption.hidden = true;
            placeholderOption.selected = true;
            wabaDisplayNameSelect.replaceChildren(placeholderOption);

            rows.forEach((row) => {
                const verifiedName = valueFrom(row, ['verifiedName', 'verified_name', 'displayName', 'display_name', 'name']);
                const wabaAccountId = valueFrom(row, ['wabaAccountId', 'waba_account_id', 'accountId', 'account_id']);
                const phoneNumberId = valueFrom(row, ['phoneNumberId', 'phoneNumberiId', 'phone_number_id']);

                if (verifiedName === '') {
                    return;
                }

                const option = new Option(verifiedName, verifiedName);
                option.dataset.wabaAccountId = wabaAccountId;
                option.dataset.phoneNumberId = phoneNumberId;
                option.selected = (oldWabaAccountId && oldWabaAccountId === wabaAccountId)
                    || (oldPhoneNumberId && oldPhoneNumberId === phoneNumberId)
                    || (oldDisplayName && oldDisplayName === verifiedName);
                wabaDisplayNameSelect.appendChild(option);
            });

            if (wabaDisplayNameSelect.options.length === 1) {
                const emptyOption = new Option('Display name tidak tersedia', '');
                emptyOption.disabled = true;
                emptyOption.selected = true;
                wabaDisplayNameSelect.replaceChildren(emptyOption);
            } else if (!wabaDisplayNameSelect.value) {
                wabaDisplayNameSelect.selectedIndex = 1;
            }

            syncSelectedWabaDisplayName();
        };

        const loadWabaDisplayNames = async () => {
            if (!wabaDisplayNameField || !wabaDisplayNameSelect || !wabaAccountIdInput || !phoneNumberIdInput) {
                return;
            }

            const listUrl = wabaDisplayNameField.dataset.listUrl;
            if (!listUrl) {
                return;
            }

            wabaDisplayNameSelect.disabled = true;
            wabaDisplayNameSelect.replaceChildren(new Option('Memuat display name...', ''));

            try {
                const response = await fetch(listUrl, {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                const payload = await response.json();
                if (!response.ok) {
                    throw new Error(payload?.message || `Display name request failed with status ${response.status}`);
                }
                if (payload?.success === false) {
                    throw new Error(payload?.message || 'Display name request failed');
                }

                const rows = extractWabaDisplayNameRows(payload);
                applyWabaDisplayNameOptions(rows, 'Pilih display name');
            } catch (error) {
                applyWabaDisplayNameOptions([], 'Gagal memuat display name');
            } finally {
                wabaDisplayNameSelect.disabled = false;
            }
        };

        syncTemplateBodyPreview();

        templateBodyInput?.addEventListener('input', syncTemplateBodyPreview);

        if (!isTemplateRead) {
            syncTemplateAssetPreview();
            syncHeaderFields();
            templateAssetInput?.addEventListener('change', syncTemplateAssetPreview);
            templateHeaderTypeSelect?.addEventListener('change', () => syncHeaderFields(true));
            templateHeaderLabelInput?.addEventListener('input', () => syncHeaderFields());
            wabaDisplayNameSelect?.addEventListener('change', syncSelectedWabaDisplayName);
            loadWabaDisplayNames();

            templateCreateForm?.addEventListener('submit', () => {
                if (templateSaveButton) {
                    templateSaveButton.disabled = true;
                    templateSaveButton.querySelector('span').textContent = 'Menyimpan...';
                }
                if (templateSubmitLoader) {
                    templateSubmitLoader.hidden = false;
                }
            });
        }

        templateAddButton?.addEventListener('click', () => {
            if (!templateButtonList || templateButtonList.querySelectorAll('[data-template-button-card]').length >= 10) {
                syncTemplateButtons();
                return;
            }

            templateButtonIndex += 1;
            const inputName = `buttons[${templateButtonIndex}]`;
            
            const defaultUrl = getDefaultCtaUrl();
            const hasDefault = defaultUrl !== '';

            const card = document.createElement('article');
            card.className = 'template-builder-button-card';
            card.dataset.templateButtonCard = String(templateButtonIndex);
            card.innerHTML = `
                <div class="template-builder-button-card__head">
                    <h3>Button ${templateButtonIndex}</h3>
                </div>
                <div class="template-builder-button-card__grid">
                    <div class="template-builder-button-card__field">
                        <label>Label</label>
                        <input type="text" name="${inputName}[label]" value="" maxlength="25" placeholder="Label" data-template-button-text>
                        <span class="template-builder-button-card__meta" data-template-button-meta>0 / 25</span>
                    </div>
                    <div class="template-builder-button-card__field">
                        <label>Aksi</label>
                        <select name="${inputName}[action]" data-template-button-action>
                            <option value="QUICK_REPLY" ${hasDefault ? '' : 'selected'}>Quick Reply</option>
                            <option value="URL" ${hasDefault ? 'selected' : ''}>Visit Website</option>
                        </select>
                    </div>
                    <div class="template-builder-button-card__field" data-template-url-type-field ${hasDefault ? '' : 'hidden'}>
                        <label>Url Type</label>
                        <select name="${inputName}[show_modal_short_url]">
                            <option value="1">Short URL</option>
                        </select>
                    </div>
                    <div class="template-builder-button-card__field" data-template-url-field ${hasDefault ? '' : 'hidden'}>
                        <label>Website URL</label>
                        <input type="url" maxlength="2000" placeholder="https://example.com/promo" data-template-button-url-suffix>
                        <input type="hidden" name="${inputName}[action_url_target]" data-template-button-url>
                        <span class="template-builder-button-card__meta" data-template-url-meta>0 / 2000</span>
                    </div>
                    <input type="hidden" name="${inputName}[action_replay]" value="" data-template-button-replay>
                    <input type="hidden" name="${inputName}[action_number]" value="">
                    <input type="hidden" name="${inputName}[short_url]" value="">
                    <input type="hidden" name="${inputName}[action_voucher_code]" value="">
                    <button type="button" class="template-builder-button-card__remove" data-template-button-remove aria-label="Hapus tombol">x</button>
                </div>
            `;

            templateButtonList.appendChild(card);
            card.querySelector('[data-template-button-url-suffix]').value = defaultUrl;
            bindTemplateButtonCard(card);
            syncTemplateButtons();
        });

        syncTemplateButtons();
    })();
</script>
<?php /**PATH D:\1 Kerja\shop-myads\resources\views/campaign-wa-template/partials/preview-script.blade.php ENDPATH**/ ?>