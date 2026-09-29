<script>
    (() => {
        const root = document.documentElement;
        const toggleButtons = document.querySelectorAll('[data-menu-toggle]');
        const closeButtons = document.querySelectorAll('[data-menu-close]');

        const setMenuState = (isOpen) => {
            root.classList.toggle('portal-menu-open', isOpen);
            toggleButtons.forEach((button) => {
                button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        };

        setMenuState(false);

        toggleButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const nextState = !root.classList.contains('portal-menu-open');
                setMenuState(nextState);
            });
        });

        closeButtons.forEach((button) => {
            button.addEventListener('click', () => setMenuState(false));
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setMenuState(false);
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 1080) {
                setMenuState(false);
            }
        });

        document.querySelectorAll('[data-nav-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const parent = button.closest('[data-nav-group]');
                const isOpen = parent?.classList.toggle('portal-nav__item--open');
                button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        });
    })();
</script>
<?php /**PATH D:\1 Kerja\shop-myads\resources\views/partials/portal-menu-script.blade.php ENDPATH**/ ?>