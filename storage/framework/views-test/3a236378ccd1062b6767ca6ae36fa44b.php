<?php
    $title = $page['title'];
    $bodyClass = '';
    $mainClass = '';
    $contentClass = 'portal-content--template-library';
    $isWaTemplateRead = false;
    $navMenus = [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'dashboard',
            'href' => route('dashboard'),
        ],
        [
            'key' => 'sms',
            'label' => 'Daftar SMS',
            'icon' => 'sms',
            'children' => [
                [
                    'key' => 'sms-location-based-area',
                    'label' => 'Location Based Area',
                    'icon' => 'location',
                    'href' => route('campaign.menu', ['channel' => 'sms', 'menu' => 'location-based-area']),
                ],
                [
                    'key' => 'sms-targeted',
                    'label' => 'Targeted',
                    'icon' => 'targeted',
                    'href' => route('campaign.menu', ['channel' => 'sms', 'menu' => 'targeted']),
                ],
            ],
        ],
        [
            'key' => 'wa-business',
            'label' => 'Daftar WA Business',
            'icon' => 'wa',
            'children' => [
                [
                    'key' => 'wa-campaign-template',
                    'label' => 'Campaign Template',
                    'icon' => 'template',
                    'href' => route('campaign-template.index'),
                ],
                [
                    'key' => 'wa-location-based-area',
                    'label' => 'Location Based Area',
                    'icon' => 'location',
                    'href' => route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'location-based-area']),
                ],
                [
                    'key' => 'wa-targeted',
                    'label' => 'Targeted',
                    'icon' => 'targeted',
                    'href' => route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'targeted']),
                ],
            ],
        ],
    ];
    $activeNav = 'wa-business';
    $activeSubnav = 'wa-campaign-template';
?>



<?php $__env->startSection('content'); ?>
    <section class="template-builder-hero">
        <h1 class="template-builder-hero__title">Create Campaign Template</h1>
    </section>

    <?php echo $__env->make('campaign-wa-template.partials.builder-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <?php echo $__env->make('campaign-wa-template.partials.preview-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.portal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\1 Kerja\shop-myads\resources\views/campaign-wa-template/create.blade.php ENDPATH**/ ?>