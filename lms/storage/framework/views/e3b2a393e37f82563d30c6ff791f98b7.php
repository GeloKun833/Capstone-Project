<?php $__env->startSection('content'); ?>

<?php
    $sectionOptions = $classes->unique('section_id')->values();
    $subjectOptions = $sectionId
        ? $classes->where('section_id', $sectionId)->unique('subject_id')->values()
        : collect();
?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page att-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Attendance</h3>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Attendance</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="status">
                <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="GET" action="<?php echo e(route('attendance.index')); ?>" id="attFilterForm" class="att-filters">
            <div class="row g-2 align-items-end">
                <div class="col-md-4 col-lg-2">
                    <label class="form-label" for="date_label">Date</label>
                    <input type="text" class="form-control att-locked" id="date_label" value="<?php echo e(\Carbon\Carbon::parse($date)->format('F j, Y')); ?>" readonly tabindex="-1">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label" for="grade_level">Grade Level</label>
                    <select class="form-control" name="grade_level" id="grade_level">
                        <option value="">All grades</option>
                        <?php $__currentLoopData = $grades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($grade); ?>" <?php if($gradeFilter === $grade): echo 'selected'; endif; ?>><?php echo e($grade); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <?php if($isAdmin): ?>
                    <div class="col-md-4 col-lg-2">
                        <label class="form-label" for="teacher_id">Teacher</label>
                        <select class="form-control" name="teacher_id" id="teacher_id">
                            <option value="">All teachers</option>
                            <?php $__currentLoopData = $teacherOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacherOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($teacherOption['teacher_id']); ?>" <?php if($teacherFilter === (int) $teacherOption['teacher_id']): echo 'selected'; endif; ?>>
                                    <?php echo e($teacherOption['teacher_name']); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="col-md-6 col-lg-3">
                    <label class="form-label" for="section_id">Section</label>
                    <select class="form-control" name="section_id" id="section_id">
                        <option value="">Select section</option>
                        <?php $__currentLoopData = $sectionOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sectionOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($sectionOption['section_id']); ?>" <?php if($sectionId === (int) $sectionOption['section_id']): echo 'selected'; endif; ?>>
                                <?php echo e($sectionOption['grade'] ? $sectionOption['grade'].' · ' : ''); ?><?php echo e($sectionOption['section_name']); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-6 col-lg-3">
                    <label class="form-label" for="subject_id">Subject</label>
                    <select class="form-control" name="subject_id" id="subject_id" <?php if($sectionId === 0): echo 'disabled'; endif; ?>>
                        <option value="">Select subject</option>
                        <?php $__currentLoopData = $subjectOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subjectOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($subjectOption['subject_id']); ?>" <?php if($subjectId === (int) $subjectOption['subject_id']): echo 'selected'; endif; ?>>
                                <?php echo e($subjectOption['subject_name']); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-6 col-lg-4">
                    <label class="form-label" for="search">Search</label>
                    <input type="search" class="form-control" id="search" value="" placeholder="Name or student number" autocomplete="off">
                </div>
                <div class="col-md-4 col-lg-2">
                    <button type="submit" class="btn btn-primary dir-btn w-100">Show class</button>
                </div>
            </div>
        </form>

        <?php if(!$academicYear): ?>
            <div class="att-empty">
                <h5>No current academic year</h5>
                <p>Set a current academic year before recording attendance.</p>
            </div>
        <?php elseif($classes->isEmpty() && $gradeFilter === '' && ! $teacherFilter): ?>
            <div class="att-empty">
                <h5>No class assignments are available for the current Academic Year.</h5>
                <p>Assignments from another academic year are not shown here.</p>
            </div>
        <?php elseif(!$ready): ?>
            <div class="att-empty">
                <h5>Select a section and subject</h5>
                <p>Choose the class for <?php echo e($academicYear->name); ?>, then mark attendance for <?php echo e(\Carbon\Carbon::parse($date)->format('M j, Y')); ?>.</p>
            </div>
        <?php else: ?>
            <?php if($dateOutsideYear): ?>
                <div class="alert alert-warning">That date is outside <?php echo e($academicYear->name); ?>. Choose a date inside this academic year.</div>
            <?php endif; ?>

            <div class="att-summary" aria-label="Attendance summary">
                <div><span>Total</span><strong><?php echo e($daySummary['total']); ?></strong></div>
                <div><span>Present</span><strong><?php echo e($daySummary['present']); ?></strong></div>
                <div><span>Absent</span><strong><?php echo e($daySummary['absent']); ?></strong></div>
                <div><span>Attendance Rate</span><strong><?php echo e(number_format($daySummary['percentage'], 1)); ?>%</strong></div>
            </div>
            <?php if($daySummary['unmarked'] > 0): ?>
                <p class="att-unmarked"><?php echo e($daySummary['unmarked']); ?> student<?php echo e($daySummary['unmarked'] === 1 ? '' : 's'); ?> not marked for this date.</p>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('attendance.store')); ?>" id="attendanceForm">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="section_id" value="<?php echo e($sectionId); ?>">
                <input type="hidden" name="subject_id" value="<?php echo e($subjectId); ?>">

                <div class="att-toolbar">
                    <div>
                        <h5><?php echo e($selectedSection); ?> · <?php echo e($selectedSubject); ?></h5>
                        <p><?php echo e(\Carbon\Carbon::parse($date)->format('M j, Y')); ?> · <?php echo e($academicYear->name); ?></p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary dir-btn" id="markAllPresent" <?php if($students->isEmpty() || $dateOutsideYear): echo 'disabled'; endif; ?>>Mark all present</button>
                        <?php if($students->isNotEmpty()): ?>
                            <a href="<?php echo e(route('attendance.report', ['section_id' => $sectionId, 'subject_id' => $subjectId, 'period' => 'weekly'])); ?>" class="btn btn-outline-secondary dir-btn">View Attendance Report</a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary dir-btn" id="saveAttendance" <?php if($students->isEmpty() || $dateOutsideYear): echo 'disabled'; endif; ?>>
                            Save attendance
                        </button>
                    </div>
                </div>

                <?php if($students->isEmpty()): ?>
                    <div class="att-empty">
                        <h5>No students in this section</h5>
                        <p>No students are enrolled in <?php echo e($selectedSection); ?> for <?php echo e($academicYear->name); ?>.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive att-table-wrap">
                        <table class="table att-table mb-0">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Student Number</th>
                                    <th>Status</th>
                                    <th>Time Present</th>
                                    <th>Remarks</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $status = $existing[$student->id]['status'] ?? '';
                                        $recordId = $existing[$student->id]['id'] ?? null;
                                    ?>
                                    <tr data-find="<?php echo e(strtolower($student->last_name.', '.$student->first_name.' '.$student->last_name.' '.$student->first_name.' '.($student->admission_id ?? ''))); ?>">
                                        <td>
                                            <strong><?php echo e($student->last_name); ?>, <?php echo e($student->first_name); ?></strong>
                                        </td>
                                        <td><?php echo e($student->admission_id ?: '—'); ?></td>
                                        <?php
                                            $savedTime = $existing[$student->id]['time_in'] ?? '';
                                            $isPresent = $status === 'present';
                                        ?>
                                        <td>
                                            <div class="att-choices">
                                                <label class="att-choice is-present">
                                                    <input type="radio" name="attendance[<?php echo e($student->id); ?>][status]" value="present" <?php echo e($isPresent ? 'checked' : ''); ?> <?php if($dateOutsideYear): echo 'disabled'; endif; ?>>
                                                    Present
                                                </label>
                                                <label class="att-choice is-absent">
                                                    <input type="radio" name="attendance[<?php echo e($student->id); ?>][status]" value="absent" <?php echo e($status === 'absent' ? 'checked' : ''); ?> <?php if($dateOutsideYear): echo 'disabled'; endif; ?>>
                                                    Absent
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="att-time-label"><?php echo e($isPresent && $savedTime ? \Carbon\Carbon::createFromFormat('H:i', $savedTime)->format('g:i A') : '—'); ?></span>
                                            <input type="hidden" class="att-time" name="attendance[<?php echo e($student->id); ?>][time_in]" value="<?php echo e($isPresent ? $savedTime : ''); ?>">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control att-note" name="attendance[<?php echo e($student->id); ?>][remarks]"
                                                   value="<?php echo e($existing[$student->id]['remarks'] ?? ''); ?>" placeholder="Optional" maxlength="255" <?php if($dateOutsideYear): echo 'disabled'; endif; ?>>
                                        </td>
                                        <td>
                                            <?php if($recordId): ?>
                                                <a href="<?php echo e(route('attendance.edit', $recordId)); ?>" class="att-link">Edit</a>
                                            <?php else: ?>
                                                <span class="text-muted">Not saved</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="att-unmarked d-none" id="attSearchEmpty">No student matches that name or student number.</p>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914m">
