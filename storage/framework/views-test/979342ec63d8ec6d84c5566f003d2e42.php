<header class="portal-topbar">
    <div class="portal-topbar__left">
        <button type="button" class="portal-burger" data-menu-toggle aria-label="Buka menu" aria-controls="mobile-menu" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <div class="portal-topbar__spacer"></div>
    </div>
    <div class="portal-topbar__actions">
        <div class="portal-user">
            <span class="portal-user__avatar"><?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?></span>
            <span class="portal-user__name"><?php echo e(auth()->user()->name); ?></span>
        </div>
        <form method="POST" action="<?php echo e(route('logout')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit" class="portal-logout">
                <span class="portal-logout__icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M14 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M10 17l5-5-5-5M15 12H4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>Logout</span>
            </button>
        </form>
    </div>
</header>
<?php /**PATH D:\1 Kerja\api-project\resources\views/partials/portal-topbar.blade.php ENDPATH**/ ?>