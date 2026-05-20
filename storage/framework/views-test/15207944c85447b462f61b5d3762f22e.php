<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login Page</title>
        <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('assets/goto-icon.svg')); ?>">
        <link rel="apple-touch-icon" href="<?php echo e(asset('assets/goto-icon.svg')); ?>">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:500,700|plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        <link rel="stylesheet" href="<?php echo e(asset('css/auth-portal.css')); ?>">
    </head>
    <body>
        <main class="auth-shell">
            <section class="auth-frame">
                <div class="auth-hero">
                    <div class="auth-hero-inner">
                        <div>
                            <div class="brand-lockup">
                                <div class="brand-mark">
                                    <img src="<?php echo e(asset('assets/logo.png')); ?>" alt="MyAds Logo" class="brand-logo">
                                </div>
                                <div>
                                    
                                    <p class="brand-subtitle">GOTO Ads Portal</p>
                                </div>
                            </div>

                            <div class="hero-copy">
                                <p class="eyebrow">Selamat Datang</p>
                                
                                
                            </div>
                        </div>

                        <div class="hero-footer">
                            <p>Senang berjumpa kembali. Ikuti langkah berikut untuk mulai beriklan dengan myAds.</p>
                            <span>&copy;PT GOTO Digital Nusantara, 2026</span>
                        </div>
                    </div>
                        </div>

                <div class="auth-panel">
                    <div class="panel-card">
                        <div class="panel-topbar">
                            <p class="panel-register">Akses portal menggunakan akun MyAds.</p>
                        </div>
                        <h2 class="panel-title">Masuk akun MyAds</h2>
                        <p class="panel-copy">Masukkan ID atau email dan password untuk melanjutkan ke portal.</p>

                        <?php if(session('status')): ?>
                            <div class="status-box status-box--success"><?php echo e(session('status')); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo e(route('login.store')); ?>" class="login-form">
                            <?php echo csrf_field(); ?>

                            <div class="field-group">
                                <label for="email" class="field-label">ID / Email</label>
                                <input id="email" name="email" type="text" value="<?php echo e(old('email')); ?>" required autofocus class="text-input">
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
                                <div class="field-label-row">
                                    <label for="password" class="field-label">Password</label>
                                    <a href="<?php echo e(route('password.request')); ?>" class="field-link">Lupa password?</a>
                                </div>
                                <input id="password" name="password" type="password" required class="text-input">
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
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
<?php /**PATH D:\1 Kerja\api-project\resources\views/auth/login.blade.php ENDPATH**/ ?>