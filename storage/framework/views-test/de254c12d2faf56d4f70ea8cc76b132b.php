<?php
    $title = $page['title'];
    $bodyClass = '';
    $mainClass = '';
    $contentClass = 'portal-content--template-library';
    $campaignRows = collect($campaignRows ?? []);
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
    $activeNav = $channel;
    $activeSubnav = $channel === 'sms' ? 'sms-location-based-area' : 'wa-location-based-area';
?>



<?php $__env->startSection('content'); ?>
    <section class="template-library-hero">
        <div>
            <h1 class="template-library-hero__title"><?php echo e($page['title']); ?></h1>
        </div>
        <a href="<?php echo e(route('campaign.menu', ['channel' => $channel, 'menu' => $menu, 'view' => 'create'])); ?>" class="template-library-hero__cta">
            <span>+</span>
            <span>Buat Iklan</span>
        </a>
    </section>

    <section class="template-library-card">
        <div class="template-library-tabs">
            <button type="button" class="template-library-tab template-library-tab--active">
                <span>All</span>
                <strong><?php echo e($campaignRows->count()); ?></strong>
            </button>
        </div>

        <div class="template-library-table-wrap">
            <table class="template-library-table">
                <thead>
                    <tr>
                        <th>ID Iklan</th>
                        <th>Tgl Tayang</th>
                        <th>Judul Pesan Iklan</th>
                        <th>Operator Seluler</th>
                        <th>Kategori Iklan</th>
                        <th>Tipe Kanal</th>
                        <th>Detil Status</th>
                        <th>Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $campaignRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($row['id']); ?></td>
                            <td><?php echo e($row['date']); ?></td>
                            <td>
                                <a href="<?php echo e(route('campaign.menu', ['channel' => $channel, 'menu' => $menu, 'view' => 'show', 'id' => $row['id']])); ?>"><?php echo e($row['title']); ?></a>
                            </td>
                            <td><?php echo e($row['operator']); ?></td>
                            <td><?php echo e($row['category']); ?></td>
                            <td><?php echo e($row['channel_type']); ?></td>
                            <td><?php echo e($row['status_detail']); ?></td>
                            <td><?php echo e($row['total_price']); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8">Belum ada data campaign.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.portal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\1 Kerja\api-project\resources\views/campaign-lba/index.blade.php ENDPATH**/ ?>