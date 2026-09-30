@php
    $templateValue = fn (string $field, mixed $default = null) => old($field, $isWaTemplateRead ? $templateRow->{$field} : $default);
    $templatePayloadValue = fn (string $field, mixed $default = null) => old($field, $isWaTemplateRead ? data_get($templateRow->gw_payload ?? [], $field, $default) : $default);
    $previewButtons = collect(data_get($templateRow ?? null, 'buttons', data_get($templateRow ?? null, 'gw_payload.buttons', [])))
        ->filter(fn ($button) => is_array($button) && trim((string) ($button['label'] ?? '')) !== '')
        ->values();
    $previewHeaderType = strtoupper((string) $templateValue('header_type', 'TEXT'));
    $previewHeaderLabel = trim((string) $templatePayloadValue('headerLabel', $templateValue('name', '')));
@endphp

@if ($errors->any())
    <section class="template-library-card">
        <strong>Template belum bisa disimpan.</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </section>
@endif

@if ($isWaTemplateRead)
    <div class="template-builder-layout">
@else
    <form method="POST" action="{{ route('campaign-template.store') }}" enctype="multipart/form-data" class="template-builder-layout" id="templateCreateForm">
        @csrf
@endif
    <div class="template-builder-main">
        <article class="template-builder-card">
            <h2>Template name and language</h2>
            @unless ($isWaTemplateRead)
                <input type="hidden" name="template_type" value="{{ $templateValue('template_type', 'simple_message') }}">
            @endunless
            <div class="template-builder-field">
                <label>Name your template</label>
                <input type="text" name="name" value="" required @readonly($isWaTemplateRead)>
            </div>

            @unless ($isWaTemplateRead)
                <input type="hidden" name="category" value="{{ $templateValue('category', 'Single Banner') }}">
            @else
                <div class="template-builder-field">
                    <label>Category Template</label>
                    <select name="category" required disabled>
                        <option value="Single Banner" @selected($templateValue('category', 'Single Banner') === 'Single Banner')>Single Banner</option>
                    </select>
                </div>
            @endunless

            <div class="template-builder-field">
                <label>Language</label>
                <select name="language" required @disabled($isWaTemplateRead)>
                    <option value="INDONESIA" @selected(in_array($templateValue('language', 'INDONESIA'), ['INDONESIA', 'Indonesia'], true))>INDONESIA</option>
                    <option value="ENGLISH" @selected(in_array($templateValue('language'), ['ENGLISH', 'English'], true))>ENGLISH</option>
                </select>
            </div>

            @unless ($isWaTemplateRead)
                <div class="template-builder-field" data-waba-display-name-field data-list-url="{{ route('campaign-template.display-name-list') }}">
                    <label>Display Name</label>
                    <select id="wabaDisplayNameSelect" name="display_name" required>
                        <option value="">Memuat display name...</option>
                    </select>
                    <input type="hidden" id="wabaAccountIdInput" name="waba_account_id" value="{{ old('waba_account_id') }}">
                    <input type="hidden" id="phoneNumberIdInput" name="phone_number_id" value="{{ old('phone_number_id') }}">
                </div>
            @else
                <div class="template-builder-field">
                    <label>Display Name</label>
                    <input type="text" value="{{ $templatePayloadValue('verifiedName', $templatePayloadValue('displayName')) }}" readonly>
                </div>
            @endunless
        </article>

        <article class="template-builder-card">
            <h2>Content</h2>
            <p>Fill in the header, body and footer section of your template</p>

            <div class="template-builder-field">
                <label>Header Type</label>
                <select name="header_type" id="templateHeaderTypeSelect" @disabled($isWaTemplateRead)>
                    <option value="none" @selected(in_array($templateValue('header_type', 'none'), ['none', ''], true))>Tidak menggunakan Header</option>
                    <option value="TEXT" @selected($templateValue('header_type') === 'TEXT')>Teks</option>
                    <option value="IMAGE" @selected(in_array($templateValue('header_type'), ['IMAGE', 'image'], true))>Gambar</option>
                </select>
            </div>

            <div class="template-builder-field" data-template-header-label-field @hidden($templateValue('header_type') !== 'TEXT')>
                <label>Header Text</label>
                <input type="text" name="header_label" value="{{ $templatePayloadValue('headerLabel') }}" maxlength="60" placeholder="Teks header" data-template-header-label-input @readonly($isWaTemplateRead)>
                <span class="template-builder-button-card__meta" data-template-header-label-count>0/60 Karakter</span>
            </div>

            <div class="template-builder-field" data-template-asset-field @hidden(! in_array($templateValue('header_type'), ['IMAGE', 'image'], true))>
                <label>Asset</label>
                <p class="template-builder-field__hint">Upload gambar untuk header template.</p>
                <label class="template-builder-upload" for="templateAssetInput">
                    <input type="file" id="templateAssetInput" name="asset" class="template-builder-upload__input" accept="image/*" @disabled($isWaTemplateRead)>
                    <span class="template-builder-upload__icon">Upload</span>
                    <strong id="templateAssetLabel">{{ $isWaTemplateRead && $templateRow->asset_path ? basename($templateRow->asset_path) : 'Upload an asset' }}</strong>
                    <small>(Ensure your asset meets the format and size requirement)</small>
                    <span class="template-builder-upload__link">Learn more about supported format assets</span>
                </label>
            </div>
            <div class="template-builder-field">
                <label>Body</label>
                <div class="template-builder-editor">
                    <div class="template-builder-editor__toolbar">
                        <button type="button" data-template-format="bold" aria-label="Tebal" @disabled($isWaTemplateRead)>B</button>
                        <button type="button" data-template-format="italic" aria-label="Miring" @disabled($isWaTemplateRead)><i>I</i></button>
                        <button type="button" data-template-format="code" aria-label="Monospace" @disabled($isWaTemplateRead)>&lt;/&gt;</button>
                        <button type="button" data-template-format="strike" aria-label="Coret" @disabled($isWaTemplateRead)><s>S</s></button>
                        <button type="button" data-template-format="bullet" aria-label="Daftar poin" @disabled($isWaTemplateRead)>-</button>
                        <button type="button" data-template-format="number" aria-label="Daftar nomor" @disabled($isWaTemplateRead)>1.</button>
                    </div>
                    <textarea id="templateBodyInput" name="body" maxlength="550" placeholder="Isi Pesan" required @readonly($isWaTemplateRead)>{{ $templateValue('body') }}</textarea>
                    <span class="template-builder-editor__count" id="templateBodyCount">0/550 Karakter</span>
                </div>
            </div>

        </article>

        <article class="template-builder-card">
            <h2>Buttons</h2>
            <button type="button" class="template-builder-add-button" id="templateAddButton" @disabled($isWaTemplateRead)>
                <span>+</span>
                <span>Add Button</span>
            </button>
            <p class="template-builder-button-warning" id="templateButtonWarning" hidden>Button hanya bisa 10.</p>

            <div class="template-builder-button-list" id="templateButtonList"></div>

            <div class="template-builder-tip">
                <strong>We recomend adding the marketing op-out button</strong>
                <p>Allow customers to request to opout all marketing messages. this can help reduce blocks from customers and increase your quality rating</p>
            </div>

            <div class="template-builder-inline-actions">
                <a href="{{ route('campaign-template.index') }}" class="template-builder-secondary">Cancel</a>
                @unless ($isWaTemplateRead)
                    <button type="submit" class="template-builder-add-button" id="templateSaveButton">
                        <span>Save Template</span>
                    </button>
                @endunless
            </div>
        </article>
    </div>

    <aside class="template-builder-preview">
        <article class="template-builder-preview__card">
            <h2>Template Preview</h2>
            <div class="template-builder-phone">
                <div class="template-builder-phone__message">
                    <strong class="template-builder-phone__header-text" id="templatePreviewHeaderText" @hidden($previewHeaderType !== 'TEXT' || $previewHeaderLabel === '')>{{ $previewHeaderLabel }}</strong>
                    <img src="{{ $isWaTemplateRead && $templateRow->asset_path ? asset('storage/' . $templateRow->asset_path) : asset('assets/shopads-icon.svg') }}" alt="Preview asset" class="template-builder-phone__image" id="templatePreviewImage" @hidden($previewHeaderType !== 'IMAGE')>
                    <div class="template-builder-phone__body" id="templatePreviewBody">{{ $templateValue('body') }}</div>
                    <div class="template-builder-phone__actions" id="templatePreviewActions">
                        @foreach ($previewButtons as $button)
                            <div class="template-builder-phone__cta">{{ $button['label'] }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="template-builder-preview__foot">
                <strong>This template is good for:</strong>
                <p>Welcome messages, promotions, offers, coupons, newsletters, announcements</p>
            </div>
        </article>
    </aside>
@if ($isWaTemplateRead)
    </div>
@else
    </form>
    <div class="template-submit-loader" id="templateSubmitLoader" role="status" aria-live="polite" hidden>
        <div class="template-submit-loader__card">
            <span class="portal-page-loader__spinner" aria-hidden="true"></span>
            <strong>Mengunggah dan menyimpan template...</strong>
            <small>Mohon tunggu, jangan tutup halaman ini.</small>
        </div>
    </div>
@endif
