<?php $__env->startSection('title', 'Enrollment Successful'); ?>

<?php $__env->startSection('content'); ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="text-center mb-4">
            <div class="ep-success-icon"><i class="fas fa-check"></i></div>
            <h1 class="ep-page-title">Application Submitted!</h1>
            <p class="ep-page-subtitle">Your enrollment application has been received and your account<?php echo e(isset($accountDetails['parent_account']) ? 's have' : ' has'); ?> been created.</p>
        </div>

        <div class="ep-alert ep-alert-danger mb-4">
            <i class="fas fa-camera me-2"></i>
            <strong>IMPORTANT:</strong> Screenshot your login credentials below before leaving this page.
        </div>

        
        <div class="ep-card mb-4">
            <div class="ep-card-header">
                <h3><i class="fas fa-graduation-cap me-2 text-primary"></i>Student Account Credentials</h3>
            </div>
            <div class="ep-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="ep-info-item"><div class="label">Student Name</div><div class="value"><?php echo e($accountDetails['student_name']); ?></div></div>
                        <div class="ep-info-item"><div class="label">Grade Level</div><div class="value"><?php echo e($accountDetails['grade_level']); ?></div></div>
                        <div class="ep-info-item">
                            <div class="label">Section</div>
                            <div class="ep-credential <?php echo e($accountDetails['assigned_section'] !== 'To be assigned after approval' ? 'success-bg' : ''); ?>">
                                <i class="fas fa-users text-success"></i>
                                <strong><?php echo e($accountDetails['assigned_section']); ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="ep-info-item"><div class="label">Application No.</div><div class="value"><?php echo e($accountDetails['application_number']); ?></div></div>
                        <div class="ep-info-item"><div class="label">Email / Username</div><div class="ep-credential"><i class="fas fa-envelope"></i><?php echo e($accountDetails['email']); ?></div></div>
                        <div class="ep-info-item">
                            <div class="label">Temporary Password</div>
                            <div class="ep-credential highlight">
                                <i class="fas fa-key text-warning"></i>
                                <span id="password-display" class="fw-bold"><?php echo e($accountDetails['password']); ?></span>
                                <button type="button" class="ep-btn ep-btn-sm ep-btn-ghost ms-auto" onclick="togglePassword()"><i class="fas fa-eye" id="toggle-icon"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if(isset($accountDetails['parent_account']) && $accountDetails['parent_account']): ?>
        <div class="ep-card mb-4">
            <div class="ep-card-header">
                <h3><i class="fas fa-user-friends me-2 text-primary"></i>Parent Account Credentials</h3>
            </div>
            <div class="ep-card-body">
                <div class="ep-alert ep-alert-info mb-3">A parent portal account was created so you can monitor your child's progress.</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="ep-info-item"><div class="label">Parent Name</div><div class="value"><?php echo e($accountDetails['parent_account']['name']); ?></div></div>
                        <div class="ep-info-item"><div class="label">Email</div><div class="ep-credential"><i class="fas fa-envelope"></i><?php echo e($accountDetails['parent_account']['email']); ?></div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="ep-info-item">
                            <div class="label">Temporary Password</div>
                            <div class="ep-credential highlight">
                                <i class="fas fa-key"></i>
                                <span id="parent-password-display" class="fw-bold"><?php echo e($accountDetails['parent_account']['password']); ?></span>
                                <button type="button" class="ep-btn ep-btn-sm ep-btn-ghost ms-auto" onclick="toggleParentPassword()"><i class="fas fa-eye" id="parent-toggle-icon"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="ep-card mb-4">
            <div class="ep-card-header"><h3>Next Steps</h3></div>
            <div class="ep-card-body">
                <div class="ep-process-list">
                    <div class="ep-process-item"><div class="ep-process-num">1</div><span>Screenshot all credentials above</span></div>
                    <div class="ep-process-item"><div class="ep-process-num">2</div><span><a href="<?php echo e(route('login')); ?>" target="_blank">Login to the LMS</a> and change your password</span></div>
                    <div class="ep-process-item"><div class="ep-process-num">3</div><span>Wait 3–5 business days for registrar approval</span></div>
                    <div class="ep-process-item"><div class="ep-process-num">4</div><span>Track status anytime via Check Status</span></div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-4 justify-content-center">
                    <a href="<?php echo e(route('login')); ?>" class="ep-btn ep-btn-primary ep-btn-lg" target="_blank"><i class="fas fa-sign-in-alt"></i> Go to Login</a>
                    <a href="<?php echo e(route('enrollment.portal.status')); ?>" class="ep-btn ep-btn-outline ep-btn-lg"><i class="fas fa-search"></i> Check Status</a>
                    <a href="<?php echo e(route('enrollment.portal.index')); ?>" class="ep-btn ep-btn-outline ep-btn-lg"><i class="fas fa-home"></i> Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function togglePassword() {
    const el = document.getElementById('password-display');
    const icon = document.getElementById('toggle-icon');
    if (el.textContent === '<?php echo e($accountDetails['password']); ?>') { el.textContent = '••••••••'; icon.className = 'fas fa-eye-slash'; }
    else { el.textContent = '<?php echo e($accountDetails['password']); ?>'; icon.className = 'fas fa-eye'; }
}
<?php if(isset($accountDetails['parent_account']) && $accountDetails['parent_account']): ?>
function toggleParentPassword() {
    const el = document.getElementById('parent-password-display');
    const icon = document.getElementById('parent-toggle-icon');
    if (el.textContent === '<?php echo e($accountDetails['parent_account']['password']); ?>') { el.textContent = '••••••••'; icon.className = 'fas fa-eye-slash'; }
    else { el.textContent = '<?php echo e($accountDetails['parent_account']['password']); ?>'; icon.className = 'fas fa-eye'; }
}
<?php endif; ?>
setTimeout(function() {
    ['password-display','parent-password-display'].forEach(id => {
        const el = document.getElementById(id);
        if (el && el.textContent !== '••••••••') el.textContent = '••••••••';
    });
    if (typeof toastr !== 'undefined') toastr.warning('Passwords hidden for security. Ensure you saved a screenshot!', 'Security');
}, 60000);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.enrollment-portal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/enrollment/portal/success.blade.php ENDPATH**/ ?>