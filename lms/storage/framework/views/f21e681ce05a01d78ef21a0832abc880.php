<?php $__env->startSection('content'); ?>

<div class="auth-shell">
    
    <div class="auth-form-panel">
        <div class="login-card">
            <header class="login-card__header">
                <img
                    src="<?php echo e(URL::to('assets/img/Logo.jpg')); ?>"
                    alt="Panorama Montessori School logo"
                    class="login-card__logo"
                    width="72"
                    height="72"
                >
                <h1 class="login-card__title">Welcome back</h1>
                <p class="login-card__subtitle">Sign in to your LMS account to continue</p>
            </header>

            <?php if(session('status') && ! in_array(strtolower(trim((string) session('status'))), ['active', 'inactive', 'disable', 'disabled', 'pending', 'blocked'], true)): ?>
                <div class="alert alert-success" role="alert"><?php echo e(session('status')); ?></div>
            <?php endif; ?>

            <form action="<?php echo e(route('login')); ?>" method="POST" class="login-form" id="login-form" novalidate>
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label for="email" class="form-label">
                        Email address <span class="login-danger" aria-hidden="true">*</span>
                    </label>
                    <div class="input-field">
                        <span class="input-field__icon" aria-hidden="true">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            value="<?php echo e(old('email')); ?>"
                            placeholder="you@school.edu"
                            autocomplete="email"
                            inputmode="email"
                            required
                            autofocus
                        >
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
                    <div class="form-label-row">
                        <label for="password" class="form-label">
                            Password <span class="login-danger" aria-hidden="true">*</span>
                        </label>
                        <a href="<?php echo e(route('password.request')); ?>" class="form-link form-link--inline">
                            Forgot password?
                        </a>
                    </div>
                    <div class="input-field">
                        <span class="input-field__icon" aria-hidden="true">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >
                        <button
                            type="button"
                            class="input-field__toggle toggle-password"
                            aria-label="Show password"
                            aria-pressed="false"
                        >
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </button>
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

                <div class="form-group form-group--compact">
                    <label class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="remember"
                            id="remember"
                            <?php echo e(old('remember') ? 'checked' : ''); ?>

                        >
                        <span class="form-check-label">Keep me signed in</span>
                    </label>
                </div>

                <div class="form-group">
                    <button class="btn-sign-in" type="submit" id="login-submit">
                        <span class="btn-sign-in__text">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            Sign in
                        </span>
                        <span class="btn-sign-in__loading" aria-hidden="true">
                            <i class="fas fa-circle-notch fa-spin"></i>
                            Signing in…
                        </span>
                    </button>
                </div>

                <p class="login-card__footer">
                    Need access? Contact your administrator.
                </p>
            </form>
        </div>
    </div>

    
    <aside class="auth-brand-panel" aria-label="School information">
        <div class="auth-brand-panel__content">
            <p class="auth-brand-panel__eyebrow">Learning Management System</p>
            <h2 class="auth-brand-panel__name">Panorama Montessori School INC.</h2>
            <p class="auth-brand-panel__address">
                Panorama Ville, Brgy. Dita,<br>
                City of Santa Rosa, Laguna
            </p>
            <div class="auth-brand-panel__divider" aria-hidden="true"></div>
            <p class="auth-brand-panel__tagline">
                <span class="auth-brand-panel__tagline-prefix">Fostering a</span>
                <span class="auth-brand-panel__tagline-accent">Passion for Excellence</span>
            </p>
            <p class="auth-brand-panel__motto">Nurturing Minds, Building Futures</p>
        </div>
    </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('login-form');
    const submitBtn = document.getElementById('login-submit');

    if (form && submitBtn) {
        form.addEventListener('submit', function () {
            submitBtn.classList.add('is-loading');
            submitBtn.disabled = true;
        });
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/auth/login.blade.php ENDPATH**/ ?>