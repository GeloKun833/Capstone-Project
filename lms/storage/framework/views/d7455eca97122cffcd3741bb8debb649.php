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
                                    <div class="ams-grade-tile-teachers mt-2 <?php echo e($assignedTeachers->isEmpty() ? 'd-none' : ''); ?>" data-role="teacher-count">
                                        <i class="fas fa-chalkboard-teacher me-1"></i>
                                        <?php echo e($assignedTeachers->count()); ?> teacher(s)
                                    </div>
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
                        <?php
                            $gradeSections = ($sectionsByGrade ?? collect())->get($grade, collect());
                            $sectionTeachers = $gradeSections->flatMap(function ($section) {
                                $teachers = $section->teachers ?? collect();
                                if ($section->adviser) {
                                    $teachers = $teachers->push($section->adviser);
                                }
                                return $teachers;
                            })->unique('id')->values();
                        ?>
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
                                <div class="ams-grade-tile-teachers mt-2 <?php echo e($sectionTeachers->isEmpty() ? 'd-none' : ''); ?>" data-role="teacher-count">
                                    <i class="fas fa-chalkboard-teacher me-1"></i>
                                    <?php echo e($sectionTeachers->count()); ?> teacher(s)
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
                    <h5 class="mb-0">Manage Teachers</h5>
                    <small class="text-muted">
                        Click a teacher to view details, then assign or unassign to a grade or section.
                    </small>
                </div>
            </div>
            <div class="card-body">
                <?php if($teachers->isEmpty()): ?>
                    <div class="alert alert-warning mb-0">
                        No teachers available. Create teacher users in User Management first.
                    </div>
                <?php else: ?>
                    <div class="row g-3" id="teacherPreviewGrid">
                        <?php $__currentLoopData = $teachers->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $tName = $teacher->full_name ?: ($teacher->user->name ?? 'Unknown');
                                $tPhoto = \App\Support\AvatarUploader::url($teacher->user->avatar ?? $teacher->avatar);
                            ?>
                            <div class="col-6 col-md-3">
                                <button type="button" class="ams-teacher-pick js-open-teacher" data-teacher-id="<?php echo e($teacher->id); ?>">
                                    <span class="ams-teacher-pick-photo">
                                        <img src="<?php echo e($tPhoto); ?>" alt="<?php echo e($tName); ?>" onerror="this.onerror=null;this.src='<?php echo e(asset('images/photo_defaults.jpg')); ?>';">
                                    </span>
                                    <span class="ams-teacher-pick-name"><?php echo e($tName); ?></span>
                                </button>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php if($teachers->count() > 4): ?>
                        <button type="button" class="ams-teacher-pick ams-teacher-pick--more ams-teacher-pick--all mt-3" data-bs-toggle="modal" data-bs-target="#allTeachersModal">
                            <span class="ams-teacher-more-icon"><i class="fas fa-users"></i></span>
                            <span>
                                <span class="ams-teacher-pick-name d-block">View all teachers</span>
                                <small class="text-muted"><?php echo e($teachers->count()); ?> created · +<?php echo e($teachers->count() - 4); ?> more</small>
                            </span>
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
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


<div class="modal fade" id="deleteSubjectConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ams-modal">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Delete subject?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-0" id="deleteSubjectConfirmText">This also removes it from student class lists.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteSubjectConfirmBtn">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteSubjectResultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ams-modal text-center">
            <div class="modal-body py-4 px-4">
                <div class="ams-result-icon mb-3" id="deleteSubjectResultIcon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h5 class="mb-2" id="deleteSubjectResultTitle">Success</h5>
                <p class="text-muted mb-0" id="deleteSubjectResultMessage">Subject deleted.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="unassignTeacherConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ams-modal">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Unassign teacher(s)?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-0" id="unassignTeacherConfirmText">
                    Unassign selected teacher(s) from all subjects in this grade?
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="unassignTeacherConfirmBtn">
                    <i class="fas fa-user-minus me-1"></i> Unassign
                </button>
            </div>
        </div>
    </div>
