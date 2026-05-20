<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'flowLabel' => 'WA Business',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'flowLabel' => 'WA Business',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="portal-category-modal waba-template-suggestion-modal" id="wabaTemplateSuggestionModal" hidden aria-hidden="true">
    <div class="portal-category-modal__backdrop" data-waba-template-suggestion-close></div>
    <section class="portal-category-modal__card waba-template-suggestion-modal__card" role="dialog" aria-modal="true" aria-labelledby="wabaTemplateSuggestionTitle">
        <div class="portal-category-modal__head">
            <div>
                <p class="portal-category-modal__eyebrow">Sebelum membuat iklan</p>
                <h2 id="wabaTemplateSuggestionTitle">Buat WABA Template terlebih dahulu?</h2>
            </div>
            <button type="button" class="portal-category-modal__close" data-waba-template-suggestion-close aria-label="Tutup popup template">&times;</button>
        </div>

        <div class="waba-template-suggestion-modal__body">
            <p>
                Iklan <?php echo e($flowLabel); ?> membutuhkan template pesan yang sudah tersedia. Anda bisa membuat template baru terlebih dahulu, atau lanjut jika template sudah siap.
            </p>
            <div class="waba-template-suggestion-modal__actions">
                <a href="<?php echo e(route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'campaign-template', 'view' => 'create'])); ?>" class="campaign-modal-btn campaign-modal-btn--primary">
                    Buat WABA Template
                </a>
                <button type="button" class="campaign-modal-btn campaign-modal-btn--ghost" data-waba-template-suggestion-close>
                    Lanjut Buat Iklan
                </button>
            </div>
        </div>
    </section>
</div>

<script>
    (() => {
        const modal = document.getElementById('wabaTemplateSuggestionModal');
        if (!modal) {
            return;
        }

        const closeModal = () => {
            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
        };

        const openModal = () => {
            modal.hidden = false;
            modal.setAttribute('aria-hidden', 'false');
        };

        modal.querySelectorAll('[data-waba-template-suggestion-close]').forEach((button) => {
            button.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', openModal, { once: true });
        } else {
            window.requestAnimationFrame(openModal);
        }
    })();
</script>
<?php /**PATH D:\1 Kerja\api-project\resources\views/partials/waba-template-suggestion-modal.blade.php ENDPATH**/ ?>