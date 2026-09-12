<?php $__env->startSection('content'); ?>

<div class="auth-shell">
    <div class="auth-form-panel">
        <div class="login-card">
            <header class="login-card__header">
                <img src="<?php echo e(URL::to('assets/img/Logo.jpg')); ?>" alt="School logo" class="login-card__logo" width="72" height="72">
                <h1 class="login-card__title">Reset password</h1>
                <p class="login-card__subtitle">Choose a new password for your LMS account.</p>
            </header>

            <form action="<?php echo e(route('password.update')); ?>" method="POST" class="login-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="token" value="<?php echo e($token); ?>">

                <div class="form-group">
                    <label for="email" class="form-label">Email address <span class="login-danger">*</span></label>
                    <div class="input-field">
                        <span class="input-field__icon"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="email" name="email"
                               class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               value="<?php echo e($email ?? old('email')); ?>" required>
                    </div>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="invalid-feedback" role="alert"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">New password <span class="login-danger">*</span></label>
                    <div class="input-field">
                        <span class="input-field__icon"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password"
                               class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               required autocomplete="new-password">
                    </div>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="invalid-feedback" role="alert"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm new password <span class="login-danger">*</span></label>
                    <div class="input-field">
                        <span class="input-field__icon"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control" required autocomplete="new-password">
                    </div>
                </div>

                <div class="form-group">
                    <button class="btn-sign-in" type="submit">
                        <span class="btn-sign-in__text">
                            <i class="fas fa-key"></i> Update password
                        </span>
                    </button>
                </div>

                <p class="login-card__footer">
                    <a href="<?php echo e(route('login')); ?>" class="form-link">Back to sign in</a>
                </p>
            </form>
        </div>
    </div>

    <aside class="auth-brand-panel" aria-label="School information">
        <div class="auth-brand-panel__content">
            <p class="auth-brand-panel__eyebrow">Secure Reset</p>
            <h2 class="auth-brand-panel__name">Panorama Montessori School INC.</h2>
            <p class="auth-brand-panel__address">This link expires after a short time for your security.</p>
        </div>
    </aside>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\auth\reset-password.blade.php ENDPATH**/ ?>