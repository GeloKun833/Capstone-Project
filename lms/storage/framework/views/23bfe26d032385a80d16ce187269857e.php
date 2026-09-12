<?php $__env->startSection('content'); ?>

<?php
    $catalogEmpty = ($subjectsByGrade ?? collect())->isEmpty();
?>

<div class="page-wrapper">
    <div class="content container-fluid ams-unified">

        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="page-title mb-1">Classes &amp; Subjects</h3>
                    <p class="ams-unified-sub mb-0">
                        Manage the subject catalog by grade, then assign teachers to an entire grade at once.
                    </p>
                    <ul class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Classes &amp; Subjects</li>
                    </ul>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <form action="<?php echo e(route('class-subject.import-defaults')); ?>" method="POST" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-download me-1"></i> Import Defaults
                        </button>
                    </form>
                </div>
            </div>
        </div>

        
        <div class="card ams-panel mb-4">
            <div class="card-header ams-panel-header">
                <div>
                    <h5 class="mb-0">Subject Catalog by Grade</h5>
                    <small class="text-muted">Click a grade to view, add, or manage its subjects</small>
                </div>
            </div>
            <div class="card-body">
                <?php if($catalogEmpty): ?>
                    <div class="alert alert-warning mb-3">
                        <strong>No subjects yet.</strong>
                        Use <em>Import Defaults</em> or open a grade below to add subjects.
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button type="button"
                                class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#catalogModal"
                                data-grade="<?php echo e($grade); ?>"
                                data-count="0">
                                Open <?php echo e($grade); ?>

                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="row g-3" id="subjectCatalogGrid">
                        <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $gradeSubjects = $subjectsByGrade->get($grade, collect());
                                $assignedTeachers = $teachersByGrade[$grade] ?? collect();
                            ?>
                            <div class="col-6 col-md-4 col-xl-3">
                                <button type="button"
                                    class="ams-grade-tile w-100 text-start"
                                    data-bs-toggle="modal"
                                    data-bs-target="#catalogModal"
                                    data-grade="<?php echo e($grade); ?>"
                                    data-count="<?php echo e($gradeSubjects->count()); ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <span class="ams-grade-tile-title"><?php echo e($grade); ?></span>
                                        <span class="ams-count-badge <?php echo e($gradeSubjects->isEmpty() ? 'is-empty' : ''); ?>" data-role="subject-count">
                                            <?php echo e($gradeSubjects->count()); ?>

                                        </span>
                                    </div>
                                    <div class="ams-grade-tile-meta mt-2" data-role="subject-preview">
                                        <?php if($gradeSubjects->isEmpty()): ?>
                                            <span class="text-muted">No subjects — click to add</span>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <?php echo e($gradeSubjects->take(3)->pluck('subject_name')->implode(', ')); ?>

                                                <?php if($gradeSubjects->count() > 3): ?>…<?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if($assignedTeachers->isNotEmpty()): ?>
                                        <div class="ams-grade-tile-teachers mt-2">
                                            <i class="fas fa-chalkboard-teacher me-1"></i>
                                            <?php echo e($assignedTeachers->count()); ?> teacher(s)
                                        </div>
                                    <?php endif; ?>
                                </button>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="card ams-panel mb-4">
            <div class="card-header ams-panel-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0">Block Sections by Grade</h5>
                    <small class="text-muted">These power the enrollment form Block Section step. Click a grade to manage.</small>
                </div>
                <a href="<?php echo e(route('sections.index')); ?>" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-list me-1"></i> All Sections
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $gradeSections = ($sectionsByGrade ?? collect())->get($grade, collect()); ?>
                        <div class="col-6 col-md-4 col-xl-3">
                            <button type="button"
                                class="ams-grade-tile w-100 text-start"
                                data-bs-toggle="modal"
                                data-bs-target="#sectionModal"
                                data-grade="<?php echo e($grade); ?>"
                                data-count="<?php echo e($gradeSections->count()); ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="ams-grade-tile-title"><?php echo e($grade); ?></span>
                                    <span class="ams-count-badge ams-count-badge--section <?php echo e($gradeSections->isEmpty() ? 'is-empty' : ''); ?>" data-role="section-count">
                                        <?php echo e($gradeSections->count()); ?>

                                    </span>
                                </div>
                                <div class="ams-grade-tile-meta mt-2" data-role="section-preview">
                                    <?php if($gradeSections->isEmpty()): ?>
                                        <span class="text-muted">No block sections — click to add</span>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <?php echo e($gradeSections->take(3)->pluck('name')->implode(', ')); ?>

                                            <?php if($gradeSections->count() > 3): ?>…<?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </button>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>

        
        <div class="card ams-panel">
            <div class="card-header ams-panel-header">
                <div>
                    <h5 class="mb-0">Assign Teacher by Grade</h5>
                    <small class="text-muted">
                        Choosing a grade assigns the teacher to <strong>all subjects</strong> in that grade.
                    </small>
                </div>
                    </div>
                    <div class="card-body">
                <form method="POST" action="<?php echo e(route('class-subject.unified-management')); ?>" id="teacherGradeForm">
            <?php echo csrf_field(); ?>
                    <input type="hidden" name="operation_type" value="teacher_grade">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label ams-label" for="grade_level">Grade Level <span class="text-danger">*</span></label>
                            <select class="form-control <?php $__errorArgs = ['grade_level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="grade_level" id="grade_level" required>
                                <option value="">Select Grade</option>
                                <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($grade); ?>"
                                        data-subject-count="<?php echo e(($subjectsByGrade->get($grade) ?? collect())->count()); ?>"
                                        <?php echo e(old('grade_level') === $grade ? 'selected' : ''); ?>>
                                        <?php echo e($grade); ?>

                                        (<?php echo e(($subjectsByGrade->get($grade) ?? collect())->count()); ?> subjects)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                            <?php $__errorArgs = ['grade_level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="invalid-feedback d-block"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <div id="gradeSubjectsPreview" class="ams-preview mt-2 d-none"></div>
                </div>

                        <div class="col-md-4">
                            <label class="form-label ams-label" for="academic_year_id">Academic Year <span class="text-danger">*</span></label>
                            <select class="form-control" name="academic_year_id" id="academic_year_id" required>
                            <option value="">Select Academic Year</option>
                            <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($year->id); ?>" <?php echo e(old('academic_year_id') == $year->id ? 'selected' : ''); ?>>
                                    <?php echo e($year->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
            </div>

                        <div class="col-md-4">
                            <label class="form-label ams-label" for="semester_id">Semester <span class="text-danger">*</span></label>
                            <select class="form-control" name="semester_id" id="semester_id" required>
                            <option value="">Select Semester</option>
                            <?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($semester->id); ?>" <?php echo e(old('semester_id') == $semester->id ? 'selected' : ''); ?>>
                                    <?php echo e($semester->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                </div>

                        <div class="col-md-4">
                            <label class="form-label ams-label" for="section_id">Section <span class="text-muted">(optional)</span></label>
                            <select class="form-control" name="section_id" id="section_id">
                                <option value="">All / None</option>
                            <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($section->id); ?>"
                                        data-grade="<?php echo e($section->grade_level); ?>"
                                        <?php echo e(old('section_id') == $section->id ? 'selected' : ''); ?>>
                                        <?php echo e($section->name); ?> (<?php echo e($section->grade_level); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                            <small class="text-muted">If set, teacher is also linked to that section.</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <label class="form-label ams-label">Teachers <span class="text-danger">*</span></label>
                <?php if($teachers->isEmpty()): ?>
                        <div class="alert alert-warning mb-0">
                            No teachers available. Create teacher users in User Management first.
                    </div>
                <?php else: ?>
                        <div class="row g-2">
                        <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-4 col-sm-6">
                                    <label class="ams-teacher-card">
                                        <input class="form-check-input me-2" type="checkbox" name="teacher_ids[]"
                                            value="<?php echo e($teacher->id); ?>"
                                            <?php echo e(in_array($teacher->id, old('teacher_ids', [])) ? 'checked' : ''); ?>>
                                        <span>
                                            <strong><?php echo e($teacher->full_name ?: ($teacher->user->name ?? 'Unknown')); ?></strong>
                                            <br><small class="text-muted"><?php echo e($teacher->user_id ?? ''); ?></small>
                                        </span>
                                    </label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                        <?php $__errorArgs = ['teacher_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="alert alert-danger mt-3 mb-0"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php endif; ?>

            <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary" id="submitGradeAssign" <?php if($teachers->isEmpty()): echo 'disabled'; endif; ?>>
                            <i class="fas fa-user-check me-1"></i> Assign Teacher(s) to Grade
                </button>
            </div>
        </form>
                    </div>
                </div>
                    </div>
                </div>


<div class="modal fade" id="catalogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content ams-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="catalogModalTitle">Grade Subjects</h5>
                    <small class="text-muted" id="catalogModalSub">Subjects for enrollment &amp; teaching</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="catalogSubjectsList" class="mb-4"></div>

                <div class="ams-modal-add">
                    <h6 class="mb-3"><i class="fas fa-plus-circle me-1 text-primary"></i> Add Subject to this Grade</h6>
                    <form method="POST" action="<?php echo e(route('class-subject.quick-add-subject')); ?>" id="quickAddSubjectForm">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="grade_level" id="quickAddGrade" value="">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-8">
                                <label class="form-label ams-label">Subject Name</label>
                                <input type="text" class="form-control" name="subject_name" id="quickAddName"
                                    placeholder="e.g. Math" required autocomplete="off">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100" id="quickAddSubjectBtn">
                                    <i class="fas fa-plus me-1"></i> Add
                                </button>
                            </div>
                        </div>
                        <div id="quickAddSubjectMsg" class="small mt-2"></div>
                    </form>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <form action="<?php echo e(route('class-subject.import-defaults')); ?>" method="POST" id="importGradeForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="grade_level" id="importGradeLevel" value="">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-download me-1"></i> Import Defaults for Grade
                    </button>
                </form>
                <a href="#" id="catalogManageLink" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i> Full Subject List
                </a>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="sectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content ams-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="sectionModalTitle">Block Sections</h5>
                    <small class="text-muted" id="sectionModalSub">Shown on enrollment when this grade is selected</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="sectionList" class="mb-4"></div>
                <div class="ams-modal-add">
                    <h6 class="mb-3"><i class="fas fa-plus-circle me-1 text-primary"></i> Add Block Section</h6>
                    <form method="POST" action="<?php echo e(route('class-subject.quick-add-section')); ?>" id="quickAddSectionForm">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="grade_level" id="quickSectionGrade" value="">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label ams-label">Section Name</label>
                                <input type="text" class="form-control" name="name" id="quickSectionName"
                                    placeholder="e.g. Pasteur" required autocomplete="off">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label ams-label">Capacity</label>
                                <input type="number" class="form-control" name="capacity" value="25" min="1">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100" id="quickAddSectionBtn">
                                    <i class="fas fa-plus me-1"></i> Add Section
                                </button>
                            </div>
                        </div>
                        <div id="quickAddSectionMsg" class="small mt-2"></div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <a href="<?php echo e(route('sections.index')); ?>" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i> Manage All Sections
                </a>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .ams-unified {
        --ams-blue: #3d5ee1;
        --ams-ink: #111827;
        --ams-muted: #6b7280;
        --ams-line: #e5e7eb;
        --ams-soft: #f8fafc;
    }
    .ams-unified-sub { color: var(--ams-muted); }
    .ams-panel {
        border: 1px solid var(--ams-line);
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }
    .ams-panel-header {
        background: linear-gradient(180deg, #fff, var(--ams-soft));
        border-bottom: 1px solid var(--ams-line);
        padding: 1rem 1.25rem;
    }
    .ams-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .ams-grade-tile {
        border: 1px solid var(--ams-line);
        background: #fff;
        border-radius: 14px;
        padding: 1rem;
        min-height: 118px;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        cursor: pointer;
    }
    .ams-grade-tile:hover {
        transform: translateY(-2px);
        border-color: #c7d2fe;
        box-shadow: 0 10px 22px rgba(61, 94, 225, 0.12);
    }
    .ams-grade-tile-title {
        font-weight: 700;
        color: var(--ams-ink);
        font-size: 1rem;
    }
    .ams-count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.75rem;
        height: 1.75rem;
        padding: 0 0.45rem;
        border-radius: 8px;
        background: #1e3a8a !important;
        color: #fff !important;
        font-size: 0.85rem;
        font-weight: 700;
        line-height: 1;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.2);
    }
    .ams-count-badge--section {
        background: #065f46 !important;
    }
    .ams-count-badge.is-empty {
        background: #64748b !important;
    }
    .ams-grade-tile-meta { font-size: 0.8rem; line-height: 1.35; }
    .ams-grade-tile-teachers {
        font-size: 0.75rem;
        color: var(--ams-blue);
        font-weight: 600;
    }
    .ams-teacher-card {
        display: flex;
        align-items: flex-start;
        gap: 0.35rem;
        border: 1px solid var(--ams-line);
        border-radius: 12px;
        padding: 0.75rem 0.85rem;
        background: #fff;
        cursor: pointer;
        height: 100%;
        transition: border-color .15s ease, background .15s ease;
    }
    .ams-teacher-card:hover { border-color: #c7d2fe; background: #f8faff; }
    .ams-preview {
        background: #f0f4ff;
        border: 1px dashed #c7d2fe;
        border-radius: 10px;
        padding: 0.65rem 0.85rem;
        font-size: 0.85rem;
        color: #3730a3;
    }
    .ams-modal {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
    }
    #catalogModal.modal.fade .modal-dialog {
        transform: translateY(22px) scale(.96);
        opacity: 0;
        transition: transform .28s cubic-bezier(.22,1,.36,1), opacity .28s ease;
    }
    #catalogModal.modal.show .modal-dialog {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
    .ams-modal-add {
        background: var(--ams-soft);
        border: 1px solid var(--ams-line);
        border-radius: 12px;
        padding: 1rem;
    }
    .ams-subject-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #eef2ff;
        color: #312e81;
        border-radius: 999px;
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
        font-weight: 600;
        margin: 0 0.35rem 0.5rem 0;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    let subjectsByGrade = <?php echo json_encode($subjectsByGradeJson ?? [], 15, 512) ?>;
    let sectionsByGrade = <?php echo json_encode($sectionsByGradeJson ?? [], 15, 512) ?>;
    const listUrlBase = <?php echo json_encode(url('subject/list/page'), 15, 512) ?>;
    const csrfToken = <?php echo json_encode(csrf_token(), 15, 512) ?>;

    function renderSubjectChips(grade, subjects) {
        const list = document.getElementById('catalogSubjectsList');
        if (!list) return;
        if (!subjects || !subjects.length) {
            list.innerHTML = '<div class="alert alert-light border mb-0">No subjects in this grade yet. Add one below or import defaults.</div>';
            return;
        }
        list.innerHTML = subjects.map(function (s) {
            return '<span class="ams-subject-chip"><i class="fas fa-book"></i> ' + (s.name || s.subject_name) + '</span>';
        }).join('');
    }

    function renderSectionChips(grade, sections) {
        const list = document.getElementById('sectionList');
        if (!list) return;
        if (!sections || !sections.length) {
            list.innerHTML = '<div class="alert alert-light border mb-0">No sections for this grade. Add one below — it will appear on the enrollment Block Section step.</div>';
            return;
        }
        list.innerHTML = sections.map(function (s) {
            const adviser = s.adviser ? (' · ' + s.adviser) : '';
            return '<span class="ams-subject-chip"><i class="fas fa-door-open"></i> ' + s.name +
                ' <small style="font-weight:500;opacity:.75">(cap ' + (s.capacity || 25) + adviser + ')</small></span>';
        }).join('');
    }

    function updateSubjectTile(grade, subjects) {
        const tile = document.querySelector('.ams-grade-tile[data-grade="' + grade + '"][data-bs-target="#catalogModal"]');
        if (!tile) return;
        const count = (subjects || []).length;
        tile.setAttribute('data-count', String(count));
        const badge = tile.querySelector('[data-role="subject-count"]');
        if (badge) {
            badge.textContent = String(count);
            badge.classList.toggle('is-empty', count === 0);
        }
        const preview = tile.querySelector('[data-role="subject-preview"]');
        if (preview) {
            if (!count) {
                preview.innerHTML = '<span class="text-muted">No subjects — click to add</span>';
            } else {
                const names = subjects.slice(0, 3).map(function (s) { return s.name || s.subject_name; });
                preview.innerHTML = '<span class="text-muted">' + names.join(', ') + (count > 3 ? '…' : '') + '</span>';
            }
        }
    }

    function updateSectionTile(grade, sections) {
        const tile = document.querySelector('.ams-grade-tile[data-grade="' + grade + '"][data-bs-target="#sectionModal"]');
        if (!tile) return;
        const count = (sections || []).length;
        tile.setAttribute('data-count', String(count));
        const badge = tile.querySelector('[data-role="section-count"]');
        if (badge) {
            badge.textContent = String(count);
            badge.classList.toggle('is-empty', count === 0);
        }
        const preview = tile.querySelector('[data-role="section-preview"]');
        if (preview) {
            if (!count) {
                preview.innerHTML = '<span class="text-muted">No block sections — click to add</span>';
            } else {
                const names = sections.slice(0, 3).map(function (s) { return s.name; });
                preview.innerHTML = '<span class="text-muted">' + names.join(', ') + (count > 3 ? '…' : '') + '</span>';
            }
        }
    }

    function refreshSubjectsFromApi(grade) {
        return fetch('/enrollment-portal/get-subjects/' + encodeURIComponent(grade) + '?_=' + Date.now(), {
            cache: 'no-store',
            headers: { 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                const subjects = (data.subjects || []).map(function (s) {
                    return { id: s.id, name: s.name, class: s.class };
                });
                subjectsByGrade[grade] = subjects;
                renderSubjectChips(grade, subjects);
                updateSubjectTile(grade, subjects);
                document.getElementById('catalogModalSub').textContent = subjects.length
                    ? (subjects.length + ' subject(s) — live from database / enrollment')
                    : 'No subjects yet for ' + grade;
                return subjects;
            });
    }

    function refreshSectionsFromApi(grade) {
        return fetch('/enrollment-portal/get-sections/' + encodeURIComponent(grade) + '?_=' + Date.now(), {
            cache: 'no-store',
            headers: { 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                const sections = (data.sections || []).map(function (s) {
                    return {
                        id: s.id,
                        name: s.name,
                        capacity: s.capacity,
                        adviser: s.adviser
                    };
                });
                sectionsByGrade[grade] = sections;
                renderSectionChips(grade, sections);
                updateSectionTile(grade, sections);
                document.getElementById('sectionModalSub').textContent = sections.length
                    ? (sections.length + ' section(s) — live from database / enrollment')
                    : 'No block sections yet for ' + grade;
                return sections;
            });
    }

    const catalogModal = document.getElementById('catalogModal');
    if (catalogModal) {
        catalogModal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;
            const grade = btn.getAttribute('data-grade') || '';

            document.getElementById('catalogModalTitle').textContent = grade + ' Subjects';
            document.getElementById('quickAddGrade').value = grade;
            document.getElementById('importGradeLevel').value = grade;
            document.getElementById('catalogManageLink').href = listUrlBase + '?search_class=' + encodeURIComponent(grade);
            document.getElementById('quickAddName').value = '';
            document.getElementById('quickAddSubjectMsg').textContent = '';
            document.getElementById('catalogSubjectsList').innerHTML = '<div class="text-muted py-2"><i class="fas fa-spinner fa-spin me-1"></i> Loading live subjects...</div>';

            refreshSubjectsFromApi(grade).catch(function () {
                renderSubjectChips(grade, subjectsByGrade[grade] || []);
            });
        });
    }

    const sectionModal = document.getElementById('sectionModal');
    if (sectionModal) {
        sectionModal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;
            const grade = btn.getAttribute('data-grade') || '';

            document.getElementById('sectionModalTitle').textContent = grade + ' Block Sections';
            document.getElementById('quickSectionGrade').value = grade;
            document.getElementById('quickSectionName').value = '';
            document.getElementById('quickAddSectionMsg').textContent = '';
            document.getElementById('sectionList').innerHTML = '<div class="text-muted py-2"><i class="fas fa-spinner fa-spin me-1"></i> Loading live sections...</div>';

            refreshSectionsFromApi(grade).catch(function () {
                renderSectionChips(grade, sectionsByGrade[grade] || []);
            });
        });
    }

    $('#quickAddSubjectForm').on('submit', function (e) {
        e.preventDefault();
        const grade = $('#quickAddGrade').val();
        const name = ($('#quickAddName').val() || '').trim();
        const $msg = $('#quickAddSubjectMsg');
        const $btn = $('#quickAddSubjectBtn');

        if (!grade || !name) {
            $msg.html('<span class="text-danger">Grade and subject name are required.</span>');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $msg.html('<span class="text-muted">Saving...</span>');

        $.ajax({
            url: <?php echo json_encode(route('class-subject.quick-add-subject'), 15, 512) ?>,
            method: 'POST',
            data: {
                _token: csrfToken,
                grade_level: grade,
                subject_name: name
            },
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).done(function (res) {
            $msg.html('<span class="text-success">' + (res.message || 'Added.') + ' Visible on enrollment now.</span>');
            $('#quickAddName').val('');
            // Wait for live fetch so tile count/preview update without page refresh
            refreshSubjectsFromApi(grade).catch(function () {
                const list = subjectsByGrade[grade] || [];
                if (res.subject) {
                    list.push({ id: res.subject.id, name: res.subject.subject_name || res.subject.name, class: grade });
                    subjectsByGrade[grade] = list;
                }
                renderSubjectChips(grade, subjectsByGrade[grade] || []);
                updateSubjectTile(grade, subjectsByGrade[grade] || []);
            });
        }).fail(function (xhr) {
            const msg = (xhr.responseJSON && (xhr.responseJSON.message || (xhr.responseJSON.errors && Object.values(xhr.responseJSON.errors)[0][0]))) || 'Failed to add subject.';
            $msg.html('<span class="text-danger">' + msg + '</span>');
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i> Add');
        });
    });

    $('#quickAddSectionForm').on('submit', function (e) {
        e.preventDefault();
        const grade = $('#quickSectionGrade').val();
        const name = ($('#quickSectionName').val() || '').trim();
        const capacity = $(this).find('[name="capacity"]').val() || 25;
        const $msg = $('#quickAddSectionMsg');
        const $btn = $('#quickAddSectionBtn');

        if (!grade || !name) {
            $msg.html('<span class="text-danger">Grade and section name are required.</span>');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $msg.html('<span class="text-muted">Saving...</span>');

        $.ajax({
            url: <?php echo json_encode(route('class-subject.quick-add-section'), 15, 512) ?>,
            method: 'POST',
            data: {
                _token: csrfToken,
                grade_level: grade,
                name: name,
                capacity: capacity
            },
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).done(function (res) {
            $msg.html('<span class="text-success">' + (res.message || 'Added.') + ' Visible on enrollment Block Section now.</span>');
            $('#quickSectionName').val('');
            refreshSectionsFromApi(grade).catch(function () {
                const list = sectionsByGrade[grade] || [];
                if (res.section) {
                    list.push(res.section);
                    sectionsByGrade[grade] = list;
                }
                renderSectionChips(grade, sectionsByGrade[grade] || []);
                updateSectionTile(grade, sectionsByGrade[grade] || []);
            });
        }).fail(function (xhr) {
            const msg = (xhr.responseJSON && (xhr.responseJSON.message || (xhr.responseJSON.errors && Object.values(xhr.responseJSON.errors)[0][0]))) || 'Failed to add section.';
            $msg.html('<span class="text-danger">' + msg + '</span>');
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i> Add Section');
        });
    });

    const gradeSelect = document.getElementById('grade_level');
    const preview = document.getElementById('gradeSubjectsPreview');
    const sectionSelect = document.getElementById('section_id');

    function refreshGradePreview() {
        if (!gradeSelect || !preview) return;
        const grade = gradeSelect.value;
        const subjects = subjectsByGrade[grade] || [];
        if (!grade) {
            preview.classList.add('d-none');
            preview.innerHTML = '';
            filterSections('');
            return;
        }
        if (!subjects.length) {
            preview.classList.remove('d-none');
            preview.innerHTML = '<strong>' + grade + '</strong> has no subjects yet. Open the catalog and add some first.';
        } else {
            preview.classList.remove('d-none');
            preview.innerHTML = '<strong>Will assign:</strong> ' + subjects.map(function (s) { return s.name; }).join(', ');
        }
        filterSections(grade);
    }

    function filterSections(grade) {
        if (!sectionSelect) return;
        Array.from(sectionSelect.options).forEach(function (opt, idx) {
            if (idx === 0) return;
            const g = opt.getAttribute('data-grade') || '';
            const match = !grade || g === grade;
            opt.hidden = !match;
            if (!match && opt.selected) {
                opt.selected = false;
                sectionSelect.value = '';
            }
        });
    }

    if (gradeSelect) {
        gradeSelect.addEventListener('change', refreshGradePreview);
        refreshGradePreview();
    }

    $('#teacherGradeForm').on('submit', function (e) {
        if ($('input[name="teacher_ids[]"]:checked').length === 0) {
            e.preventDefault();
            alert('Please select at least one teacher.');
            return false;
        }
        const grade = $('#grade_level').val();
        const subjects = subjectsByGrade[grade] || [];
        if (!subjects.length) {
            e.preventDefault();
            alert('This grade has no subjects yet. Add subjects in the catalog first.');
            return false;
        }
        $('#submitGradeAssign').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Assigning...');
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\class-subject\unified-management.blade.php ENDPATH**/ ?>