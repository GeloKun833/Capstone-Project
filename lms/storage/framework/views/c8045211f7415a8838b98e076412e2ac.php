<?php $__env->startSection('title', 'Enrollment Successful'); ?>

<?php $__env->startSection('content'); ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="text-center mb-4">
            <div class="ep-success-icon"><i class="fas fa-check"></i></div>
            <h1 class="ep-page-title">Application Submitted!</h1>
            <p class="ep-page-subtitle">Your enrollment application has been received.</p>
        </div>

        <?php if(!empty($accountDetails)): ?>
        <div class="ep-alert ep-alert-danger mb-4">
            <i class="fas fa-camera me-2"></i>
            <strong>IMPORTANT:</strong> Screenshot your login credentials below before leaving this page.
        </div>

        <?php if(!empty($accountDetails['children'])): ?>
        <div class="ep-card mb-4">
            <div class="ep-card-header">
                <h3><i class="fas fa-graduation-cap me-2 text-primary"></i>Child Enrollment Records</h3>
            </div>
            <div class="ep-card-body">
                <p class="mb-3"><strong>Parent Account:</strong> 1 account · <strong>Student Records:</strong> <?php echo e(count($accountDetails['children'])); ?> records</p>
                <?php $__currentLoopData = $accountDetails['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="row g-3 py-3 <?php echo e(!$loop->last ? 'border-bottom' : ''); ?>">
                    <div class="col-md-6">
                        <div class="ep-info-item"><div class="label">Student Name</div><div class="value"><?php echo e($child['student_name']); ?></div></div>
                        <div class="ep-info-item"><div class="label">Grade Level</div><div class="value"><?php echo e($child['grade_level']); ?></div></div>
                        <div class="ep-info-item"><div class="label">Section</div><div class="value"><?php echo e($child['assigned_section']); ?></div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="ep-info-item"><div class="label">Application No.</div><div class="value"><?php echo e($child['application_number']); ?></div></div>
                        <?php if($child['student_account']): ?>
                        <div class="ep-info-item"><div class="label">Student Login</div><div class="ep-credential"><i class="fas fa-envelope"></i><?php echo e($child['email']); ?></div></div>
                        <div class="ep-info-item"><div class="label">Temporary Password</div><div class="ep-credential highlight"><i class="fas fa-key"></i><?php echo e($child['password']); ?></div></div>
                        <?php else: ?>
                        <div class="ep-info-item"><div class="label">Student Login</div><div class="value">No separate student account for this grade level</div></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php elseif($accountDetails['student_account'] ?? false): ?>
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
                                <span id="password-display" class="fw-bold"><?php echo e($accountDetails['password'] ?: 'Shown when the account is created'); ?></span>
                                <?php if(!empty($accountDetails['password'])): ?>
                                <button type="button" class="ep-btn ep-btn-sm ep-btn-ghost ms-auto" onclick="togglePassword()"><i class="fas fa-eye" id="toggle-icon"></i></button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="ep-card mb-4">
            <div class="ep-card-header">
                <h3><i class="fas fa-graduation-cap me-2 text-primary"></i>Student Enrollment</h3>
            </div>
            <div class="ep-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="ep-info-item"><div class="label">Student Name</div><div class="value"><?php echo e($accountDetails['student_name']); ?></div></div>
                        <div class="ep-info-item"><div class="label">Grade Level</div><div class="value"><?php echo e($accountDetails['grade_level']); ?></div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="ep-info-item"><div class="label">Application No.</div><div class="value"><?php echo e($accountDetails['application_number']); ?></div></div>
                        <div class="ep-info-item"><div class="label">Student login</div><div class="value">No separate student account for this grade level</div></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if(!empty($accountDetails['parent_account'])): ?>
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
                                <span id="parent-password-display" class="fw-bold"><?php echo e($accountDetails['parent_account']['password'] ?: 'Use the existing parent password'); ?></span>
                                <?php if(!empty($accountDetails['parent_account']['password'])): ?>
                                <button type="button" class="ep-btn ep-btn-sm ep-btn-ghost ms-auto" onclick="toggleParentPassword()"><i class="fas fa-eye" id="parent-toggle-icon"></i></button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <div class="ep-card mb-4">
            <div class="ep-card-header"><h3>Next Steps</h3></div>
            <div class="ep-card-body">
                <div class="ep-process-list">
                    <?php if(!empty($accountDetails['student_account']) || !empty($accountDetails['parent_account'])): ?>
                    <div class="ep-process-item"><div class="ep-process-num">1</div><span>Screenshot all credentials above</span></div>
                    <div class="ep-process-item"><div class="ep-process-num">2</div><span><a href="<?php echo e(route('login')); ?>" target="_blank">Login to the LMS</a> and change your password</span></div>
                    <div class="ep-process-item"><div class="ep-process-num">3</div><span>Wait 3–5 business days for registrar approval</span></div>
                    <div class="ep-process-item"><div class="ep-process-num">4</div><span>Track status anytime via Check Status</span></div>
                    <?php else: ?>
                    <div class="ep-process-item"><div class="ep-process-num">1</div><span>Wait 3–5 business days for registrar approval</span></div>
                    <div class="ep-process-item"><div class="ep-process-num">2</div><span>Track status anytime via Check Status</span></div>
                    <?php endif; ?>
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
try { localStorage.removeItem('pmsEnrollmentForm'); } catch (error) {}
</script>
<?php if(!empty($accountDetails)): ?>
<script>
<?php if(!empty($accountDetails['password'])): ?>
function togglePassword() {
    const el = document.getElementById('password-display');
    const icon = document.getElementById('toggle-icon');
    if (!el || !icon) return;
    if (el.textContent === <?php echo json_encode($accountDetails['password'], 15, 512) ?>) { el.textContent = '••••••••'; icon.className = 'fas fa-eye-slash'; }
    else { el.textContent = <?php echo json_encode($accountDetails['password'], 15, 512) ?>; icon.className = 'fas fa-eye'; }
}
<?php endif; ?>
<?php if(!empty($accountDetails['parent_account']['password'])): ?>
function toggleParentPassword() {
    const el = document.getElementById('parent-password-display');
    const icon = document.getElementById('parent-toggle-icon');
    if (!el || !icon) return;
    if (el.textContent === <?php echo json_encode($accountDetails['parent_account']['password'], 15, 512) ?>) { el.textContent = '••••••••'; icon.className = 'fas fa-eye-slash'; }
    else { el.textContent = <?php echo json_encode($accountDetails['parent_account']['password'], 15, 512) ?>; icon.className = 'fas fa-eye'; }
}
<?php endif; ?>
setTimeout(function() {
    ['password-display','parent-password-display'].forEach(id => {
        const el = document.getElementById(id);
        if (el && el.textContent !== '••••••••' && el.textContent !== 'Use the existing parent password') el.textContent = '••••••••';
    });
}, 30000);
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.enrollment-portal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/enrollment/portal/success.blade.php ENDPATH**/ ?>