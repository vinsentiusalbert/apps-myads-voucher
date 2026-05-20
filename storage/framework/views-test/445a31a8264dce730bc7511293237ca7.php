<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Reset Password Page</title>
        <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('assets/goto-icon.svg')); ?>">
        <link rel="apple-touch-icon" href="<?php echo e(asset('assets/goto-icon.svg')); ?>">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:500,700|plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        <link rel="stylesheet" href="<?php echo e(asset('css/auth-portal.css')); ?>">
    </head>
    <body>
        <main class="auth-simple-shell">
            <section class="auth-simple-card">
                <a href="<?php echo e(route('login')); ?>" class="back-link">Kembali ke login</a>

                <div class="simple-header">
                    <h1 class="simple-title">Reset password</h1>
                    <p class="simple-copy">Masukkan password baru untuk akun Anda.</p>
                </div>

                <form method="POST" action="<?php echo e(route('password.store')); ?>" class="login-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="token" value="<?php echo e($request->route('token')); ?>">

                    <div class="field-group">
                        <label for="email" class="field-label">Alamat Email</label>
                        <input id="email" name="email" type="email" value="<?php echo e(old('email', $request->email)); ?>" required autofocus class="text-input">
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
                        <label for="password" class="field-label">Password Baru</label>
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

                    <div class="field-group">
                        <label for="password_confirmation" class="field-label">Konfirmasi Password Baru</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required class="text-input">
                    </div>

                    <button type="submit" class="submit-btn">Simpan password baru</button>
                </form>
            </section>
        </main>
    </body>
</html>
<?php /**PATH D:\1 Kerja\api-project\resources\views/auth/reset-password.blade.php ENDPATH**/ ?>