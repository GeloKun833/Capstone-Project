

<?php $__env->startSection('title', 'Old Student Login'); ?>

<?php $__env->startSection('content'); ?>
<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="text-center mb-4">
            <div class="ep-stat-icon green d-inline-flex mb-3" style="width:64px;height:64px;font-size:1.5rem;"><i class="fas fa-user-check"></i></div>
            <h1 class="ep-page-title">Returning Student Login</h1>
            <p class="ep-page-subtitle">Sign in to view your subjects, schedule, and select your section.</p>
        </div>

        <div class="ep-card">
            <div class="ep-card-body p-4">
                <form action="<?php echo e(route('enrollment.old-student.authenticate')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="ep-form-group">
                        <label class="ep-label" for="email"><i class="fas fa-envelope me-1 text-primary"></i> Email <span class="required">*</span></label>
                        <input type="email" class="form-control form-control-lg <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="student@email.com" required>
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="ep-form-group">
                        <label class="ep-label" for="password"><i class="fas fa-lock me-1 text-primary"></i> Password <span class="required">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-lg <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   id="password" name="password" placeholder="Your password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="fas fa-eye" id="toggleIcon"></i></button>
                        </div>
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <button type="submit" class="ep-btn ep-btn-success ep-btn-lg ep-btn-block">
                        <i class="fas fa-sign-in-alt"></i> Login & Continue Enrollment
                    </button>
                </form>
                <p class="text-center text-muted mt-4 mb-0 small">
                    New student? <a href="<?php echo e(route('enrollment.portal.create', ['type' => 'new'])); ?>">Apply here</a>
                </p>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="<?php echo e(route('enrollment.portal.index')); ?>" class="ep-btn ep-btn-outline ep-btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.getElementById('togglePassword')?.addEventListener('click', function() {
    const p = document.getElementById('password');
    const i = document.getElementById('toggleIcon');
    p.type = p.type === 'password' ? 'text' : 'password';
    i.classList.toggle('fa-eye'); i.classList.toggle('fa-eye-slash');
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.enrollment-portal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\enrollment\portal\old-student\login.blade.php ENDPATH**/ ?>