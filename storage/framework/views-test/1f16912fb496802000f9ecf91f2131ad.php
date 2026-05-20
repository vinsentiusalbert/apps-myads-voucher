<aside class="portal-sidebar" id="mobile-menu">
    <div class="portal-sidebar__bg"></div>

    <div class="portal-sidebar__inner">
        <div class="portal-sidebar__mobile-head">
            <p class="portal-sidebar__mobile-title">Menu</p>
            <button type="button" class="portal-sidebar__close" data-menu-close aria-label="Tutup menu">
                <span></span>
                <span></span>
            </button>
        </div>

        <div class="portal-brand">
            <img src="<?php echo e(asset('assets/logo.png')); ?>" alt="MyAds Logo" class="portal-brand__logo">
            <p class="portal-brand__text">MyAds</p>
        </div>

        <?php if(! empty($createButtonHref)): ?>
            <a href="<?php echo e($createButtonHref); ?>" class="portal-create-btn portal-create-btn--link"><?php echo e($createButtonLabel); ?></a>
        <?php else: ?>
            <a
                href="<?php echo e(route('campaign.menu', ['channel' => 'sms', 'menu' => 'location-based-area', 'view' => 'create'])); ?>"
                class="portal-create-btn portal-create-btn--link"
                data-campaign-category-open
            ><?php echo e($createButtonLabel); ?></a>
        <?php endif; ?>

        <nav class="portal-nav" aria-label="Main navigation">
            <?php $__currentLoopData = $navMenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $isActive = ($activeNav ?? null) === $menu['key'];
                    $hasChildren = ! empty($menu['children']);
                    $isSelected = false;

                    if ($hasChildren) {
                        $isSelected = collect($menu['children'])->contains(fn ($child) => ($activeSubnav ?? null) === $child['key']);
                    }

                    $itemClasses = ['portal-nav__item'];
                    if ($isActive) {
                        $itemClasses[] = 'portal-nav__item--active';
                    }
                    if ($isSelected) {
                        $itemClasses[] = 'portal-nav__item--selected';
                        $itemClasses[] = 'portal-nav__item--open';
                    }
                    if ($hasChildren) {
                        $itemClasses[] = 'portal-nav__item--dropdown';
                    }
                ?>

                <div class="<?php echo e(implode(' ', $itemClasses)); ?>" <?php if($hasChildren): ?> data-nav-group <?php endif; ?>>
                    <?php if($hasChildren): ?>
                        <button type="button" class="portal-nav__head portal-nav__toggle" data-nav-toggle aria-expanded="<?php echo e($isSelected ? 'true' : 'false'); ?>">
                            <?php echo $__env->make('partials.portal-sidebar-icon', ['icon' => $menu['icon']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <span><?php echo e($menu['label']); ?></span>
                            <span class="portal-nav__caret" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M7 10l5 5 5-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </button>

                        <div class="portal-subnav">
                            <?php $__currentLoopData = $menu['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $childHref = $child['href'] ?? '#';
                                    $isChildActive = ($activeSubnav ?? null) === $child['key'];
                                ?>
                                <a href="<?php echo e($childHref); ?>" class="portal-subnav__item<?php echo e($isChildActive ? ' portal-subnav__item--active' : ''); ?>" <?php if(! empty($child['interactive'])): ?> data-subnav-link <?php endif; ?>>
                                    <span class="portal-subnav__icon" aria-hidden="true">
                                        <?php echo $__env->make('partials.portal-sidebar-icon', ['icon' => $child['icon'], 'subnav' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    </span>
                                    <span><?php echo e($child['label']); ?></span>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <?php
                            $menuHref = $menu['href'] ?? '#';
                            $linkClasses = 'portal-nav__head';
                            if (! empty($menu['interactive'])) {
                                $linkClasses .= ' portal-nav__link';
                            }
                        ?>
                        <?php if(($menu['href'] ?? null) !== null): ?>
                            <a href="<?php echo e($menuHref); ?>" class="<?php echo e($linkClasses); ?>" <?php if(! empty($menu['dashboard'])): ?> data-dashboard-link <?php endif; ?>>
                                <?php echo $__env->make('partials.portal-sidebar-icon', ['icon' => $menu['icon']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                <span><?php echo e($menu['label']); ?></span>
                            </a>
                        <?php else: ?>
                            <button type="button" class="<?php echo e($linkClasses); ?>" <?php if(! empty($menu['dashboard'])): ?> data-dashboard-link <?php endif; ?>>
                                <?php echo $__env->make('partials.portal-sidebar-icon', ['icon' => $menu['icon']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                <span><?php echo e($menu['label']); ?></span>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>

        <div class="portal-mobile-actions">
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="portal-mobile-actions__form">
                <?php echo csrf_field(); ?>
                <button type="submit" class="portal-logout portal-logout--menu">
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
    </div>
</aside>
<?php /**PATH D:\1 Kerja\api-project\resources\views/partials/portal-sidebar.blade.php ENDPATH**/ ?>