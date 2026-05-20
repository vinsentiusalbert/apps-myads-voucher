<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Register Page</title>
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
                    <h1 class="simple-title"><?php echo e($hasExistingSsoUser ? 'Masuk akun MyAds' : 'Daftar akun MyAds'); ?></h1>
                    <p class="simple-copy">
                        <?php echo e($hasExistingSsoUser
                            ? 'Akun SSO partner sudah terdaftar. Masukkan ID dan password untuk masuk.'
                            : 'Lengkapi data akun untuk melanjutkan akses partner melalui SSO.'); ?>

                    </p>
                </div>

                <?php if(! empty($contextMessage)): ?>
                    <div class="status-box status-box--warning">
                        <?php echo e($contextMessage); ?>

                    </div>
                <?php endif; ?>

                <?php if($hasExistingSsoUser): ?>
                <form method="POST" action="<?php echo e(route('login.store')); ?>" class="login-form">
                    <?php echo csrf_field(); ?>

                    <input name="merchant_id" type="hidden" value="<?php echo e($merchantId); ?>">
                    <input name="partner_id" type="hidden" value="<?php echo e($partnerId); ?>">

                    <div class="field-group">
                        <label for="merchant_id" class="field-label">Merchant ID</label>
                        <input id="merchant_id" type="text" value="<?php echo e($merchantId); ?>" readonly class="text-input">
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
                        <label for="partner_id" class="field-label">Partner ID</label>
                        <input id="partner_id" type="text" value="<?php echo e($partnerId); ?>" readonly class="text-input">
                        <?php $__errorArgs = ['partner_id'];
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
                        <label for="password" class="field-label">Password</label>
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

                    <button type="submit" class="submit-btn">Masuk</button>
                </form>
                <?php else: ?>
                <form method="POST" action="<?php echo e(route('register.store')); ?>" class="login-form">
                    <?php echo csrf_field(); ?>

                    <div class="field-group">
                        <label for="merchant_id" class="field-label">Merchant ID</label>
                        <input id="merchant_id" name="merchant_id" type="text" value="<?php echo e(old('merchant_id', $merchantId)); ?>" readonly required class="text-input">
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
                        <label for="partner_id" class="field-label">Partner ID</label>
                        <input id="partner_id" name="partner_id" type="text" value="<?php echo e(old('partner_id', $partnerId)); ?>" readonly required class="text-input">
                        <?php $__errorArgs = ['partner_id'];
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
                        <label for="name" class="field-label">Nama Lengkap</label>
                        <input id="name" name="name" type="text" value="<?php echo e(old('name')); ?>" required autofocus class="text-input">
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
                        <input id="email" name="email" type="email" value="<?php echo e(old('email')); ?>" required class="text-input">
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
                        <label for="password_confirmation" class="field-label">Konfirmasi Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required class="text-input">
                    </div>

                    <button type="submit" class="submit-btn">Daftar</button>
                </form>
                <?php endif; ?>
            </section>
        </main>
    </body>
</html>
<?php /**PATH D:\1 Kerja\api-project\resources\views/auth/register.blade.php ENDPATH**/ ?>