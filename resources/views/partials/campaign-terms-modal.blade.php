<style>
    .campaign-terms-modal {
        position: fixed;
        inset: 0;
        z-index: 1200;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(9, 30, 66, .48);
    }

    .campaign-terms-modal.is-open {
        display: flex;
    }

    .campaign-terms-modal__dialog {
        width: min(720px, 100%);
        max-height: min(82vh, 760px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 24px 72px rgba(9, 30, 66, .24);
    }

    .campaign-terms-modal__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 24px;
        border-bottom: 1px solid #e3ebf6;
    }

    .campaign-terms-modal__title {
        margin: 0;
        color: #071d3a;
        font-size: 20px;
        font-weight: 800;
    }

    .campaign-terms-modal__close {
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 8px;
        background: #eef4fb;
        color: #071d3a;
        font-size: 24px;
        line-height: 1;
        cursor: pointer;
    }

    .campaign-terms-modal__body {
        overflow: auto;
        padding: 22px 24px 28px;
        color: #3c5068;
        font-size: 14px;
        line-height: 1.68;
    }

    .campaign-terms-modal__body p {
        margin: 0 0 12px;
    }

    .campaign-terms-modal__body .campaign-terms-modal__section {
        margin-top: 18px;
        color: #071d3a;
        font-size: 16px;
        font-weight: 800;
    }

    .campaign-terms-modal__loading,
    .campaign-terms-modal__error {
        margin: 0;
        color: #5f7188;
    }

    .campaign-terms-modal__error {
        color: #d71920;
    }

    @media (max-width: 640px) {
        .campaign-terms-modal {
            align-items: flex-end;
            padding: 0;
        }

        .campaign-terms-modal__dialog {
            width: 100%;
            max-height: 88vh;
            border-radius: 14px 14px 0 0;
        }

        .campaign-terms-modal__header {
            padding: 16px 18px;
        }

        .campaign-terms-modal__body {
            padding: 18px;
        }
    }
</style>

<div class="campaign-terms-modal" id="campaignTermsModal" aria-hidden="true">
    <div class="campaign-terms-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="campaignTermsModalTitle">
        <div class="campaign-terms-modal__header">
            <h2 class="campaign-terms-modal__title" id="campaignTermsModalTitle">Syarat & Ketentuan</h2>
            <button type="button" class="campaign-terms-modal__close" data-campaign-terms-close aria-label="Tutup">&times;</button>
        </div>
        <div class="campaign-terms-modal__body" id="campaignTermsModalBody">
            <p class="campaign-terms-modal__loading">Memuat syarat dan ketentuan...</p>
        </div>
    </div>
</div>

<script>
    (() => {
        if (window.campaignTermsModalInitialized) {
            return;
        }

        window.campaignTermsModalInitialized = true;

        const termsUrl = @json(route('campaign.terms'));
        const modal = document.getElementById('campaignTermsModal');
        const body = document.getElementById('campaignTermsModalBody');
        const title = document.getElementById('campaignTermsModalTitle');
        let cachedTerms = null;

        const openModal = () => {
            if (!modal) {
                return;
            }

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        };

        const closeModal = () => {
            if (!modal) {
                return;
            }

            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };

        const renderTerms = (payload) => {
            if (!body) {
                return;
            }

            body.innerHTML = '';
            if (title && payload?.title) {
                title.textContent = payload.title;
            }

            const paragraphs = Array.isArray(payload?.paragraphs) ? payload.paragraphs : [];
            if (paragraphs.length === 0) {
                const empty = document.createElement('p');
                empty.className = 'campaign-terms-modal__error';
                empty.textContent = 'Isi syarat dan ketentuan belum tersedia.';
                body.appendChild(empty);
                return;
            }

            paragraphs.forEach((text) => {
                const item = document.createElement('p');
                item.textContent = text;
                if (/^[A-Z]\.\s/.test(text)) {
                    item.className = 'campaign-terms-modal__section';
                }
                body.appendChild(item);
            });
        };

        const loadTerms = async () => {
            if (cachedTerms) {
                renderTerms(cachedTerms);
                return;
            }

            if (body) {
                body.innerHTML = '<p class="campaign-terms-modal__loading">Memuat syarat dan ketentuan...</p>';
            }

            try {
                const response = await fetch(termsUrl, { headers: { Accept: 'application/json' } });
                const payload = await response.json();

                if (!response.ok || payload?.success !== true) {
                    throw new Error(payload?.message || 'Gagal memuat syarat dan ketentuan.');
                }

                cachedTerms = payload;
                renderTerms(payload);
            } catch (error) {
                if (body) {
                    body.innerHTML = '';
                    const message = document.createElement('p');
                    message.className = 'campaign-terms-modal__error';
                    message.textContent = error?.message || 'Gagal memuat syarat dan ketentuan.';
                    body.appendChild(message);
                }
            }
        };

        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-campaign-terms-trigger]');
            if (trigger) {
                event.preventDefault();
                event.stopPropagation();
                openModal();
                loadTerms();
                return;
            }

            if (event.target === modal || event.target.closest('[data-campaign-terms-close]')) {
                event.preventDefault();
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    })();
</script>
