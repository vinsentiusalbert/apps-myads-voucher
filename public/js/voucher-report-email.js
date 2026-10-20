(() => {
    const modal = document.getElementById('voucherReportEmailModal');
    if (!modal) return;
    const open = () => {
        if (modal.open) return;
        modal.showModal();
        modal.querySelector('#report_email').focus();
    };
    document.querySelectorAll('[data-open-report-email]').forEach(button => button.addEventListener('click', open));
    modal.querySelectorAll('[data-close-report-email]').forEach(button => {
        button.addEventListener('click', () => modal.close());
    });
    modal.addEventListener('click', event => {
        const bounds = modal.getBoundingClientRect();
        if (event.target === modal && (event.clientX < bounds.left || event.clientX > bounds.right
            || event.clientY < bounds.top || event.clientY > bounds.bottom)) modal.close();
    });
    if (modal.dataset.autoOpen === 'true') open();
})();
