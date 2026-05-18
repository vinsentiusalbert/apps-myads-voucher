@props([
    'flowLabel' => 'WA Business',
])

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
                Iklan {{ $flowLabel }} membutuhkan template pesan yang sudah tersedia. Anda bisa membuat template baru terlebih dahulu, atau lanjut jika template sudah siap.
            </p>
            <div class="waba-template-suggestion-modal__actions">
                <a href="{{ route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'campaign-template', 'view' => 'create']) }}" class="campaign-modal-btn campaign-modal-btn--primary">
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
