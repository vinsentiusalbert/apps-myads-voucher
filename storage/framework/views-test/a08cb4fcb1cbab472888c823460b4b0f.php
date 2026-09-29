<?php
    $templateValue = fn (string $field, mixed $default = null) => old($field, $isWaTemplateRead ? $templateRow->{$field} : $default);
    $templatePayloadValue = fn (string $field, mixed $default = null) => old($field, $isWaTemplateRead ? data_get($templateRow->gw_payload ?? [], $field, $default) : $default);
    $previewButtons = collect(data_get($templateRow ?? null, 'buttons', data_get($templateRow ?? null, 'gw_payload.buttons', [])))
        ->filter(fn ($button) => is_array($button) && trim((string) ($button['label'] ?? '')) !== '')
        ->values();
    $previewHeaderType = strtoupper((string) $templateValue('header_type', 'TEXT'));
    $previewHeaderLabel = trim((string) $templatePayloadValue('headerLabel', $templateValue('name', '')));
?>

<?php if($errors->any()): ?>
    <section class="template-library-card">
        <strong>Template belum bisa disimpan.</strong>
        <ul>
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </section>
<?php endif; ?>

<?php if($isWaTemplateRead): ?>
    <div class="template-builder-layout">
<?php else: ?>
    <form method="POST" action="<?php echo e(route('campaign-template.store')); ?>" enctype="multipart/form-data" class="template-builder-layout" id="templateCreateForm">
        <?php echo csrf_field(); ?>
