<?php $__env->startSection('content'); ?>
<div class="auth-shell">
    <div class="auth-form-panel">
        <div class="login-card">
            <header class="login-card__header">
                <h1 class="login-card__title">Verify your email</h1>
                <p class="login-card__subtitle">
                    Thanks for signing up. Please verify your email address using the link we sent you.
                    If you did not receive the email, we can send another.
                </p>
            </header>

            <?php if(session('status') == 'verification-link-sent'): ?>
                <div class="alert alert-success">
                    A new verification link has been sent to your email address.
                </div>
            <?php endif; ?>

            <?php if(Route::has('verification.send')): ?>
                <form method="POST" action="<?php echo e(route('verification.send')); ?>" class="login-form">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <button class="btn-sign-in" type="submit">Resend verification email</button>
                    </div>
                </form>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('logout')); ?>" class="login-form">
                <?php echo csrf_field(); ?>
                <p class="login-card__footer">
                    <button type="submit" class="form-link" style="background:none;border:none;padding:0;">Log out</button>
                </p>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\auth\verify-email.blade.php ENDPATH**/ ?>