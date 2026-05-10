@php
    $templateValue = fn (string $field, mixed $default = null) => old($field, $isWaTemplateRead ? $templateRow->{$field} : $default);
    $templatePayloadValue = fn (string $field, mixed $default = null) => old($field, $isWaTemplateRead ? data_get($templateRow->gw_payload ?? [], $field, $default) : $default);
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
    <form method="POST" action="{{ route('campaign-template.store') }}" enctype="multipart/form-data" class="template-builder-layout">
        @csrf
@endif
    <div class="template-builder-main">
        <article class="template-builder-card">
            <h2>Template Preview</h2>
            <p>Choose the category that best describes your message template. Then, select the type of message that you want to send.</p>

            <label class="template-builder-choice {{ $templateValue('template_type', 'simple_message') === 'simple_message' ? 'template-builder-choice--active' : '' }}">
                <input type="radio" name="template_type" value="simple_message" @checked($templateValue('template_type', 'simple_message') === 'simple_message') @disabled($isWaTemplateRead)>
                <span class="template-builder-choice__dot"></span>
                <span>
                    <strong>Simple Message</strong>
                    <small>Custom send promotions with only have 1 upload image Capability</small>
                </span>
            </label>

            <label class="template-builder-choice {{ $templateValue('template_type') === 'carousel_cards' ? 'template-builder-choice--active' : '' }}">
                <input type="radio" name="template_type" value="carousel_cards" @checked($templateValue('template_type') === 'carousel_cards') @disabled($isWaTemplateRead)>
                <span class="template-builder-choice__dot"></span>
                <span>
                    <strong>Carousel Cards</strong>
                    <small>Send promotion with custom multiple image using carousel component</small>
                </span>
            </label>
        </article>

        <article class="template-builder-card">
            <h2>Template name and language</h2>
            <div class="template-builder-field">
                <label>Name your template</label>
                <input type="text" name="name" value="{{ $templateValue('name', 'campaign_whatsapp') }}" required @readonly($isWaTemplateRead)>
            </div>

            <div class="template-builder-field">
                <label>Category Template</label>
                <select name="category" required @disabled($isWaTemplateRead)>
                    <option value="Single Banner" @selected($templateValue('category', 'Single Banner') === 'Single Banner')>Single Banner</option>
                    <option value="Carousel" @selected($templateValue('category') === 'Carousel')>Carousel</option>
                </select>
            </div>

            <div class="template-builder-field">
                <label>Language</label>
                <select name="language" required @disabled($isWaTemplateRead)>
                    <option value="INDONESIA" @selected(in_array($templateValue('language', 'INDONESIA'), ['INDONESIA', 'Indonesia'], true))>INDONESIA</option>
                    <option value="ENGLISH" @selected(in_array($templateValue('language'), ['ENGLISH', 'English'], true))>ENGLISH</option>
                </select>
            </div>

            <div class="template-builder-field">
                <label>WABA Account ID</label>
                <input type="text" name="waba_account_id" value="{{ $templatePayloadValue('wabaAccountId') }}" placeholder="3735503976689341" @readonly($isWaTemplateRead)>
            </div>

            <div class="template-builder-field">
                <label>Phone Number ID</label>
                <input type="text" name="phone_number_id" value="{{ $templatePayloadValue('phoneNumberId') }}" placeholder="337795902761255" @readonly($isWaTemplateRead)>
            </div>
        </article>

        <article class="template-builder-card">
            <h2>Content</h2>
            <p>Fill in the header, body and footer section of your template</p>

            <div class="template-builder-field">
                <label>Header Type</label>
                <select name="header_type" @disabled($isWaTemplateRead)>
                    <option value="TEXT" @selected(in_array($templateValue('header_type', 'TEXT'), ['TEXT', 'none'], true))>TEXT</option>
                    <option value="IMAGE" @selected(in_array($templateValue('header_type'), ['IMAGE', 'image'], true))>IMAGE</option>
                    <option value="VIDEO" @selected(in_array($templateValue('header_type'), ['VIDEO', 'video'], true))>VIDEO</option>
                    <option value="DOCUMENT" @selected(in_array($templateValue('header_type'), ['DOCUMENT', 'document'], true))>DOCUMENT</option>
                </select>
            </div>

            <div class="template-builder-field">
                <label>Header Label</label>
                <input type="text" name="header_label" value="{{ $templatePayloadValue('headerLabel') }}" maxlength="255" placeholder="Test" @readonly($isWaTemplateRead)>
            </div>

            <div class="template-builder-field">
                <label>Asset</label>
                <p class="template-builder-field__hint">You can upload videos, images, PDFs, or locations.</p>
                <label class="template-builder-upload" for="templateAssetInput">
                    <input type="file" id="templateAssetInput" name="asset" class="template-builder-upload__input" accept="image/*,application/pdf,video/mp4" @disabled($isWaTemplateRead)>
                    <span class="template-builder-upload__icon">☁</span>
                    <strong id="templateAssetLabel">{{ $isWaTemplateRead && $templateRow->asset_path ? basename($templateRow->asset_path) : 'Upload an asset' }}</strong>
                    <small>(Ensure your asset meets the format and size requirement)</small>
                    <span class="template-builder-upload__link">Learn more about supported format assets</span>
                </label>
            </div>

            <div class="template-builder-editor">
                <div class="template-builder-editor__toolbar">
                    <select>
                        <option>Paragraph</option>
                    </select>
                    <button type="button">B</button>
                    <button type="button"><i>I</i></button>
                    <button type="button"><u>U</u></button>
                    <button type="button">S</button>
                    <button type="button">•</button>
                    <button type="button">1.</button>
                </div>
                <textarea id="templateBodyInput" name="body" maxlength="1024" placeholder="Write something awesome..." required @readonly($isWaTemplateRead)>{{ $templateValue('body') }}</textarea>
                <span class="template-builder-editor__count" id="templateBodyCount">0 / 1024</span>
            </div>

            <div class="template-builder-inline-actions">
                <button type="button" class="template-builder-ai">Creating body content with AI <span>Token quota: 0</span></button>
                <button type="button" class="template-builder-secondary">Add Body Variable</button>
            </div>

            <div class="template-builder-field">
                <label>Footer (optional)</label>
                <input type="text" name="footer" value="{{ $templateValue('footer') }}" maxlength="60" @readonly($isWaTemplateRead)>
            </div>
        </article>

        <article class="template-builder-card">
            <h2>Buttons</h2>
            <button type="button" class="template-builder-add-button" id="templateAddButton" @disabled($isWaTemplateRead)>
                <span>+</span>
                <span>Add Button</span>
            </button>
            <p class="template-builder-button-warning" id="templateButtonWarning" hidden>Button hanya bisa 2.</p>

            <div class="template-builder-button-list" id="templateButtonList"></div>

            <div class="template-builder-tip">
                <strong>We recomend adding the marketing op-out button</strong>
                <p>Allow customers to request to opout all marketing messages. this can help reduce blocks from customers and increase your quality rating</p>
            </div>

            <div class="template-builder-inline-actions">
                <a href="{{ route('campaign-template.index') }}" class="template-builder-secondary">Cancel</a>
                @unless ($isWaTemplateRead)
                    <button type="submit" class="template-builder-add-button">
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
                    <img src="{{ $isWaTemplateRead && $templateRow->asset_path ? asset('storage/' . $templateRow->asset_path) : asset('assets/logo.png') }}" alt="Preview asset" class="template-builder-phone__image" id="templatePreviewImage">
                    <div class="template-builder-phone__body" id="templatePreviewBody">
                        Halo Pelanggan Setia ComboFit, kamu mendapatkan akses Exercise Plan GRATIS selama 30 hari 🔥

                        ✅ Akses ke program latihan fisik oleh Coach profesional
                        ✅ Panduan video latihan yang mudah diikuti
                        ✅ Tips dan trik kebugaran

                        Aktifkan akunmu di link ini
                        https://bit.ly/Fita_Combofit

                        #SehatMakinNikmat bersama Fita!
                    </div>
                    <div class="template-builder-phone__actions" id="templatePreviewActions">
                        <div class="template-builder-phone__cta">Coba Sekarang</div>
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
@endif