<?php endif; ?>
    <div class="template-builder-main">
        <article class="template-builder-card">
            <h2>Template name and language</h2>
            <?php if (! ($isWaTemplateRead)): ?>
                <input type="hidden" name="template_type" value="<?php echo e($templateValue('template_type', 'simple_message')); ?>">
            <?php endif; ?>
            <div class="template-builder-field">
                <label>Name your template</label>
                <input type="text" name="name" value="" required <?php if($isWaTemplateRead): echo 'readonly'; endif; ?>>
            </div>

            <?php if (! ($isWaTemplateRead)): ?>
                <input type="hidden" name="category" value="<?php echo e($templateValue('category', 'Single Banner')); ?>">
            <?php else: ?>
                <div class="template-builder-field">
                    <label>Category Template</label>
                    <select name="category" required disabled>
                        <option value="Single Banner" <?php if($templateValue('category', 'Single Banner') === 'Single Banner'): echo 'selected'; endif; ?>>Single Banner</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="template-builder-field">
                <label>Language</label>
                <select name="language" required <?php if($isWaTemplateRead): echo 'disabled'; endif; ?>>
                    <option value="INDONESIA" <?php if(in_array($templateValue('language', 'INDONESIA'), ['INDONESIA', 'Indonesia'], true)): echo 'selected'; endif; ?>>INDONESIA</option>
                    <option value="ENGLISH" <?php if(in_array($templateValue('language'), ['ENGLISH', 'English'], true)): echo 'selected'; endif; ?>>ENGLISH</option>
                </select>
            </div>

            <?php if (! ($isWaTemplateRead)): ?>
                <div class="template-builder-field" data-waba-display-name-field data-list-url="<?php echo e(route('campaign-template.display-name-list')); ?>">
                    <label>Display Name</label>
                    <select id="wabaDisplayNameSelect" name="display_name" required>
                        <option value="">Memuat display name...</option>
                    </select>
                    <input type="hidden" id="wabaAccountIdInput" name="waba_account_id" value="<?php echo e(old('waba_account_id')); ?>">
                    <input type="hidden" id="phoneNumberIdInput" name="phone_number_id" value="<?php echo e(old('phone_number_id')); ?>">
                </div>
            <?php else: ?>
                <div class="template-builder-field">
                    <label>Display Name</label>
                    <input type="text" value="<?php echo e($templatePayloadValue('verifiedName', $templatePayloadValue('displayName'))); ?>" readonly>
                </div>
            <?php endif; ?>
        </article>

        <article class="template-builder-card">
            <h2>Content</h2>
            <p>Fill in the header, body and footer section of your template</p>

            <div class="template-builder-field">
                <label>Header Type</label>
                <select name="header_type" id="templateHeaderTypeSelect" <?php if($isWaTemplateRead): echo 'disabled'; endif; ?>>
                    <option value="none" <?php if(in_array($templateValue('header_type', 'none'), ['none', ''], true)): echo 'selected'; endif; ?>>Tidak menggunakan Header</option>
                    <option value="TEXT" <?php if($templateValue('header_type') === 'TEXT'): echo 'selected'; endif; ?>>Teks</option>
                    <option value="IMAGE" <?php if(in_array($templateValue('header_type'), ['IMAGE', 'image'], true)): echo 'selected'; endif; ?>>Gambar</option>
                </select>
            </div>

            <div class="template-builder-field" data-template-header-label-field @hidden($templateValue('header_type') !== 'TEXT')>
                <label>Header Text</label>
                <input type="text" name="header_label" value="<?php echo e($templatePayloadValue('headerLabel')); ?>" maxlength="60" placeholder="Teks header" data-template-header-label-input <?php if($isWaTemplateRead): echo 'readonly'; endif; ?>>
                <span class="template-builder-button-card__meta" data-template-header-label-count>0/60 Karakter</span>
            </div>

            <div class="template-builder-field" data-template-asset-field @hidden(! in_array($templateValue('header_type'), ['IMAGE', 'image'], true))>
                <label>Asset</label>
                <p class="template-builder-field__hint">Upload gambar untuk header template.</p>
                <label class="template-builder-upload" for="templateAssetInput">
                    <input type="file" id="templateAssetInput" name="asset" class="template-builder-upload__input" accept="image/*" <?php if($isWaTemplateRead): echo 'disabled'; endif; ?>>
                    <span class="template-builder-upload__icon">Upload</span>
                    <strong id="templateAssetLabel"><?php echo e($isWaTemplateRead && $templateRow->asset_path ? basename($templateRow->asset_path) : 'Upload an asset'); ?></strong>
                    <small>(Ensure your asset meets the format and size requirement)</small>
                    <span class="template-builder-upload__link">Learn more about supported format assets</span>
                </label>
            </div>
            <div class="template-builder-field">
                <label>Body</label>
                <div class="template-builder-editor">
                    <div class="template-builder-editor__toolbar">
                        <select>
                            <option>Paragraph</option>
                        </select>
                        <button type="button">B</button>
                        <button type="button"><i>I</i></button>
                        <button type="button"><u>U</u></button>
                        <button type="button">S</button>
                        <button type="button">-</button>
                        <button type="button">1.</button>
                    </div>
                    <textarea id="templateBodyInput" name="body" maxlength="550" placeholder="Isi Pesan" required <?php if($isWaTemplateRead): echo 'readonly'; endif; ?>><?php echo e($templateValue('body')); ?></textarea>
                    <span class="template-builder-editor__count" id="templateBodyCount">0/550 Karakter</span>
                </div>
            </div>

        </article>

        <article class="template-builder-card">
            <h2>Buttons</h2>
            <button type="button" class="template-builder-add-button" id="templateAddButton" <?php if($isWaTemplateRead): echo 'disabled'; endif; ?>>
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
                <a href="<?php echo e(route('campaign-template.index')); ?>" class="template-builder-secondary">Cancel</a>
                <?php if (! ($isWaTemplateRead)): ?>
                    <button type="submit" class="template-builder-add-button" id="templateSaveButton">
                        <span>Save Template</span>
                    </button>
                <?php endif; ?>
            </div>
        </article>
    </div>

    <aside class="template-builder-preview">
        <article class="template-builder-preview__card">
            <h2>Template Preview</h2>
            <div class="template-builder-phone">
                <div class="template-builder-phone__message">
                    <strong class="template-builder-phone__header-text" id="templatePreviewHeaderText" @hidden($previewHeaderType !== 'TEXT' || $previewHeaderLabel === '')><?php echo e($previewHeaderLabel); ?></strong>
                    <img src="<?php echo e($isWaTemplateRead && $templateRow->asset_path ? asset('storage/' . $templateRow->asset_path) : asset('assets/logo.png')); ?>" alt="Preview asset" class="template-builder-phone__image" id="templatePreviewImage" @hidden($previewHeaderType !== 'IMAGE')>
                    <div class="template-builder-phone__body" id="templatePreviewBody"><?php echo e($templateValue('body')); ?></div>
                    <div class="template-builder-phone__actions" id="templatePreviewActions">
                        <?php $__currentLoopData = $previewButtons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $button): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="template-builder-phone__cta"><?php echo e($button['label']); ?></div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
            <div class="template-builder-preview__foot">
                <strong>This template is good for:</strong>
                <p>Welcome messages, promotions, offers, coupons, newsletters, announcements</p>
            </div>
        </article>
    </aside>
<?php if($isWaTemplateRead): ?>
    </div>
<?php else: ?>
    </form>
    <div class="template-submit-loader" id="templateSubmitLoader" role="status" aria-live="polite" hidden>
        <div class="template-submit-loader__card">
            <span class="portal-page-loader__spinner" aria-hidden="true"></span>
            <strong>Mengunggah dan menyimpan template...</strong>
            <small>Mohon tunggu, jangan tutup halaman ini.</small>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH D:\1 Kerja\shop-myads\resources\views/campaign-wa-template/partials/builder-form.blade.php ENDPATH**/ ?>