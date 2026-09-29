<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo e($title ?? 'ShopAds Portal'); ?></title>
        <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('assets/goto-icon.svg')); ?>">
        <link rel="apple-touch-icon" href="<?php echo e(asset('assets/goto-icon.svg')); ?>">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:500,700|plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        <link rel="stylesheet" href="<?php echo e(asset('css/auth-portal.css')); ?>?v=<?php echo e(filemtime(public_path('css/auth-portal.css'))); ?>">
        <?php echo $__env->yieldPushContent('styles'); ?>
    </head>
    <body class="<?php echo e($bodyClass ?? ''); ?>">
        <div class="portal-page-loader" id="portalPageLoader" role="status" aria-live="polite">
            <div class="portal-page-loader__card">
                <span class="portal-page-loader__spinner" aria-hidden="true"></span>
                <strong>Memuat halaman...</strong>
            </div>
        </div>

        <main class="portal-shell">
            <button type="button" class="portal-overlay" data-menu-close aria-label="Tutup menu"></button>

            <?php
                $processedNavMenus = $navMenus ?? [];
                if (session('myads.user.role') === 'admin') {
                    $hasAdmin = false;
                    foreach ($processedNavMenus as $menu) {
                        if ($menu['key'] === 'admin') {
                            $hasAdmin = true;
                            break;
                        }
                    }
                    if (!$hasAdmin) {
                        $processedNavMenus[] = [
                            'key' => 'admin',
                            'label' => 'Admin Panel',
                            'icon' => 'dashboard',
                            'href' => route('admin.dashboard'),
                            'interactive' => true,
                        ];
                    }
                }
            ?>

            <?php echo $__env->make('partials.portal-sidebar', [
                'navMenus' => $processedNavMenus,
                'activeNav' => $activeNav ?? null,
                'activeSubnav' => $activeSubnav ?? null,
                'createButtonLabel' => $createButtonLabel ?? 'Buat Iklan',
                'createButtonHref' => $createButtonHref ?? null,
                'sidebarMembership' => session('myads.membership', ['is_member' => false]),
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <section class="portal-main <?php echo e($mainClass ?? ''); ?>">
                <?php echo $__env->make('partials.portal-topbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <div class="portal-content <?php echo e($contentClass ?? ''); ?>">
                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            </section>
        </main>

        <?php echo $__env->yieldContent('after_shell'); ?>

        <?php echo $__env->make('partials.portal-campaign-category-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('partials.portal-menu-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <script>
            (() => {
                const loader = document.getElementById('portalPageLoader');
                if (!loader) {
                    return;
                }

                const hideLoader = () => {
                    loader.classList.add('portal-page-loader--hidden');
                    window.setTimeout(() => loader.remove(), 250);
                };

                if (document.readyState === 'complete') {
                    hideLoader();
                } else {
                    window.addEventListener('load', hideLoader, { once: true });
                    window.setTimeout(hideLoader, 3500);
                }
            })();
        </script>
        <?php echo $__env->yieldPushContent('scripts'); ?>
        <?php if(config('app.debug')): ?>
            <?php echo $__env->make('partials.api-debug-console', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
    </body>
</html>
<?php /**PATH D:\1 Kerja\shop-myads\resources\views/layouts/portal.blade.php ENDPATH**/ ?>