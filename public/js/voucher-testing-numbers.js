(() => {
    const list = document.getElementById('voucherTestingNumbers');
    const template = document.getElementById('voucherTestingNumberTemplate');
    const addButton = document.getElementById('voucherAddTestingNumber');
    const input = document.getElementById('voucherTestingNumberInput');
    const empty = document.getElementById('voucherTestingNumbersEmpty');
    if (!list || !template || !addButton || !input) return;

    const sync = () => {
        input.required = list.children.length === 0;
        empty.hidden = list.children.length > 0;
    };

    const addNumber = () => {
        input.setCustomValidity('');
        if (!input.value.trim()) {
            input.setCustomValidity('Masukkan nomor testing mulai dari 8.');
            input.reportValidity();
            return false;
        }
        if (!input.reportValidity()) return false;
        const number = input.value.trim();
        const exists = [...list.querySelectorAll('input')].some(field => field.value === number);
        if (!exists) {
            const row = template.content.firstElementChild.cloneNode(true);
            row.querySelector('input').value = number;
            row.querySelector('[data-testing-number-label]').textContent = `62${number}`;
            row.querySelector('button').setAttribute('aria-label', `Hapus nomor testing 62${number}`);
            list.appendChild(row);
        }
        input.value = '';
        sync();
        input.focus();
        return true;
    };

    addButton.addEventListener('click', addNumber);
    input.addEventListener('input', () => input.setCustomValidity(''));
    input.addEventListener('keydown', event => {
        if (event.key !== 'Enter') return;
        event.preventDefault();
        addNumber();
    });
    list.addEventListener('click', event => {
        const remove = event.target.closest('[data-remove-testing-number]');
        if (!remove) return;
        remove.closest('[data-testing-number]').remove();
        input.setCustomValidity('');
        sync();
        input.focus();
    });
    input.addEventListener('paste', event => {
        const number = event.clipboardData?.getData('text').trim().replace(/^(?:\+?62|0)/, '');
        if (!/^8[0-9]{8,11}$/.test(number || '')) return;
        event.preventDefault();
        input.value = number;
        input.setCustomValidity('');
    });
    document.getElementById('voucherCampaignForm').addEventListener('submit', event => {
        if (input.value.trim()) {
            if (!addNumber()) event.preventDefault();
        } else if (!list.children.length) {
            event.preventDefault();
            addNumber();
        }
    });
    sync();
})();
