

<?php $__env->startSection('title', 'Old Student Enrollment Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="ep-card mb-4" style="background:linear-gradient(135deg,#059669,#22C55E);border:none;color:#fff;">
    <div class="ep-card-body py-4">
        <h1 class="ep-page-title" style="color:#fff!important;">Welcome back, <?php echo e($student->first_name); ?>!</h1>
        <p style="color:rgba(255,255,255,.9)!important;margin:0;">Review your subjects and select your section for <?php echo e($academicYear->name ?? 'this school year'); ?>.</p>
    </div>
</div>

<div class="ep-card mb-4">
    <div class="ep-card-header">
        <h3><i class="fas fa-user-circle me-2"></i>Student Information</h3>
    </div>
    <div class="ep-card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <p class="mb-2"><span class="text-muted">Name</span><br><strong><?php echo e($student->first_name); ?> <?php echo e($student->middle_name); ?> <?php echo e($student->last_name); ?></strong></p>
                <p class="mb-2"><span class="text-muted">Grade Level</span><br><strong><?php echo e($student->year_level); ?></strong></p>
                <p class="mb-0"><span class="text-muted">Email</span><br><strong><?php echo e($student->email); ?></strong></p>
            </div>
            <div class="col-md-6">
                <p class="mb-2"><span class="text-muted">Student ID</span><br><strong><?php echo e($student->user_id ?? 'N/A'); ?></strong></p>
                <p class="mb-2"><span class="text-muted">Academic Year</span><br><strong><?php echo e($academicYear->name ?? 'Not Set'); ?></strong></p>
                <p class="mb-0"><span class="text-muted">Semester</span><br><strong><?php echo e($semester->name ?? 'Not Set'); ?></strong></p>
            </div>
        </div>
    </div>
</div>

<div class="ep-card mb-4">
    <div class="ep-card-header">
        <h3><i class="fas fa-book me-2"></i>Upcoming Subjects — <?php echo e($student->year_level); ?></h3>
    </div>
    <div class="ep-card-body">
        <?php if($subjects->count() > 0): ?>
            <div class="row g-3">
                <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $subjectSchedules = $schedules->flatten()->where('subject_id', $subject->id); ?>
                    <div class="col-md-6">
                        <div class="ep-subject-card h-100">
                            <div class="ep-subject-card-title">
                                <i class="fas fa-book-open me-2"></i><?php echo e($subject->subject_name); ?>

                            </div>
                            <p class="text-muted small mb-2">ID: <?php echo e($subject->subject_id); ?></p>
                            <?php if($subjectSchedules->count() > 0): ?>
                                <div class="ep-schedule-list">
                                    <?php $__currentLoopData = $subjectSchedules->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="ep-schedule-item">
                                            <i class="fas fa-calendar-day me-1"></i>
                                            <strong><?php echo e(ucfirst($schedule->day_of_week)); ?></strong>
                                            <?php echo e(\Carbon\Carbon::parse($schedule->start_time)->format('g:i A')); ?> –
                                            <?php echo e(\Carbon\Carbon::parse($schedule->end_time)->format('g:i A')); ?>

                                            <?php if($schedule->room): ?>
                                                <br><small class="text-muted ms-3"><i class="fas fa-door-open me-1"></i><?php echo e($schedule->room->room_name); ?></small>
                                            <?php endif; ?>
                                            <?php if($schedule->teacher): ?>
                                                <br><small class="text-muted ms-3"><i class="fas fa-chalkboard-teacher me-1"></i><?php echo e($schedule->teacher->full_name); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($subjectSchedules->count() > 3): ?>
                                        <small class="text-muted">+<?php echo e($subjectSchedules->count() - 3); ?> more schedules</small>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted small mb-0">Schedule to be announced</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="ep-alert ep-alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>No subjects found for <?php echo e($student->year_level); ?>. Please contact the registrar.
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="ep-card mb-4">
    <div class="ep-card-header">
        <h3><i class="fas fa-users me-2"></i>Available Sections — <?php echo e($student->year_level); ?></h3>
    </div>
    <div class="ep-card-body">
        <div class="ep-alert ep-alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Choose your section</strong> to complete enrollment. Sections are assigned based on your grade level.
        </div>

        <?php if($availableSections->count() > 0): ?>
            <div class="row g-3">
                <?php $__currentLoopData = $availableSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-6">
                        <div class="ep-section-card <?php echo e($section->is_full ? 'is-full' : ''); ?>"
                             <?php if(!$section->is_full): ?> role="button" tabindex="0" data-section-id="<?php echo e($section->id); ?>" <?php endif; ?>>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1"><i class="fas fa-chalkboard me-2 text-primary"></i><?php echo e($section->name); ?></h5>
                                    <small class="text-muted">Grade <?php echo e($section->grade_level); ?></small>
                                </div>
                                <?php if($section->is_full): ?>
                                    <span class="ep-chip ep-chip-rejected">Full</span>
                                <?php else: ?>
                                    <span class="ep-chip ep-chip-approved">Available</span>
                                <?php endif; ?>
                            </div>

                            <p class="mb-2 small">
                                <i class="fas fa-user-tie me-1"></i>
                                <strong>Adviser:</strong> <?php echo e($section->adviser?->full_name ?? 'To be assigned'); ?>

                            </p>
                            <p class="mb-3 small">
                                <i class="fas fa-users me-1"></i>
                                <strong>Capacity:</strong>
                                <?php if($section->capacity): ?>
                                    <?php echo e($section->available_spots); ?> / <?php echo e($section->capacity); ?> spots available
                                <?php else: ?>
                                    <?php echo e($section->available_spots); ?> spots available
                                <?php endif; ?>
                            </p>

                            <?php if($section->description): ?>
                                <p class="small text-muted mb-3"><?php echo e($section->description); ?></p>
                            <?php endif; ?>

                            <?php if(!$section->is_full): ?>
                                <button type="button" class="ep-btn ep-btn-success w-100">
                                    <i class="fas fa-check-circle me-2"></i>Select This Section
                                </button>
                            <?php else: ?>
                                <button type="button" class="ep-btn w-100" disabled style="opacity:.6;">
                                    <i class="fas fa-times-circle me-2"></i>Section is Full
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="ep-alert ep-alert-warning mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No sections are currently available for <?php echo e($student->year_level); ?>. Please contact the registrar.
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="d-flex justify-content-center gap-2 flex-wrap mb-4">
    <form action="<?php echo e(route('enrollment.old-student.login')); ?>" method="GET">
        <button type="submit" class="ep-btn ep-btn-outline"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
    </form>
    <a href="<?php echo e(route('enrollment.portal.index')); ?>" class="ep-btn ep-btn-outline"><i class="fas fa-home me-2"></i>Back to Portal</a>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function selectSection(sectionId) {
    if (!confirm('Are you sure you want to select this section? This will complete your enrollment.')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?php echo e(route("enrollment.old-student.select-section")); ?>';
    form.innerHTML = '<input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">' +
        '<input type="hidden" name="section_id" value="' + sectionId + '">';
    document.body.appendChild(form);
    form.submit();
}

document.querySelectorAll('.ep-section-card[data-section-id]').forEach(card => {
    const id = card.dataset.sectionId;
    card.addEventListener('click', e => {
        if (e.target.closest('button')) return;
        selectSection(id);
    });
    card.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); selectSection(id); }
    });
    card.querySelector('button')?.addEventListener('click', e => {
        e.stopPropagation();
        selectSection(id);
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.enrollment-portal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\enrollment\portal\old-student\dashboard.blade.php ENDPATH**/ ?>