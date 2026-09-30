<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Daftar Akun Merchant - ShopAds</title>
        <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('assets/shopads-icon.svg')); ?>">
        <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(asset('assets/shopads-touch-icon.png')); ?>">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:500,700|plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        <link rel="stylesheet" href="<?php echo e(asset('css/auth-portal.css')); ?>?v=<?php echo e(filemtime(public_path('css/auth-portal.css'))); ?>">
    </head>
    <body>
        <main class="auth-simple-shell">
            <section class="auth-simple-card">
                <a href="<?php echo e(route('login')); ?>" class="back-link">&larr; Kembali ke login</a>

                <div class="simple-header">
                    <div class="brand-mark" style="margin-bottom: 1rem; gap: 10px;">
                        <img src="<?php echo e(asset('assets/shopads-icon.svg')); ?>" alt="ShopAds" style="height: 36px;">
                        <span style="font-size: 1.4rem; font-weight: 800; color: var(--brand-primary);">ShopAds</span>
                    </div>
                    <h1 class="simple-title">Daftar Akun Merchant</h1>
                    <p class="simple-copy">
                        Lengkapi data berikut untuk mendaftarkan akun merchant Anda di ShopAds.
                    </p>
                </div>

                <?php if(session('status')): ?>
                    <div class="status-box status-box--success">
                        <?php echo e(session('status')); ?>

                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('register.store')); ?>" class="login-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="partner_id" value="<?php echo e(old('partner_id', $partnerId ?? '')); ?>">

                    <div class="field-group">
                        <label for="merchant_id" class="field-label">Merchant ID</label>
                        <input id="merchant_id" name="merchant_id" type="text" value="<?php echo e(old('merchant_id', $merchantId ?? '')); ?>" placeholder="Bebas diisi, contoh: BJG78783" required autofocus class="text-input">
                        <p class="field-hint" style="font-size: 0.8rem; color: #64748b; margin-top: 0.25rem;">Bebas diisi (ID unik toko/merchant Anda).</p>
                        <?php $__errorArgs = ['merchant_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="error-text"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="field-group">
                        <label for="name" class="field-label">Nama Toko / Nama Lengkap</label>
                        <input id="name" name="name" type="text" value="<?php echo e(old('name')); ?>" placeholder="Contoh: Toko Berkah Jaya" required class="text-input">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="error-text"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="field-group">
                        <label for="email" class="field-label">Alamat Email</label>
                        <input id="email" name="email" type="email" value="<?php echo e(old('email')); ?>" placeholder="email@toko.com" required class="text-input">
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="error-text"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="field-group">
                        <label for="password" class="field-label">Password</label>
                        <input id="password" name="password" type="password" placeholder="Minimal 6 karakter" required class="text-input">
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="error-text"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="field-group">
                        <label for="password_confirmation" class="field-label">Konfirmasi Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Ulangi password" required class="text-input">
                    </div>

                    <button type="submit" class="submit-btn">Daftar Akun Merchant</button>

                    <div class="register-prompt" style="margin-top: 1.25rem; text-align: center; font-size: 0.875rem; color: #64748b;">
                        Sudah memiliki akun? <a href="<?php echo e(route('login')); ?>" style="color: #e1000c; font-weight: 600; text-decoration: none;">Masuk di sini</a>
                    </div>
                </form>
            </section>
        </main>
    </body>
</html>
<?php /**PATH D:\1 Kerja\shop-myads\resources\views/auth/register.blade.php ENDPATH**/ ?>