</div>

<?php if(($teachers ?? collect())->isNotEmpty()): ?>
<div class="modal fade" id="allTeachersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content ams-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title mb-0">All created teachers</h5>
                    <small class="text-muted"><?php echo e($teachers->count()); ?> teacher<?php echo e($teachers->count() === 1 ? '' : 's'); ?> — tap a card to view details</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="search" id="allTeachersSearch" class="form-control" placeholder="Search teacher name...">
                </div>
                <div class="row g-3" id="allTeachersGrid">
                    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $tName = $teacher->full_name ?: ($teacher->user->name ?? 'Unknown');
                            $tPhoto = \App\Support\AvatarUploader::url($teacher->user->avatar ?? $teacher->avatar);
                        ?>
                        <div class="col-6 col-md-4 col-lg-3 js-teacher-modal-item" data-name="<?php echo e($tName); ?>">
                            <button type="button" class="ams-teacher-pick js-open-teacher" data-teacher-id="<?php echo e($teacher->id); ?>" data-from-all="1">
                                <span class="ams-teacher-pick-photo">
                                    <img src="<?php echo e($tPhoto); ?>" alt="<?php echo e($tName); ?>" onerror="this.onerror=null;this.src='<?php echo e(asset('images/photo_defaults.jpg')); ?>';">
                                </span>
                                <span class="ams-teacher-pick-name"><?php echo e($tName); ?></span>
                            </button>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="teacherDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content ams-modal">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title mb-0">Teacher details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo e(route('class-subject.unified-management')); ?>" id="teacherGradeForm">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="operation_type" id="teacherGradeOperation" value="teacher_grade">
                <input type="hidden" name="teacher_ids[]" id="modalTeacherId" value="<?php echo e(old('teacher_ids.0')); ?>">

                <div class="modal-body">
                    <div class="ams-teacher-profile mb-4">
                        <img id="tdPhoto" src="<?php echo e(asset('images/photo_defaults.jpg')); ?>" alt="">
                        <div>
                            <h5 class="mb-1" id="tdName">Teacher</h5>
                            <div class="text-muted small" id="tdEmail"></div>
                            <div class="d-flex flex-wrap gap-2 mt-2" id="tdMeta"></div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="ams-teacher-detail">
                                <span>Qualification</span>
                                <strong id="tdQualification">—</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ams-teacher-detail">
                                <span>Experience</span>
                                <strong id="tdExperience">—</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ams-teacher-detail">
                                <span>Assigned grades</span>
                                <strong id="tdGrades">None yet</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ams-teacher-detail">
                                <span>Assigned sections</span>
                                <strong id="tdSections">None yet</strong>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <h6 class="mb-3">Assign or unassign</h6>
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
                        <div class="col-md-6">
                            <label class="form-label ams-label" for="section_id">Section <span class="text-muted" id="sectionRequiredMark">(for section actions)</span></label>
                            <select class="form-control" name="section_id" id="section_id">
                                <option value="" id="sectionNoneOption">Select Section</option>
                                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($section->id); ?>"
                                        data-grade="<?php echo e($section->grade_level); ?>"
                                        <?php echo e(old('section_id') == $section->id ? 'selected' : ''); ?>>
                                        <?php echo e($section->name); ?> (<?php echo e($section->grade_level); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <small class="text-muted" id="sectionHelpText">Required when assigning or unassigning a section.</small>
                        </div>
                        <div class="col-md-6" id="sectionAdviserWrap">
                            <div class="form-check mt-4 pt-1">
                                <input class="form-check-input" type="checkbox" name="set_as_adviser" id="setAsAdviser" value="1" checked>
                                <label class="form-check-label" for="setAsAdviser">Also set as section adviser (homeroom)</label>
                            </div>
                        </div>
                    </div>
                    <div id="gradeSubjectsPreview" class="ams-preview mt-3 d-none"></div>
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
                </div>
                <div class="modal-footer flex-column align-items-stretch border-0 pt-0">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary w-100 js-teacher-action" data-op="teacher_grade">
                                <i class="fas fa-user-check me-1"></i> Assign Teacher to Grade
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-outline-danger w-100 js-teacher-action" data-op="teacher_grade_unassign">
                                <i class="fas fa-user-minus me-1"></i> Unassign from Grade
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary w-100 js-teacher-action" data-op="teacher_section">
                                <i class="fas fa-chalkboard-teacher me-1"></i> Assign Teacher to Section
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-outline-danger w-100 js-teacher-action" data-op="teacher_section_unassign">
                                <i class="fas fa-user-minus me-1"></i> Unassign from Section
                            </button>
                        </div>
                    </div>
                </div>
            </form>
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
    .ams-teacher-pick {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        text-align: center;
        gap: 0.55rem;
        width: 100%;
        min-height: 148px;
        border: 1px solid var(--ams-line);
        border-radius: 14px;
        padding: 1rem 0.7rem 0.85rem;
        background: #fff;
        cursor: pointer;
        margin: 0;
        font-family: inherit;
        color: inherit;
        transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
    }
    .ams-teacher-pick:hover {
        border-color: #c7d2fe;
        background: #f8faff;
    }
    .ams-teacher-pick-photo {
        position: relative;
        width: 64px;
        height: 64px;
        flex-shrink: 0;
    }
    .ams-teacher-pick-photo img {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        object-fit: cover;
        display: block;
        background: #e5e7eb;
    }
    .ams-teacher-pick-name {
        font-weight: 650;
        font-size: 0.86rem;
        color: var(--ams-ink);
        line-height: 1.3;
        word-break: break-word;
    }
    .ams-teacher-pick--more {
        border-style: dashed;
        background: var(--ams-soft);
        color: inherit;
    }
    .ams-teacher-pick--all {
        flex-direction: row;
        min-height: 0;
        justify-content: center;
        gap: 0.85rem;
        padding: 0.85rem 1rem;
        text-align: left;
    }
    .ams-teacher-pick--all .ams-teacher-more-icon {
        width: 44px;
        height: 44px;
        font-size: 1rem;
    }
    .ams-teacher-more-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e0e7ff;
        color: var(--ams-blue);
        font-size: 1.25rem;
    }
    .ams-teacher-profile {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.85rem 1rem;
        background: var(--ams-soft);
        border: 1px solid var(--ams-line);
        border-radius: 14px;
    }
    .ams-teacher-profile img {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        object-fit: cover;
        background: #e5e7eb;
        flex-shrink: 0;
    }
    .ams-teacher-detail {
        border: 1px solid var(--ams-line);
        border-radius: 12px;
        padding: 0.7rem 0.85rem;
        background: #fff;
        min-height: 100%;
    }
    .ams-teacher-detail span {
        display: block;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--ams-muted);
        margin-bottom: 0.2rem;
    }
    .ams-teacher-detail strong {
        font-size: 0.9rem;
        color: var(--ams-ink);
        font-weight: 650;
        word-break: break-word;
    }
    .ams-teacher-chip {
        display: inline-flex;
        align-items: center;
        background: #eef2ff;
        color: #3730a3;
        border-radius: 999px;
        padding: 0.15rem 0.55rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
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
    #unassignTeacherConfirmModal,
    #deleteSubjectResultModal {
        z-index: 1080;
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
    .ams-subject-chip .ams-chip-del {
        border: 0;
        background: transparent;
        color: #b91c1c;
        padding: 0 0 0 0.25rem;
        line-height: 1;
        cursor: pointer;
    }
    .ams-subject-chip .ams-chip-del:hover { color: #7f1d1d; }
    .ams-mode-toggle {
        display: inline-flex;
        padding: 0.2rem;
        border-radius: 999px;
        background: #f1f5f9;
        border: 1px solid var(--ams-line);
        gap: 0.15rem;
    }
    .ams-mode-btn {
        border: 0;
        background: transparent;
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.45rem 0.9rem;
        border-radius: 999px;
        cursor: pointer;
        transition: background .15s ease, color .15s ease, box-shadow .15s ease;
    }
    .ams-mode-btn.is-active {
        background: #fff;
        color: #1e3a8a;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.12);
    }
    .ams-mode-btn[data-mode="unassign"].is-active {
        color: #b91c1c;
    }
    .ams-result-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }
    .ams-result-icon.is-success {
        background: #ecfdf5;
        color: #059669;
    }
    .ams-result-icon.is-error {
        background: #fef2f2;
        color: #dc2626;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    let subjectsByGrade = <?php echo json_encode($subjectsByGradeJson ?? [], 15, 512) ?>;
    let sectionsByGrade = <?php echo json_encode($sectionsByGradeJson ?? [], 15, 512) ?>;
    const teachersById = {};
    (<?php echo json_encode($teachersJson ?? [], 15, 512) ?>).forEach(function (t) {
        teachersById[String(t.id)] = t;
    });
    const defaultPhoto = <?php echo json_encode(asset('images/photo_defaults.jpg'), 15, 512) ?>;
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
            const id = s.id;
            const name = s.name || s.subject_name || 'Subject';
            return '<span class="ams-subject-chip" data-subject-id="' + id + '">' +
                '<i class="fas fa-book"></i> ' + name +
                '<button type="button" class="ams-chip-del" title="Remove subject" data-id="' + id + '" data-name="' + name.replace(/"/g, '&quot;') + '" data-grade="' + grade + '">' +
                '<i class="fas fa-times"></i></button></span>';
        }).join('');
    }

    let pendingDelete = null;

    function showDeleteResultModal(ok, title, message) {
        const icon = document.getElementById('deleteSubjectResultIcon');
        const titleEl = document.getElementById('deleteSubjectResultTitle');
        const msgEl = document.getElementById('deleteSubjectResultMessage');
        if (!icon || !titleEl || !msgEl) return;

        icon.className = 'ams-result-icon mb-3 ' + (ok ? 'is-success' : 'is-error');
        icon.innerHTML = ok
            ? '<i class="fas fa-check-circle"></i>'
            : '<i class="fas fa-times-circle"></i>';
        titleEl.textContent = title;
        msgEl.textContent = message;

        const el = document.getElementById('deleteSubjectResultModal');
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        } else if (window.$) {
            $(el).modal('show');
        }
    }

    function runSubjectDelete(id, name, grade, triggerBtn) {
        if (triggerBtn) triggerBtn.disabled = true;
        const confirmBtn = document.getElementById('deleteSubjectConfirmBtn');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';
        }

        $.ajax({
            url: <?php echo json_encode(route('class-subject.quick-delete-subject'), 15, 512) ?>,
            method: 'POST',
            data: { _token: csrfToken, subject_id: id },
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).done(function (res) {
            const confirmEl = document.getElementById('deleteSubjectConfirmModal');
            if (confirmEl && window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(confirmEl).hide();
            } else if (window.$) {
                $(confirmEl).modal('hide');
            }

            refreshSubjectsFromApi(grade).catch(function () {
                subjectsByGrade[grade] = (subjectsByGrade[grade] || []).filter(function (s) { return String(s.id) !== String(id); });
                renderSubjectChips(grade, subjectsByGrade[grade]);
                updateSubjectTile(grade, subjectsByGrade[grade]);
            });

            showDeleteResultModal(
                true,
                'Deleted successfully',
                res.message || ('"' + name + '" was removed from ' + grade + ' and student class lists.')
            );
        }).fail(function (xhr) {
            const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to delete subject.';
            const confirmEl = document.getElementById('deleteSubjectConfirmModal');
            if (confirmEl && window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(confirmEl).hide();
            } else if (window.$) {
                $(confirmEl).modal('hide');
            }
            showDeleteResultModal(false, 'Delete failed', msg);
            if (triggerBtn) triggerBtn.disabled = false;
        }).always(function () {
            pendingDelete = null;
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-trash me-1"></i> Delete';
            }
        });
    }

    document.getElementById('catalogSubjectsList')?.addEventListener('click', function (e) {
        const btn = e.target.closest('.ams-chip-del');
        if (!btn) return;
        const id = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name') || 'this subject';
        const grade = btn.getAttribute('data-grade') || '';
        if (!id) return;

        pendingDelete = { id: id, name: name, grade: grade, btn: btn };
        const text = document.getElementById('deleteSubjectConfirmText');
        if (text) {
            text.textContent = 'Delete "' + name + '" from ' + grade + '? This also removes it from student class lists.';
        }
        const el = document.getElementById('deleteSubjectConfirmModal');
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        } else if (window.$) {
            $(el).modal('show');
        }
    });

    document.getElementById('deleteSubjectConfirmBtn')?.addEventListener('click', function () {
        if (!pendingDelete) return;
        runSubjectDelete(pendingDelete.id, pendingDelete.name, pendingDelete.grade, pendingDelete.btn);
    });

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

    function setTileTeacherCount(tile, teachers) {
        if (!tile) return;
        let line = tile.querySelector('[data-role="teacher-count"]');
        if (!line) {
            line = document.createElement('div');
            line.className = 'ams-grade-tile-teachers mt-2';
            line.setAttribute('data-role', 'teacher-count');
            tile.appendChild(line);
        }
        const count = (teachers || []).length;
        if (!count) {
            line.classList.add('d-none');
            line.innerHTML = '';
            return;
        }
        line.classList.remove('d-none');
        line.innerHTML = '<i class="fas fa-chalkboard-teacher me-1"></i> ' + count + ' teacher(s)';
    }

    function applyAssignmentResult(res) {
        const grade = res && res.grade_level;
        if (!grade) return;

        setTileTeacherCount(
            document.querySelector('.ams-grade-tile[data-grade="' + grade + '"][data-bs-target="#catalogModal"]'),
            res.teachers || []
        );
        setTileTeacherCount(
            document.querySelector('.ams-grade-tile[data-grade="' + grade + '"][data-bs-target="#sectionModal"]'),
            res.section_teachers || []
        );

        if (res.sections) {
            applySectionsForGrade(grade, res.sections);
            setTileTeacherCount(
                document.querySelector('.ams-grade-tile[data-grade="' + grade + '"][data-bs-target="#sectionModal"]'),
                res.section_teachers || []
            );
        }

        const gradeIds = (res.teachers || []).map(function (t) { return String(t.id); });
        Object.keys(teachersById).forEach(function (id) {
            const teacher = teachersById[id];
            teacher.grades = (teacher.grades || []).filter(function (g) { return g !== grade; });
            if (gradeIds.indexOf(id) !== -1) {
                teacher.grades.push(grade);
            }
        });

        const sectionNames = (res.sections || []).map(function (s) {
            return (s.name || '') + (s.grade_level ? ' (' + s.grade_level + ')' : '');
        });
        const sectionTeacherIds = (res.section_teachers || []).map(function (t) { return String(t.id); });
        Object.keys(teachersById).forEach(function (id) {
            const teacher = teachersById[id];
            teacher.sections = (teacher.sections || []).filter(function (label) {
                return sectionNames.indexOf(label) === -1;
            });
            if (sectionTeacherIds.indexOf(id) !== -1) {
                teacher.sections = teacher.sections.concat(sectionNames);
            }
        });
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

    function upsertSectionInGrade(grade, section) {
        if (!grade || !section) return;
        const list = sectionsByGrade[grade] || [];
        const id = String(section.id);
        const next = {
            id: section.id,
            name: section.name,
            capacity: section.capacity || 25,
            adviser: section.adviser || null,
            grade_level: section.grade_level || grade
        };
        const idx = list.findIndex(function (s) { return String(s.id) === id; });
        if (idx === -1) {
            list.push(next);
        } else {
            list[idx] = Object.assign({}, list[idx], next);
        }
        applySectionsForGrade(grade, list);
    }

    function applySectionsForGrade(grade, sections) {
        sectionsByGrade[grade] = sections || [];
        renderSectionChips(grade, sectionsByGrade[grade]);
        updateSectionTile(grade, sectionsByGrade[grade]);
        syncTeacherSectionSelect();
    }

    function upsertTeacherSectionOption(section) {
        const select = document.getElementById('section_id');
        if (!select || !section || !section.id) return;
        const id = String(section.id);
        const grade = section.grade_level || '';
        let opt = select.querySelector('option[value="' + id + '"]');
        if (!opt) {
            opt = document.createElement('option');
            opt.value = id;
            select.appendChild(opt);
        }
        opt.textContent = section.name + (grade ? ' (' + grade + ')' : '');
        opt.setAttribute('data-grade', grade);
    }

    function syncTeacherSectionSelect() {
        Object.keys(sectionsByGrade).forEach(function (grade) {
            (sectionsByGrade[grade] || []).forEach(function (section) {
                upsertTeacherSectionOption(Object.assign({ grade_level: section.grade_level || grade }, section));
            });
        });
        const gradeSelectEl = document.getElementById('grade_level');
        filterSections(gradeSelectEl ? gradeSelectEl.value : '');
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
                const incoming = (data.sections || []).map(function (s) {
                    return {
                        id: s.id,
                        name: s.name,
                        capacity: s.capacity,
                        adviser: s.adviser,
                        grade_level: s.grade_level || grade
                    };
                });
                const current = sectionsByGrade[grade] || [];
                const merged = incoming.slice();
                current.forEach(function (s) {
                    if (s && s.id && !merged.some(function (row) { return String(row.id) === String(s.id); })) {
                        merged.push(s);
                    }
                });
                applySectionsForGrade(grade, merged);
                const sub = document.getElementById('sectionModalSub');
                if (sub) {
                    sub.textContent = merged.length
                        ? (merged.length + ' section(s) — live from database / enrollment')
                        : 'No block sections yet for ' + grade;
                }
                return merged;
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
            if (res.section) {
                upsertSectionInGrade(grade, res.section);
            }
            refreshSectionsFromApi(grade).catch(function () {
                applySectionsForGrade(grade, sectionsByGrade[grade] || []);
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
    const detailsModalEl = document.getElementById('teacherDetailsModal');
    const allTeachersModalEl = document.getElementById('allTeachersModal');
    const unassignConfirmEl = document.getElementById('unassignTeacherConfirmModal');
    let pendingTeacherOp = 'teacher_grade';
    let openedFromAllTeachers = false;
    let currentTeacherName = 'this teacher';
    let detailsSwapLock = false;
    let reopenDetailsOnConfirmClose = false;

    function showBsModal(el) {
        if (!el) return;
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        } else if (window.$) {
            $(el).modal('show');
        }
    }

    function hideBsModal(el) {
        if (!el) return;
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).hide();
        } else if (window.$) {
            $(el).modal('hide');
        }
    }

    function afterModalHidden(el, cb) {
        if (!el || !el.classList.contains('show')) {
            cb();
            return;
        }
        const once = function () {
            el.removeEventListener('hidden.bs.modal', once);
            cb();
        };
        el.addEventListener('hidden.bs.modal', once);
        hideBsModal(el);
    }

    function dash(value) {
        const text = (value || '').toString().trim();
        return text || '—';
    }

    function fillTeacherDetails(teacher) {
        currentTeacherName = teacher.name || 'this teacher';
        const photo = document.getElementById('tdPhoto');
        const name = document.getElementById('tdName');
        const email = document.getElementById('tdEmail');
        const meta = document.getElementById('tdMeta');
        if (photo) {
            photo.src = teacher.photo || defaultPhoto;
            photo.alt = currentTeacherName;
            photo.onerror = function () { this.onerror = null; this.src = defaultPhoto; };
        }
        if (name) name.textContent = currentTeacherName;
        if (email) email.textContent = teacher.email || '';
        if (meta) {
            const chips = [];
            if (teacher.user_id) chips.push('<span class="ams-teacher-chip">ID ' + teacher.user_id + '</span>');
            if (teacher.gender) chips.push('<span class="ams-teacher-chip">' + teacher.gender + '</span>');
            if (teacher.phone) chips.push('<span class="ams-teacher-chip">' + teacher.phone + '</span>');
            meta.innerHTML = chips.join('');
        }
        const qual = document.getElementById('tdQualification');
        const exp = document.getElementById('tdExperience');
        const grades = document.getElementById('tdGrades');
        const sections = document.getElementById('tdSections');
        if (qual) qual.textContent = dash(teacher.qualification);
        if (exp) exp.textContent = dash(teacher.experience);
        if (grades) grades.textContent = (teacher.grades && teacher.grades.length) ? teacher.grades.join(', ') : 'None yet';
        if (sections) sections.textContent = (teacher.sections && teacher.sections.length) ? teacher.sections.join(', ') : 'None yet';
        const idInput = document.getElementById('modalTeacherId');
        if (idInput) idInput.value = teacher.id;
    }

    function openTeacherDetails(teacherId, fromAll) {
        const teacher = teachersById[String(teacherId)];
        if (!teacher) return;
        openedFromAllTeachers = !!fromAll;
        fillTeacherDetails(teacher);
        if (openedFromAllTeachers) {
            hideBsModal(allTeachersModalEl);
            setTimeout(function () { showBsModal(detailsModalEl); }, 220);
        } else {
            showBsModal(detailsModalEl);
        }
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.js-open-teacher');
        if (!btn) return;
        e.preventDefault();
        openTeacherDetails(btn.getAttribute('data-teacher-id'), btn.getAttribute('data-from-all') === '1');
    });

    if (detailsModalEl) {
        detailsModalEl.addEventListener('hidden.bs.modal', function () {
            if (detailsSwapLock) return;
            if (openedFromAllTeachers) {
                openedFromAllTeachers = false;
                showBsModal(allTeachersModalEl);
            }
        });
    }

    if (unassignConfirmEl) {
        unassignConfirmEl.addEventListener('hidden.bs.modal', function () {
            if (!reopenDetailsOnConfirmClose) {
                detailsSwapLock = false;
                return;
            }
            reopenDetailsOnConfirmClose = false;
            detailsSwapLock = true;
            showBsModal(detailsModalEl);
            const unlock = function () {
                detailsModalEl.removeEventListener('shown.bs.modal', unlock);
                detailsSwapLock = false;
            };
            if (detailsModalEl) {
                detailsModalEl.addEventListener('shown.bs.modal', unlock);
            } else {
                detailsSwapLock = false;
            }
        });
    }

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
        preview.classList.remove('d-none');
        if (!subjects.length) {
            preview.innerHTML = '<strong>' + grade + '</strong> has no subjects yet. Open the catalog and add some first.';
        } else {
            preview.innerHTML = '<strong>Subjects in ' + grade + ':</strong> ' + subjects.map(function (s) { return s.name; }).join(', ');
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
    if (sectionSelect) {
        sectionSelect.addEventListener('change', refreshGradePreview);
    }

    document.querySelectorAll('.js-teacher-action').forEach(function (btn) {
        btn.addEventListener('click', function () {
            pendingTeacherOp = this.getAttribute('data-op') || 'teacher_grade';
            const op = document.getElementById('teacherGradeOperation');
            if (op) op.value = pendingTeacherOp;
        });
    });

    function setTeacherActionsBusy(busy, label) {
        document.querySelectorAll('.js-teacher-action').forEach(function (btn) {
            btn.disabled = !!busy;
            if (busy && btn.getAttribute('data-op') === pendingTeacherOp) {
                btn.dataset.originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> ' + label;
            } else if (!busy && btn.dataset.originalHtml) {
                btn.innerHTML = btn.dataset.originalHtml;
            }
        });
    }

    $('#teacherGradeForm').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const op = pendingTeacherOp || $('#teacherGradeOperation').val();
        const isUnassign = op.indexOf('unassign') !== -1;
        const isSection = op.indexOf('section') !== -1;

        if (!$('#modalTeacherId').val()) {
            showDeleteResultModal(false, 'Missing teacher', 'Please open a teacher first.');
            return false;
        }

        const grade = $('#grade_level').val();
        const subjects = subjectsByGrade[grade] || [];
        if (!isSection && !subjects.length) {
            showDeleteResultModal(false, 'No subjects', 'This grade has no subjects yet. Add subjects in the catalog first.');
            return false;
        }

        if (isSection && !$('#section_id').val()) {
            showDeleteResultModal(false, 'Missing section', 'Please select a block section.');
            return false;
        }

        if (isUnassign && !$form.data('unassign-confirmed')) {
            const text = document.getElementById('unassignTeacherConfirmText');
            if (text) {
                text.textContent = isSection
                    ? ('Unassign ' + currentTeacherName + ' from the selected section only?')
                    : ('Unassign ' + currentTeacherName + ' from all subjects in ' + (grade || 'this grade') + '?');
            }
            detailsSwapLock = true;
            reopenDetailsOnConfirmClose = true;
            afterModalHidden(detailsModalEl, function () {
                showBsModal(unassignConfirmEl);
            });
            return false;
        }

        $form.data('unassign-confirmed', false);
        reopenDetailsOnConfirmClose = false;
        detailsSwapLock = true;
        setTeacherActionsBusy(true, isUnassign ? 'Unassigning...' : 'Assigning...');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).done(function (res) {
            applyAssignmentResult(res || {});
            openedFromAllTeachers = false;
            afterModalHidden(detailsModalEl, function () {
                detailsSwapLock = false;
                showDeleteResultModal(true, 'Done', (res && res.message) || 'Teacher assignment updated.');
            });
        }).fail(function (xhr) {
            detailsSwapLock = false;
            const msg = (xhr.responseJSON && xhr.responseJSON.message)
                || (xhr.responseJSON && xhr.responseJSON.errors && Object.values(xhr.responseJSON.errors)[0][0])
                || 'Failed to update teacher assignment.';
            showDeleteResultModal(false, 'Failed', msg);
        }).always(function () {
            setTeacherActionsBusy(false);
        });
        return false;
    });

    document.getElementById('allTeachersSearch')?.addEventListener('input', function () {
        const q = (this.value || '').toLowerCase().trim();
        document.querySelectorAll('#allTeachersGrid .js-teacher-modal-item').forEach(function (item) {
            const name = (item.getAttribute('data-name') || '').toLowerCase();
            item.classList.toggle('d-none', q !== '' && name.indexOf(q) === -1);
        });
    });

    document.getElementById('unassignTeacherConfirmBtn')?.addEventListener('click', function () {
        reopenDetailsOnConfirmClose = false;
        detailsSwapLock = true;
        afterModalHidden(unassignConfirmEl, function () {
            const $form = $('#teacherGradeForm');
            $form.data('unassign-confirmed', true);
            $form.trigger('submit');
        });
    });

    <?php if(old('teacher_ids.0')): ?>
        openTeacherDetails(<?php echo json_encode((int) old('teacher_ids.0'), 15, 512) ?>, false);
    <?php endif; ?>
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/class-subject/unified-management.blade.php ENDPATH**/ ?>