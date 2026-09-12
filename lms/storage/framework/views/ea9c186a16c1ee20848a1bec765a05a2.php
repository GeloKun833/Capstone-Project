
<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Create Class Schedule</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.schedules.index')); ?>">Schedules</a></li>
                        <li class="breadcrumb-item active">Create Schedule</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap gap-2 align-items-center schedule-steps">
                    <span class="badge rounded-pill bg-primary step-badge" data-step="1">1. Teacher</span>
                    <i class="fas fa-chevron-right text-muted"></i>
                    <span class="badge rounded-pill bg-secondary step-badge" data-step="2">2. Class Information</span>
                    <i class="fas fa-chevron-right text-muted"></i>
                    <span class="badge rounded-pill bg-secondary step-badge" data-step="3">3. Schedule Details</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="<?php echo e(route('admin.schedules.store')); ?>" method="POST" id="scheduleForm">
                    <?php echo csrf_field(); ?>

                    
                    <div class="row" id="step-teacher">
                        <div class="col-12">
                            <h5 class="form-title"><span>Step 1 — Select Teacher</span></h5>
                            <p class="text-muted small">Choose the teacher first. Section and subject lists will load from their current assignments.</p>
                        </div>
                        <div class="col-12 col-md-8 col-lg-6">
                            <div class="form-group local-forms">
                                <label>Teacher <span class="login-danger">*</span></label>
                                <select class="form-control" name="teacher_id" id="teacher_id" required>
                                    <option value="">Select Teacher</option>
                                    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($teacher->id); ?>" <?php echo e(old('teacher_id') == $teacher->id ? 'selected' : ''); ?>>
                                            <?php echo e($teacher->full_name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['teacher_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <div id="teacher-assign-hint" class="small text-muted mt-1"></div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="row mt-2 d-none" id="step-class">
                        <div class="col-12">
                            <h5 class="form-title"><span>Step 2 — Class Information</span></h5>
                            <p class="text-muted small">Only sections and subjects assigned to the selected teacher are shown.</p>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group local-forms">
                                <label>Section <span class="login-danger">*</span></label>
                                <select class="form-control" name="section_id" id="section_id" required disabled>
                                    <option value="">Select Section</option>
                                </select>
                                <?php $__errorArgs = ['section_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group local-forms">
                                <label>Subject <span class="login-danger">*</span></label>
                                <select class="form-control" name="subject_id" id="subject_id" required disabled>
                                    <option value="">Select Subject</option>
                                </select>
                                <?php $__errorArgs = ['subject_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <small class="text-muted" id="subject-filter-hint"></small>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group local-forms">
                                <label>Room</label>
                                <select class="form-control" name="room_id" id="room_id">
                                    <option value="">Select Room (Optional)</option>
                                    <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($room->id); ?>" <?php echo e(old('room_id') == $room->id ? 'selected' : ''); ?>>
                                            <?php echo e($room->room_name); ?><?php if($room->room_type): ?> — <?php echo e($room->room_type); ?><?php endif; ?>
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['room_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="alert alert-light border py-2 mb-0" id="class-info-summary">
                                <small class="text-muted">Class summary will appear after you choose section and subject.</small>
                            </div>
                        </div>
                    </div>

                    
                    <div class="row mt-3 d-none" id="step-schedule">
                        <div class="col-12">
                            <h5 class="form-title"><span>Step 3 — Schedule Details &amp; Notes</span></h5>
                        </div>

                        <div class="col-12 col-sm-4">
                            <div class="form-group local-forms">
                                <label>Day of Week <span class="login-danger">*</span></label>
                                <select class="form-control" name="day_of_week" required>
                                    <option value="">Select Day</option>
                                    <?php $__currentLoopData = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($day); ?>" <?php echo e(old('day_of_week') == $day ? 'selected' : ''); ?>><?php echo e(ucfirst($day)); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['day_of_week'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="col-12 col-sm-4">
                            <div class="form-group local-forms">
                                <label>Start Time <span class="login-danger">*</span></label>
                                <input type="time" class="form-control" name="start_time" value="<?php echo e(old('start_time')); ?>" required>
                                <?php $__errorArgs = ['start_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="col-12 col-sm-4">
                            <div class="form-group local-forms">
                                <label>End Time <span class="login-danger">*</span></label>
                                <input type="time" class="form-control" name="end_time" value="<?php echo e(old('end_time')); ?>" required>
                                <?php $__errorArgs = ['end_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group local-forms">
                                <label>Class Type <span class="login-danger">*</span></label>
                                <select class="form-control" name="class_type" required>
                                    <option value="">Select Type</option>
                                    <option value="lecture" <?php echo e(old('class_type') == 'lecture' ? 'selected' : ''); ?>>Regular Class</option>
                                    <option value="laboratory" <?php echo e(old('class_type') == 'laboratory' ? 'selected' : ''); ?>>Laboratory</option>
                                    <option value="tutorial" <?php echo e(old('class_type') == 'tutorial' ? 'selected' : ''); ?>>Activity / Tutorial</option>
                                    <option value="exam" <?php echo e(old('class_type') == 'exam' ? 'selected' : ''); ?>>Exam</option>
                                    <option value="other" <?php echo e(old('class_type') == 'other' ? 'selected' : ''); ?>>Other</option>
                                </select>
                                <?php $__errorArgs = ['class_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group local-forms">
                                <label>Color</label>
                                <input type="color" class="form-control" name="color" value="<?php echo e(old('color', '#3d5ee1')); ?>" style="height: 45px;">
                                <small class="text-muted">Used on calendar display</small>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group local-forms">
                                <label>Notes</label>
                                <textarea class="form-control" name="notes" rows="3" placeholder="Optional notes or special instructions"><?php echo e(old('notes')); ?></textarea>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Schedule
                            </button>
                            <a href="<?php echo e(route('admin.schedules.index')); ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const assignmentsUrl = <?php echo json_encode(url('/admin/schedules/teacher'), 15, 512) ?>;
    const oldTeacher = <?php echo json_encode(old('teacher_id'), 15, 512) ?>;
    const oldSection = <?php echo json_encode(old('section_id'), 15, 512) ?>;
    const oldSubject = <?php echo json_encode(old('subject_id'), 15, 512) ?>;

    let allSubjects = [];
    let allSections = [];

    const teacherSelect = document.getElementById('teacher_id');
    const sectionSelect = document.getElementById('section_id');
    const subjectSelect = document.getElementById('subject_id');
    const stepClass = document.getElementById('step-class');
    const stepSchedule = document.getElementById('step-schedule');
    const hint = document.getElementById('teacher-assign-hint');
    const subjectHint = document.getElementById('subject-filter-hint');
    const summary = document.getElementById('class-info-summary');

    function setStepBadges(active) {
        document.querySelectorAll('.step-badge').forEach(function (el) {
            const step = Number(el.getAttribute('data-step'));
            el.classList.toggle('bg-primary', step <= active);
            el.classList.toggle('bg-secondary', step > active);
        });
    }

    function resetClassFields() {
        sectionSelect.innerHTML = '<option value="">Select Section</option>';
        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
        sectionSelect.disabled = true;
        subjectSelect.disabled = true;
        allSubjects = [];
        allSections = [];
        if (summary) summary.innerHTML = '<small class="text-muted">Class summary will appear after you choose section and subject.</small>';
        if (subjectHint) subjectHint.textContent = '';
    }

    function fillSections(selectedId) {
        sectionSelect.innerHTML = '<option value="">Select Section</option>';
        allSections.forEach(function (sec) {
            const opt = document.createElement('option');
            opt.value = sec.id;
            opt.textContent = sec.label;
            opt.dataset.grade = sec.grade_level || '';
            if (String(selectedId) === String(sec.id)) opt.selected = true;
            sectionSelect.appendChild(opt);
        });
        sectionSelect.disabled = allSections.length === 0;
    }

    function subjectsForSection(sectionId) {
        if (!sectionId) return allSubjects;
        const sec = allSections.find(function (s) { return String(s.id) === String(sectionId); });
        const grade = sec ? (sec.grade_level || '') : '';
        if (!grade) return allSubjects;
        const matched = allSubjects.filter(function (sub) {
            return !sub.class || String(sub.class) === String(grade);
        });
        return matched.length ? matched : allSubjects;
    }

    function fillSubjects(sectionId, selectedId) {
        const list = subjectsForSection(sectionId);
        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
        list.forEach(function (sub) {
            const opt = document.createElement('option');
            opt.value = sub.id;
            opt.textContent = sub.label;
            if (String(selectedId) === String(sub.id)) opt.selected = true;
            subjectSelect.appendChild(opt);
        });
        subjectSelect.disabled = list.length === 0;
        if (subjectHint) {
            subjectHint.textContent = sectionId
                ? (list.length + ' subject(s) for the selected section/grade')
                : (allSubjects.length + ' assigned subject(s)');
        }
    }

    function updateSummary() {
        const teacherName = teacherSelect.options[teacherSelect.selectedIndex]?.text || '';
        const sectionName = sectionSelect.options[sectionSelect.selectedIndex]?.text || '';
        const subjectName = subjectSelect.options[subjectSelect.selectedIndex]?.text || '';
        if (!sectionSelect.value || !subjectSelect.value) {
            summary.innerHTML = '<small class="text-muted">Class summary will appear after you choose section and subject.</small>';
            return;
        }
        summary.innerHTML = '<strong>Class:</strong> ' + subjectName +
            ' &nbsp;|&nbsp; <strong>Section:</strong> ' + sectionName +
            ' &nbsp;|&nbsp; <strong>Teacher:</strong> ' + teacherName;
        stepSchedule.classList.remove('d-none');
        setStepBadges(3);
    }

    function loadTeacher(teacherId, preselectSection, preselectSubject) {
        resetClassFields();
        stepClass.classList.add('d-none');
        stepSchedule.classList.add('d-none');
        setStepBadges(1);
        hint.textContent = '';

        if (!teacherId) return;

        hint.textContent = 'Loading assignments…';
        fetch(assignmentsUrl + '/' + teacherId + '/assignments', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            allSections = data.sections || [];
            allSubjects = data.subjects || [];
            hint.textContent = (allSections.length + ' section(s), ' + allSubjects.length + ' subject(s) assigned');

            if (!allSections.length && !allSubjects.length) {
                hint.innerHTML = '<span class="text-danger">No section/subject assignments found for this teacher. Assign them under Classes &amp; Subjects first.</span>';
                return;
            }

            stepClass.classList.remove('d-none');
            setStepBadges(2);
            fillSections(preselectSection || '');
            fillSubjects(preselectSection || '', preselectSubject || '');
            if (preselectSection && preselectSubject) {
                updateSummary();
            }
        })
        .catch(function () {
            hint.innerHTML = '<span class="text-danger">Failed to load teacher assignments.</span>';
        });
    }

    teacherSelect.addEventListener('change', function () {
        loadTeacher(this.value);
    });

    sectionSelect.addEventListener('change', function () {
        fillSubjects(this.value, '');
        updateSummary();
    });

    subjectSelect.addEventListener('change', updateSummary);

    if (oldTeacher) {
        loadTeacher(oldTeacher, oldSection, oldSubject);
    }
})();
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\admin\schedules\create.blade.php ENDPATH**/ ?>