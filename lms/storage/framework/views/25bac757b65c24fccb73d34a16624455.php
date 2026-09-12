<?php $__env->startSection('content'); ?>
<div class="auth-shell">
    <div class="auth-form-panel">
        <div class="login-card">
            <header class="login-card__header">
                <h1 class="login-card__title">Confirm password</h1>
                <p class="login-card__subtitle">This is a secure area. Please confirm your password before continuing.</p>
            </header>

            <form method="POST" action="<?php echo e(route('password.confirm')); ?>" class="login-form">
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password"
                           class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           name="password" required autocomplete="current-password">
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
                    <button class="btn-sign-in" type="submit">Confirm password</button>
                </div>

                <?php if(Route::has('password.request')): ?>
                    <p class="login-card__footer">
                        <a href="<?php echo e(route('password.request')); ?>" class="form-link">Forgot your password?</a>
                    </p>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\auth\confirm-password.blade.php ENDPATH**/ ?>