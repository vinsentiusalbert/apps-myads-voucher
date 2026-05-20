<style>
    .campaign-payment-modal[hidden] {
        display: none;
    }

    .campaign-payment-modal {
        position: fixed;
        inset: 0;
        z-index: 1300;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .campaign-payment-modal__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(9, 30, 66, .5);
    }

    .campaign-payment-modal__dialog {
        position: relative;
        width: min(460px, 100%);
        max-height: 88vh;
        overflow: auto;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 24px 72px rgba(9, 30, 66, .24);
    }

    .campaign-payment-modal__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 24px 8px;
    }

    .campaign-payment-modal__header h2 {
        margin: 0;
        color: #071d3a;
        font-size: 22px;
        font-weight: 800;
    }

    .campaign-payment-modal__close {
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

    .campaign-payment-modal__body {
        margin: 0 24px;
        padding-top: 12px;
    }

    .campaign-payment-modal__qr {
        display: block;
        margin-top: 12px;
    }

    .campaign-payment-modal__qr img {
        width: min(280px, 100%);
        height: auto;
        display: block;
        margin: 12px auto 0;
    }

    .campaign-payment-modal__actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 14px;
    }

    .campaign-payment-modal__download {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: 8px 16px;
        border: 1px solid #ff1f3d;
        border-radius: 8px;
        color: #ff1f3d;
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
    }

    .campaign-payment-modal__download[hidden] {
        display: none;
    }

    .campaign-payment-modal__footnote {
        margin: 18px 24px 24px;
    }

    @media (max-width: 640px) {
        .campaign-payment-modal {
            align-items: flex-end;
            padding: 0;
        }

        .campaign-payment-modal__dialog {
            width: 100%;
            max-height: 90vh;
            border-radius: 14px 14px 0 0;
        }

        .campaign-payment-modal__header {
            padding: 16px 18px 8px;
        }

        .campaign-payment-modal__body {
            margin: 0 18px;
        }

        .campaign-payment-modal__footnote {
            margin: 18px 18px 22px;
        }
    }
</style>

<script>
    (() => {
        if (window.paymentQrisModalInitialized) {
            return;
        }

        window.paymentQrisModalInitialized = true;
        const qrisDownloadUrl = @json(route('campaign.payment.qris-download'));

        const closeModal = (modal) => {
            if (!modal) {
                return;
            }
            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };

        const triggerFileDownload = (href, filename) => {
            const link = document.createElement('a');
            link.href = href;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            link.remove();
        };

        const downloadRenderedQrImage = (downloadTrigger, filename) => {
            const modal = downloadTrigger.closest('.campaign-payment-modal');
            const image = modal?.querySelector('.campaign-payment-modal__qr img');
            const imageUrl = image?.currentSrc || image?.src || '';

            if (!imageUrl) {
                return false;
            }

            if (imageUrl.startsWith('data:') || imageUrl.startsWith('blob:')) {
                triggerFileDownload(imageUrl, filename);
                return true;
            }

            try {
                const canvas = document.createElement('canvas');
                canvas.width = image.naturalWidth || image.width;
                canvas.height = image.naturalHeight || image.height;
                if (!canvas.width || !canvas.height) {
                    return false;
                }

                const context = canvas.getContext('2d');
                context.drawImage(image, 0, 0, canvas.width, canvas.height);
                triggerFileDownload(canvas.toDataURL('image/png'), filename);
                return true;
            } catch (error) {
                return false;
            }
        };

        document.addEventListener('click', (event) => {
            const downloadTrigger = event.target.closest('.campaign-payment-modal__download');
            if (downloadTrigger) {
                event.preventDefault();

                const url = downloadTrigger.getAttribute('href') || '';
                if (!url || url === '#') {
                    window.alert('QRIS belum tersedia untuk di-download.');
                    return;
                }

                const filename = downloadTrigger.getAttribute('download') || 'qris.png';
                if (downloadRenderedQrImage(downloadTrigger, filename)) {
                    return;
                }

                const downloadUrl = new URL(qrisDownloadUrl, window.location.origin);
                downloadUrl.searchParams.set('url', url);
                downloadUrl.searchParams.set('filename', filename);

                downloadTrigger.setAttribute('aria-busy', 'true');

                fetch(downloadUrl.toString(), { headers: { Accept: 'image/*' } })
                    .then(async (response) => {
                        if (!response.ok) {
                            throw new Error('Gagal download QRIS. QRIS tidak tersedia atau link sudah kedaluwarsa.');
                        }

                        const contentType = response.headers.get('Content-Type') || '';
                        if (!contentType.toLowerCase().startsWith('image/') && !contentType.toLowerCase().includes('application/octet-stream')) {
                            throw new Error('File QRIS yang diterima bukan gambar.');
                        }

                        return response.blob();
                    })
                    .then((blob) => {
                        const objectUrl = URL.createObjectURL(blob);
                        triggerFileDownload(objectUrl, filename);
                        URL.revokeObjectURL(objectUrl);
                    })
                    .catch((error) => {
                        window.alert(error?.message || 'Gagal download QRIS.');
                    })
                    .finally(() => {
                        downloadTrigger.removeAttribute('aria-busy');
                    });

                return;
            }

            const closeTrigger = event.target.closest('[data-payment-modal-close]');
            if (!closeTrigger) {
                return;
            }

            event.preventDefault();
            closeModal(closeTrigger.closest('.campaign-payment-modal'));
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            document.querySelectorAll('.campaign-payment-modal:not([hidden])').forEach(closeModal);
        });
    })();
</script>
