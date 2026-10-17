<?php $__env->startSection('content'); ?>

    <div class="page-wrapper">
        <div class="content container-fluid dir-page">

            <div class="page-header">
                <div class="row align-items-start">
                    <div class="col">
                        <h3 class="page-title mb-1">Create Lesson</h3>
                        <p class="dir-subtitle">Select your assigned class first, then fill in the lesson details.</p>
                    </div>
                    <div class="col-auto text-end">
                        <ul class="breadcrumb justify-content-end mb-2">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo e(route('lessons.index')); ?>">My Lessons</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ul>
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="<?php echo e(route('lessons.index')); ?>" class="btn btn-outline-secondary dir-btn">Back</a>
                            <button type="submit" form="lessonForm" class="btn btn-primary dir-btn" id="submitBtn" <?php if($subjects->isEmpty() || $sections->isEmpty() || ! $activeYear || $semesters->isEmpty()): ?> disabled <?php endif; ?>>
                                <i class="fas fa-save me-1"></i> <span id="submitText">Create Lesson</span>
                                <span id="submitSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-sm-12">
                    <div class="dir-card">
                        <div class="p-3 p-md-4">
                            <?php if($subjects->isEmpty() || $sections->isEmpty()): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-info-circle me-2"></i>
                                    You have no subject/section assignment yet. Ask Admin to assign you under
                                    <strong>Classes &amp; Subjects</strong> before creating a lesson.
                                    <a href="<?php echo e(route('teacher.classes')); ?>" class="alert-link">View My Classes &amp; Subjects</a>
                                </div>
                            <?php endif; ?>
                            <?php if(! $activeYear || $semesters->isEmpty()): ?>
                                <div class="alert alert-warning">
                                    The admin has not set a current academic year with semesters. A lesson cannot be created until that is set.
                                </div>
                            <?php endif; ?>

                            <form action="<?php echo e(route('lessons.store')); ?>" method="POST" id="lessonForm" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>

                                
                                <div class="student-group-form">
                                    <div class="mb-3">
                                        <h5 class="mb-1">1. Class Assignment</h5>
                                        <p class="text-muted mb-0 small">Select your assigned section first, then choose a subject for that section</p>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Section <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-control <?php $__errorArgs = ['section_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="section_id" name="section_id" required <?php if($sections->isEmpty()): ?> disabled <?php endif; ?>>
                                                    <option value="">Select Section</option>
                                                    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($section->id); ?>"
                                                            data-grade="<?php echo e($section->grade_level); ?>"
                                                            <?php echo e((string) old('section_id') === (string) $section->id ? 'selected' : ''); ?>>
                                                            <?php echo e($section->name); ?> (<?php echo e($section->grade_level ?? 'N/A'); ?>)
                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <?php $__errorArgs = ['section_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Subject <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-control <?php $__errorArgs = ['subject_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="subject_id" name="subject_id" required disabled>
                                                    <option value="">Select section first</option>
                                                </select>
                                                <?php $__errorArgs = ['subject_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                <div class="form-text">Subjects appear after you pick a section (same grade only)</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3" id="lessonOutline">
                                        <p class="text-muted mb-0">Select a section and subject to see the course outline and OPTLB.</p>
                                    </div>
                                </div>

                                
                                <div class="student-group-form">
                                    <div class="mb-3">
                                        <h5 class="mb-1">2. Lesson Information</h5>
                                        <p class="text-muted mb-0 small">Fill in the details below to create a new lesson</p>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-8 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Lesson Title <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="title" name="title" value="<?php echo e(old('title')); ?>" placeholder="Enter lesson title" required>
                                                <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Lesson Date <span class="text-danger">*</span>
                                                </label>
                                                <input type="date" class="form-control <?php $__errorArgs = ['lesson_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="lesson_date" name="lesson_date" value="<?php echo e(old('lesson_date', now()->toDateString())); ?>" required>
                                                <?php $__errorArgs = ['lesson_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Lesson Description <span class="text-danger">*</span>
                                                </label>
                                                <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="description" name="description" rows="4" placeholder="Describe the lesson objectives, content, and learning outcomes" required><?php echo e(old('description')); ?></textarea>
                                                <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Academic Year</label>
                                                <input type="text" class="form-control" value="<?php echo e($activeYear->name ?? 'No current academic year'); ?>" readonly>
                                                <?php if($activeYear): ?>
                                                    <input type="hidden" name="academic_year_id" id="academic_year_id" value="<?php echo e($activeYear->id); ?>">
                                                <?php endif; ?>
                                                <div class="form-text">This follows the academic year the admin set as current.</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Semester <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-control <?php $__errorArgs = ['semester_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="semester_id" name="semester_id" required <?php if($semesters->isEmpty()): ?> disabled <?php endif; ?>>
                                                    <option value="">Select Semester</option>
                                                    <?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($semester->id); ?>" <?php echo e((string) old('semester_id', $currentSemester->id ?? '') === (string) $semester->id ? 'selected' : ''); ?>>
                                                            <?php echo e($semester->name); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <?php $__errorArgs = ['semester_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                <div class="form-text">Semesters for the current academic year.</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label class="form-label">Lesson Materials (Optional)</label>
                                                <input type="file" class="form-control <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="file" name="file" accept=".pdf,.docx,.ppt,.pptx">
                                                <div class="upload-thumb mt-2" id="filePreview"></div>
                                                <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                <small class="form-text text-muted">Supported formats: PDF, Word (DOCX), PPT, PPTX (Max: 10MB)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title mb-0">
                                                    <i class="fas fa-eye me-2"></i>Lesson Preview
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Subject:</label>
                                                            <span class="info-value" id="previewSubject">-</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Section:</label>
                                                            <span class="info-value" id="previewSection">-</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Academic Period:</label>
                                                            <span class="info-value" id="previewPeriod">-</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Title:</label>
                                                            <span class="info-value" id="previewTitle">-</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914e">
<style>
.student-group-form {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 31px 3px rgba(44,50,63,.02);
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
}
.student-group-form .form-control {
    border: 1px solid #ddd;
    border-radius: 5px;
    height: 45px;
    padding: 10px 15px;
    font-size: 15px;
}
.student-group-form .form-control:focus {
    border-color: #3d5ee1;
    box-shadow: 0 0 0 0.2rem rgba(61, 94, 225, 0.25);
}
.student-group-form .form-label {
    font-weight: 600;
    color: #2c323f;
    margin-bottom: 8px;
}
.student-group-form textarea.form-control {
    height: auto;
    min-height: 100px;
}
.student-group-form input[type="file"] {
    height: auto;
    padding: 8px 12px;
}
.info-label {
    display: block;
    font-weight: 600;
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 5px;
}
.info-value {
    display: block;
    font-weight: 500;
    color: #2c323f;
    font-size: 16px;
}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('enrollment.partials.file-card-assets', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->startPush('scripts'); ?>
<?php
    $subjectsForJs = $subjects->map(function ($s) {
        return [
            'id' => $s->id,
            'label' => $s->subject_name . ($s->class ? ' (' . $s->class . ')' : ''),
            'grade' => $s->class,
        ];
    })->values();
?>
<script>
$(document).ready(function() {
    const subjectsBySection = <?php echo json_encode($subjectsBySection ?? [], 15, 512) ?>;
    const allSubjects = <?php echo json_encode($subjectsForJs, 15, 512) ?>;
    const courseOutlines = <?php echo json_encode($courseOutlines ?? [], 15, 512) ?>;
    const oldSectionId = <?php echo json_encode(old('section_id'), 15, 512) ?>;
    const oldSubjectId = <?php echo json_encode(old('subject_id'), 15, 512) ?>;
    const lockedYearName = <?php echo json_encode($activeYear->name ?? '', 15, 512) ?>;

    function filterSubjects() {
        const sectionId = $('#section_id').val();
        const $subject = $('#subject_id');
        const current = oldSubjectId || $subject.val();

        $subject.empty();

        if (!sectionId) {
            $subject.append('<option value="">Select section first</option>');
            $subject.prop('disabled', true);
            updatePreview();
            showCourseOutline();
            return;
        }

        const allowedIds = (subjectsBySection[sectionId] || []).map(String);
        $subject.append('<option value="">Select Subject</option>');

        let matched = 0;
        allSubjects.forEach(function(subject) {
            if (allowedIds.includes(String(subject.id))) {
                matched++;
                const selected = String(current) === String(subject.id) ? ' selected' : '';
                $subject.append('<option value="' + subject.id + '"' + selected + '>' + subject.label + '</option>');
            }
        });

        if (matched === 0) {
            $subject.empty().append('<option value="">No subjects for this section</option>');
            $subject.prop('disabled', true);
        } else {
            $subject.prop('disabled', false);
        }

        updatePreview();
        showCourseOutline();
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function showCourseOutline() {
        const box = document.getElementById('lessonOutline');
        if (!box) return;
        const subjectId = $('#subject_id').val();
        if (!subjectId) {
            box.innerHTML = '<p class="text-muted mb-0">Select a section and subject to see the course outline and OPTLB.</p>';
            return;
        }
        const rows = courseOutlines[subjectId] || courseOutlines[String(subjectId)] || [];
        if (!rows.length) {
            box.innerHTML = '<p class="text-muted mb-0">No course outline has been published for this subject yet.</p>';
            return;
        }
        box.innerHTML = rows.map(function (row) {
            const topics = (row.topics || []).map(function (topic) {
                return '<li>Unit ' + escapeHtml(topic.unit) + ': ' + escapeHtml(topic.title) + '</li>';
            }).join('');
            const optlb = row.optlb
                ? '<div class="mt-3"><div class="fw-semibold">OPTLB</div><div>' + escapeHtml(row.optlb.title) + '</div>'
                    + '<p class="mb-1 mt-2"><strong>Objectives.</strong> ' + escapeHtml(row.optlb.objectives) + '</p>'
                    + '<p class="mb-1" style="white-space:pre-line;"><strong>Sequence.</strong> ' + escapeHtml(row.optlb.sequence) + '</p>'
                    + '<p class="mb-1"><strong>Activities.</strong> ' + escapeHtml(row.optlb.activities) + '</p>'
                    + '<p class="mb-1"><strong>Assessment.</strong> ' + escapeHtml(row.optlb.assessment) + '</p>'
                    + '<p class="mb-0"><strong>References.</strong> ' + escapeHtml(row.optlb.references) + '</p></div>'
                : '';
            const files = (row.files || []).map(function (file) {
                return '<div class="mt-3"><div class="small fw-semibold mb-1">' + escapeHtml(file.name) + '</div>'
                    + '<iframe src="' + escapeHtml(file.url) + '" title="' + escapeHtml(file.name) + '" style="width:100%;height:280px;border:1px solid #e5e7eb;border-radius:8px;"></iframe></div>';
            }).join('');
            const badge = row.demo ? '<span class="badge bg-warning text-dark ms-2">DEMO</span>' : '';
            return '<div class="border rounded p-3 mb-3 bg-light">'
                + '<div class="fw-semibold">Course outline ' + badge + '</div>'
                + '<div>' + escapeHtml(row.title) + '</div>'
                + '<div class="small text-muted mb-2">' + escapeHtml(row.year) + '</div>'
                + '<p class="mb-1">' + escapeHtml(row.description) + '</p>'
                + '<p class="mb-1"><strong>Objectives.</strong> ' + escapeHtml(row.objectives) + '</p>'
                + '<ul class="mb-1">' + topics + '</ul>'
                + '<p class="mb-0"><strong>Assessment.</strong> ' + escapeHtml(row.assessment) + '</p>'
                + optlb + files + '</div>';
        }).join('');
    }

    function updatePreview() {
        const subjectSelect = $('#subject_id option:selected');
        const sectionSelect = $('#section_id option:selected');
        const semesterSelect = $('#semester_id option:selected');
        $('#previewSubject').text(subjectSelect.val() ? subjectSelect.text() : '-');
        $('#previewSection').text(sectionSelect.val() ? sectionSelect.text() : '-');
        $('#previewPeriod').text(
            (lockedYearName && semesterSelect.val())
                ? lockedYearName + ' - ' + semesterSelect.text()
                : '-'
        );
        $('#previewTitle').text($('#title').val().trim() || '-');
    }

    $('#section_id').on('change', function() {
        filterSubjects();
    });
    $('#subject_id').on('change', function () {
        updatePreview();
        showCourseOutline();
    });
    $('#semester_id').on('change', updatePreview);
    $('#title').on('input', updatePreview);

    if (oldSectionId) {
        $('#section_id').val(String(oldSectionId));
    }
    filterSubjects();

    $('#lessonForm').on('submit', function(e) {
        const requiredFields = [
            { id: '#section_id', label: 'Section' },
            { id: '#subject_id', label: 'Subject' },
            { id: '#title', label: 'Lesson title' },
            { id: '#description', label: 'Lesson description' },
            { id: '#lesson_date', label: 'Lesson date' },
            { id: '#semester_id', label: 'Semester' },
        ];

        let hasErrors = false;
        let errorMessage = 'Please fix the following errors:\n';
        $('.form-control').removeClass('is-invalid');

        requiredFields.forEach(function(field) {
            const val = $(field.id).val();
            if (!val || (typeof val === 'string' && !val.trim())) {
                $(field.id).addClass('is-invalid');
                errorMessage += '• ' + field.label + ' is required\n';
                hasErrors = true;
            }
        });

        if (hasErrors) {
            e.preventDefault();
            alert(errorMessage);
            return false;
        }

        $('#subject_id').prop('disabled', false);

        $('#submitBtn').prop('disabled', true);
        $('#submitText').text('Creating...');
        $('#submitSpinner').show();
    });

    $('#file').on('change', function() {
        const file = this.files[0];
        const preview = document.getElementById('filePreview');
        const maxSize = 10 * 1024 * 1024;
        if (!file) {
            window.paintEnrollmentFileCard(preview, null);
            return;
        }
        if (file.size > maxSize) {
            alert('File size must be less than 10MB.');
            this.value = '';
            window.paintEnrollmentFileCard(preview, null);
            return;
        }
        const ext = (file.name.split('.').pop() || '').toLowerCase();
        if (!['pdf', 'docx', 'ppt', 'pptx'].includes(ext)) {
            alert('Please select a valid file type (PDF, Word DOCX, PPT, PPTX).');
            this.value = '';
            window.paintEnrollmentFileCard(preview, null);
            return;
        }
        window.paintEnrollmentFileCard(preview, file);
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/lessons/create.blade.php ENDPATH**/ ?>