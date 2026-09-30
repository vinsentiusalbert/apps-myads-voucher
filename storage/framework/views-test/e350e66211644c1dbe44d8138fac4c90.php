<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Masuk - ShopAds</title>
        <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('assets/shopads-icon.svg')); ?>">
        <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(asset('assets/shopads-touch-icon.png')); ?>">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:500,700|plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        <link rel="stylesheet" href="<?php echo e(asset('css/auth-portal.css')); ?>?v=<?php echo e(filemtime(public_path('css/auth-portal.css'))); ?>">
    </head>
    <body>
        <main class="auth-shell">
            <section class="auth-frame">
                <div class="auth-hero">
                    <div class="auth-hero-inner">
                        <div>
                            <div class="brand-lockup">
                                <div class="brand-mark">
                                    <img src="<?php echo e(asset('assets/shopads-icon.svg')); ?>" alt="ShopAds" class="brand-logo">
                                </div>
                                <div>
                                    <p class="brand-subtitle">ShopAds</p>
                                </div>
                            </div>

                            <div class="hero-copy">
                                <p class="eyebrow">Selamat Datang</p>
                                <h1 class="auth-title">Tingkatkan penjualan toko Anda dengan periklanan tertarget ShopAds.</h1>
                            </div>
                        </div>

                        <div class="hero-footer">
                            <p>Senang berjumpa kembali. Masuk ke akun Anda untuk mulai mengelola iklan.</p>
                            <span>&copy; ShopAds, <?php echo e(date('Y')); ?></span>
                        </div>
                    </div>
                </div>

                <div class="auth-panel">
                    <div class="panel-card">
                        <div class="panel-topbar">
                            <span class="panel-register">Belum punya akun?</span>
                            <a href="<?php echo e(route('register')); ?>" class="panel-link">Daftar sekarang</a>
                        </div>
                        <h2 class="panel-title">Masuk akun ShopAds</h2>
                        <p class="panel-copy">Masukkan Merchant ID dan password Anda untuk melanjutkan.</p>

                        <?php if(session('status')): ?>
                            <div class="status-box status-box--success"><?php echo e(session('status')); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo e(route('login.store')); ?>" class="login-form">
                            <?php echo csrf_field(); ?>

                            <div class="field-group">
                                <label for="merchant_id" class="field-label">Merchant ID</label>
                                <input id="merchant_id" name="merchant_id" type="text" value="<?php echo e(old('merchant_id')); ?>" placeholder="Contoh: SHOP12345" required autofocus class="text-input">
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
                                <div class="field-label-row">
                                    <label for="password" class="field-label">Password</label>
                                    <a href="<?php echo e(route('password.request')); ?>" class="field-link">Lupa password?</a>
                                </div>
                                <input id="password" name="password" type="password" placeholder="Masukkan password" required class="text-input">
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

                            <div class="field-actions">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="remember" value="1">
                                    <span>Ingat saya</span>
                                </label>
                            </div>

                            <button type="submit" class="submit-btn">Masuk</button>

                            <div class="register-prompt" style="margin-top: 1.25rem; text-align: center; font-size: 0.875rem; color: #64748b;">
                                Belum memiliki akun merchant? <a href="<?php echo e(route('register')); ?>" style="color: #e1000c; font-weight: 600; text-decoration: none;">Daftar di sini</a>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
<?php /**PATH D:\1 Kerja\shop-myads\resources\views/auth/login.blade.php ENDPATH**/ ?>