<style>
.att-page .page-header { margin-bottom: 0.75rem; }
.att-filters {
    background: #fff;
    border: 1px solid #e7e5e4;
    border-radius: 12px;
    padding: 0.9rem 1rem 1rem;
    margin-bottom: 1rem;
}
.att-filters .form-label {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: #78716c;
    margin-bottom: 0.25rem;
}
.att-filters .form-control { min-height: 40px; border-radius: 8px; }
.att-locked { background: #fafaf9; color: #1c1917; pointer-events: none; }
.att-summary {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}
.att-summary div {
    background: #fff;
    border: 1px solid #e7e5e4;
    border-radius: 10px;
    padding: 0.65rem 0.75rem;
}
.att-summary span { display: block; font-size: 0.72rem; color: #78716c; font-weight: 650; }
.att-summary strong { font-size: 1.15rem; color: #1c1917; }
.att-unmarked { color: #78716c; font-size: 0.85rem; margin: -0.25rem 0 0.75rem; }
.att-toolbar {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    align-items: flex-end;
    flex-wrap: wrap;
    margin-bottom: 0.65rem;
}
.att-toolbar h5 { margin: 0; font-size: 1rem; font-weight: 700; color: #1c1917; }
.att-toolbar p { margin: 0.15rem 0 0; color: #78716c; font-size: 0.84rem; }
.att-table-wrap { max-height: 68vh; border: 1px solid #e7e5e4; border-radius: 12px; background: #fff; }
.att-table { margin: 0; }
.att-table thead th {
    position: sticky;
    top: 0;
    z-index: 1;
    background: #fafaf9;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #57534e;
    border-bottom: 1px solid #e7e5e4;
}
.att-table td { vertical-align: middle; }
.att-choices { display: flex; gap: 0.35rem; flex-wrap: wrap; }
.att-choice {
    margin: 0;
    min-width: 72px;
    text-align: center;
    padding: 0.35rem 0.55rem;
    border-radius: 999px;
    border: 1px solid #e7e5e4;
    background: #fff;
    font-size: 0.78rem;
    font-weight: 700;
    color: #57534e;
    cursor: pointer;
}
.att-choice input { position: absolute; opacity: 0; width: 1px; height: 1px; }
.att-choice.is-present:has(input:checked) { background: #ecfdf3; border-color: #86efac; color: #166534; }
.att-choice.is-absent:has(input:checked) { background: #fef2f2; border-color: #fca5a5; color: #b91c1c; }
.att-choice:has(input:focus-visible) { outline: 2px solid #3d5ee1; outline-offset: 1px; }
.att-note { min-width: 140px; border-radius: 8px; }
.att-time-label { font-weight: 700; color: #1c1917; white-space: nowrap; }
.att-link { font-weight: 700; color: #3d5ee1; }
.att-empty {
    background: #fff;
    border: 1px dashed #d6d3d1;
    border-radius: 12px;
    padding: 1.5rem 1rem;
    text-align: center;
}
.att-empty h5 { margin: 0 0 0.25rem; font-size: 1rem; }
.att-empty p { margin: 0; color: #78716c; }
@media (max-width: 991px) {
    .att-summary { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (max-width: 575px) {
    .att-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function () {
    function manilaStamp() {
        const parts = new Intl.DateTimeFormat('en-GB', {
            timeZone: 'Asia/Manila',
            hour: '2-digit',
            minute: '2-digit',
            hourCycle: 'h23'
        }).formatToParts(new Date());
        const hour = parts.find(function (part) { return part.type === 'hour'; }).value;
        const minute = parts.find(function (part) { return part.type === 'minute'; }).value;
        let hour12 = parseInt(hour, 10) % 12;
        if (hour12 === 0) {
            hour12 = 12;
        }
        return {
            value: hour + ':' + minute,
            label: hour12 + ':' + minute + ' ' + (parseInt(hour, 10) >= 12 ? 'PM' : 'AM')
        };
    }

    function stampPresent($row) {
        const stamp = manilaStamp();
        $row.find('.att-time').val(stamp.value);
        $row.find('.att-time-label').text(stamp.label);
    }

    function clearTime($row) {
        $row.find('.att-time').val('');
        $row.find('.att-time-label').text('—');
    }

    $('#search').on('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
        }
    });
    $('#search').on('input', function () {
        const needle = $(this).val().toLowerCase().trim();
        let shown = 0;
        const $rows = $('#attendanceForm tbody tr');
        $rows.each(function () {
            const haystack = String($(this).attr('data-find') || '');
            const match = needle === '' || haystack.indexOf(needle) !== -1;
            $(this).toggle(match);
            if (match) {
                shown++;
            }
        });
        $('#attSearchEmpty').toggleClass('d-none', !(needle !== '' && $rows.length > 0 && shown === 0));
    });
        $('#section_id, #subject_id').val('');
        $('#attFilterForm').submit();
    });
    $('#section_id').on('change', function () {
        $('#subject_id').val('');
        $('#attFilterForm').submit();
    });
    $('#subject_id').on('change', function () {
        if ($(this).val()) {
            $('#attFilterForm').submit();
        }
    });
    $('#attendanceForm').on('change', 'input[name$="[status]"]', function () {
        const $row = $(this).closest('tr');
        if (this.value === 'present' && this.checked) {
            stampPresent($row);
        }
        if (this.value === 'absent' && this.checked) {
            clearTime($row);
        }
    });
    $('#markAllPresent').on('click', function () {
        $('input[name$="[status]"][value="present"]').each(function () {
            const alreadyPresent = this.checked && $(this).closest('tr').find('.att-time').val();
            $(this).prop('checked', true);
            if (!alreadyPresent) {
                stampPresent($(this).closest('tr'));
            }
        });
    });
    $('#attendanceForm').on('submit', function (e) {
        const total = $('#attendanceForm tbody tr').length;
        const marked = $('input[name$="[status]"]:checked').length;
        if (total > 0 && marked < total) {
            e.preventDefault();
            alert('Mark every student, or use Mark all present.');
            return false;
        }
        $('#saveAttendance').prop('disabled', true).text('Saving...');
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/attendance/index.blade.php ENDPATH**/ ?>