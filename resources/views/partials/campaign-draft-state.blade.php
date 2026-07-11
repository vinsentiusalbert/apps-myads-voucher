@php
    $storageKey = (string) ($storageKey ?? ('campaign-draft:' . md5(request()->path())));
@endphp

<script>
    (() => {
        const storageKey = @json($storageKey);
        const ignoredNames = new Set(['_token', 'payment_transaction_id']);
        const ignoredIds = new Set([
            'gwPaymentTransactionId',
            'waLbaPaymentTransactionId',
            'waTargetedPaymentTransactionId',
            'campaignTermsCheck',
            'waLbaTermsCheck',
            'waTargetedTermsCheck',
        ]);

        const getFields = () => Array.from(document.querySelectorAll('input, select, textarea'))
            .filter((field) => {
                if (!field.id && !field.name) return false;
                if (field.disabled) return false;
                if (ignoredNames.has(field.name)) return false;
                if (ignoredIds.has(field.id)) return false;
                if (field.matches('[data-schedule-time-start-hour], [data-schedule-time-start-minute], [data-schedule-time-end-hour], [data-schedule-time-end-minute], [data-wa-targeted-schedule-time-start-hour], [data-wa-targeted-schedule-time-start-minute], [data-wa-targeted-schedule-time-end-hour], [data-wa-targeted-schedule-time-end-minute]')) return false;
                if (['button', 'submit', 'reset', 'file', 'password'].includes(String(field.type || '').toLowerCase())) return false;
                return true;
            });

        const fieldKey = (field) => field.id ? `id:${field.id}` : `name:${field.name}`;

        const readField = (field) => {
            if (field.type === 'checkbox' || field.type === 'radio') {
                return { checked: field.checked };
            }

            if (field.tagName === 'SELECT' && field.multiple) {
                return { value: Array.from(field.selectedOptions).map((option) => option.value) };
            }

            return { value: field.value };
        };

        const writeField = (field, saved) => {
            if (!saved || typeof saved !== 'object') return false;

            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = Boolean(saved.checked);
                return true;
            }

            if (field.tagName === 'SELECT' && field.multiple && Array.isArray(saved.value)) {
                const selectedValues = new Set(saved.value.map(String));
                Array.from(field.options).forEach((option) => {
                    option.selected = selectedValues.has(String(option.value));
                });
                return true;
            }

            if ('value' in saved) {
                field.value = saved.value ?? '';
                return true;
            }

            return false;
        };

        const saveDraft = () => {
            try {
                const controls = {};
                getFields().forEach((field) => {
                    controls[fieldKey(field)] = readField(field);
                });

                localStorage.setItem(storageKey, JSON.stringify({
                    controls,
                    savedAt: Date.now(),
                }));
            } catch (error) {
                // localStorage can be blocked in private or restricted browser modes.
            }
        };

        const restoreDraft = () => {
            let draft = null;

            try {
                draft = JSON.parse(localStorage.getItem(storageKey) || 'null');
            } catch (error) {
                return;
            }

            if (!draft || typeof draft !== 'object' || !draft.controls) {
                return;
            }

            const restoredFields = [];
            getFields().forEach((field) => {
                const saved = draft.controls[fieldKey(field)];
                if (writeField(field, saved)) {
                    restoredFields.push(field);
                }
            });

            restoredFields.forEach((field) => {
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
            });

            document.dispatchEvent(new CustomEvent('campaign:draft-restored', {
                detail: { storageKey },
            }));

            saveDraft();
        };

        const clearDraft = () => {
            try {
                localStorage.removeItem(storageKey);
            } catch (error) {
                // ignore storage failures
            }
        };

        const patchFinalSubmit = () => {
            document.querySelectorAll('form').forEach((form) => {
                if (!form.id || !/GwSubmitForm$/.test(form.id)) return;
                const nativeSubmit = form.submit.bind(form);

                form.submit = () => {
                    clearDraft();
                    nativeSubmit();
                };

                form.addEventListener('submit', clearDraft);
            });
        };

        document.addEventListener('DOMContentLoaded', () => {
            restoreDraft();
            window.setTimeout(restoreDraft, 250);
            window.setTimeout(restoreDraft, 1000);
            window.setTimeout(restoreDraft, 2500);
            patchFinalSubmit();

            document.addEventListener('input', (event) => {
                if (event.target?.matches?.('input, select, textarea')) {
                    saveDraft();
                }
            });
            document.addEventListener('change', (event) => {
                if (event.target?.matches?.('input, select, textarea')) {
                    saveDraft();
                }
            });
            document.addEventListener('click', () => {
                window.setTimeout(saveDraft, 0);
            });
        });
    })();
</script>
