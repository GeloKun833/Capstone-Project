
<?php $__env->startSection('content'); ?>



<?php if(session('success')): ?>
<script>
    // Immediate success notification
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🎉 SUCCESS SESSION FOUND:', '<?php echo e(session('success')); ?>');
        
        // Show toastr immediately
        if (typeof toastr !== 'undefined') {
            toastr.success('<?php echo e(session('success')); ?>', 'Success!', {
                timeOut: 3000,
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right'
            });
        }
    });
</script>
<?php endif; ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Create New Assignment</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('assignments.index')); ?>">Assignments</a></li>
                        <li class="breadcrumb-item active">Create Assignment</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Create New Assignment</h5>
                        <small class="text-muted">Section first, then subject, then assignment details</small>
                    </div>
                    <div class="card-body">
                        
                        <?php if($errors->any()): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong><i class="fas fa-exclamation-triangle"></i> Validation Errors:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo e(route('assignments.store')); ?>" method="POST" id="assignmentForm">
                            <?php echo csrf_field(); ?>

                            
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">1. Class Assignment</h5>
                                    <small class="text-muted">Select your assigned section first, then choose a subject</small>
                                </div>
                                <div class="card-body">
                                    <?php if($subjects->isEmpty() || $sections->isEmpty()): ?>
                                        <div class="alert alert-warning mb-0">
                                            You have no subject/section assignment yet. Ask Admin to assign you under
                                            <strong>Classes &amp; Subjects</strong>.
                                            <?php if(Route::has('teacher.classes')): ?>
                                                <a href="<?php echo e(route('teacher.classes')); ?>" class="alert-link">View My Classes &amp; Subjects</a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="row">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label for="section_id" class="form-label">Section <span class="text-danger">*</span></label>
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
                                                    <option value="<?php echo e($section->id); ?>" <?php echo e((string) old('section_id') === (string) $section->id ? 'selected' : ''); ?>>
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
                                        <div class="col-md-6">
                                            <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
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
                                            <small class="text-muted">Subjects appear after you pick a section (same grade only)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">2. Assignment Details</h5>
                                        </div>
                                        <div class="card-body">
                                    
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="title" class="form-label">Assignment Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                   id="title" name="title" value="<?php echo e(old('title')); ?>" required>
                                            <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="max_score" class="form-label">Maximum Score <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control <?php $__errorArgs = ['max_score'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                   id="max_score" name="max_score" value="<?php echo e(old('max_score', 100)); ?>" min="1" max="1000" required>
                                            <?php $__errorArgs = ['max_score'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                            <select class="form-control <?php $__errorArgs = ['academic_year_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="academic_year_id" name="academic_year_id" required>
                                                <option value="">Select Academic Year</option>
                                                <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $academicYear): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($academicYear->id); ?>" <?php echo e(old('academic_year_id') == $academicYear->id ? 'selected' : ''); ?>>
                                                        <?php echo e($academicYear->name); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <?php $__errorArgs = ['academic_year_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="semester_id" class="form-label">Semester <span class="text-danger">*</span></label>
                                            <select class="form-control <?php $__errorArgs = ['semester_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="semester_id" name="semester_id" required>
                                                <option value="">Select Semester</option>
                                                <?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($semester->id); ?>" <?php echo e(old('semester_id') == $semester->id ? 'selected' : ''); ?>>
                                                        <?php echo e($semester->name); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <?php $__errorArgs = ['semester_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control <?php $__errorArgs = ['due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                   id="due_date" name="due_date" value="<?php echo e(old('due_date')); ?>" required>
                                            <?php $__errorArgs = ['due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="due_time" class="form-label">Due Time</label>
                                            <input type="time" class="form-control <?php $__errorArgs = ['due_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                   id="due_time" name="due_time" value="<?php echo e(old('due_time')); ?>">
                                            <?php $__errorArgs = ['due_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="description" class="form-label">Assignment Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                  id="description" name="description" rows="5" required><?php echo e(old('description')); ?></textarea>
                                        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div class="mb-0">
                                        <label for="submission_instructions" class="form-label">Submission Instructions</label>
                                        <textarea class="form-control <?php $__errorArgs = ['submission_instructions'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                  id="submission_instructions" name="submission_instructions" rows="3"><?php echo e(old('submission_instructions')); ?></textarea>
                                        <?php $__errorArgs = ['submission_instructions'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">Assignment Settings</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="allows_late_submission" 
                                                           name="allows_late_submission" <?php echo e(old('allows_late_submission') ? 'checked' : ''); ?>>
                                                    <label class="form-check-label" for="allows_late_submission">
                                                        Allow Late Submissions
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="mb-3" id="late_penalty_div" style="display: none;">
                                                <label for="late_submission_penalty" class="form-label">Late Submission Penalty (%)</label>
                                                <input type="number" class="form-control" id="late_submission_penalty" 
                                                       name="late_submission_penalty" value="<?php echo e(old('late_submission_penalty', 10)); ?>" 
                                                       min="0" max="100">
                                            </div>

                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="requires_file_upload" 
                                                           name="requires_file_upload" <?php echo e(old('requires_file_upload') ? 'checked' : ''); ?>>
                                                    <label class="form-check-label" for="requires_file_upload">
                                                        Require File Upload
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="mb-3" id="file_settings_div" style="display: none;">
                                                <label class="form-label mb-2">Allowed File Types</label>
                                                <div class="border rounded p-3 bg-light">
                                                    <div class="mb-3">
                                                        <div class="fw-semibold text-dark mb-2"><i class="fas fa-file-alt me-1"></i> Documents</div>
                                                        <div class="d-flex flex-wrap gap-3">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="allowed_file_types[]" value="pdf" id="type_pdf"
                                                                       <?php echo e(in_array('pdf', (array) old('allowed_file_types', ['pdf', 'docx']), true) ? 'checked' : ''); ?>>
                                                                <label class="form-check-label" for="type_pdf">PDF</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="allowed_file_types[]" value="docx" id="type_docx"
                                                                       <?php echo e(in_array('docx', (array) old('allowed_file_types', ['pdf', 'docx']), true) ? 'checked' : ''); ?>>
                                                                <label class="form-check-label" for="type_docx">DOCX</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <div class="fw-semibold text-dark"><i class="fas fa-image me-1"></i> Images</div>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary py-0" id="selectAllImagesBtn">Select all images</button>
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-3" id="imageTypeChecks">
                                                            <?php $__currentLoopData = ['jpg' => 'JPG', 'png' => 'PNG', 'gif' => 'GIF', 'webp' => 'WEBP', 'bmp' => 'BMP', 'tif' => 'TIF', 'tiff' => 'TIFF', 'heic' => 'HEIC', 'svg' => 'SVG']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <div class="form-check">
                                                                    <input class="form-check-input image-type-check" type="checkbox" name="allowed_file_types[]" value="<?php echo e($value); ?>" id="type_<?php echo e($value); ?>"
                                                                           <?php echo e(in_array($value, (array) old('allowed_file_types', []), true) ? 'checked' : ''); ?>>
                                                                    <label class="form-check-label" for="type_<?php echo e($value); ?>"><?php echo e($label); ?></label>
                                                                </div>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted">Tick the formats students may submit. JPEG is accepted when JPG is selected.</small>
                                            </div>

                                            <div class="mb-3" id="file_size_div" style="display: none;">
                                                <label for="max_file_size" class="form-label">Maximum File Size (MB)</label>
                                                <input type="number" class="form-control" id="max_file_size" 
                                                       name="max_file_size" value="<?php echo e(old('max_file_size', 10)); ?>" 
                                                       min="1" max="50">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="<?php echo e(route('assignments.index')); ?>" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="fas fa-save me-2"></i>Create Assignment
                                        </button>
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


<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Assignment Created Successfully!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h5 class="mb-3">🎉 Great Job!</h5>
                <p class="mb-2">Your assignment has been created successfully and is now <strong>published</strong>.</p>
                <p class="mb-0 text-muted">Students can now view and submit this assignment.</p>
            </div>
            <div class="modal-footer">
                <a href="<?php echo e(route('assignments.index')); ?>" class="btn btn-primary w-100">
                    <i class="fas fa-list me-2"></i>View All Assignments
                </a>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<?php
    $subjectsForJs = $subjects->map(function ($s) {
        return [
            'id' => $s->id,
            'label' => $s->subject_name . ($s->class ? ' (' . $s->class . ')' : ''),
        ];
    })->values();
?>
<script>
    $(document).ready(function() {
        const subjectsBySection = <?php echo json_encode($subjectsBySection ?? [], 15, 512) ?>;
        const allSubjects = <?php echo json_encode($subjectsForJs, 15, 512) ?>;
        const oldSectionId = <?php echo json_encode(old('section_id'), 15, 512) ?>;
        const oldSubjectId = <?php echo json_encode(old('subject_id'), 15, 512) ?>;

        function filterSubjects() {
            const sectionId = $('#section_id').val();
            const $subject = $('#subject_id');
            const current = oldSubjectId || $subject.val();

            $subject.empty();

            if (!sectionId) {
                $subject.append('<option value="">Select section first</option>');
                $subject.prop('disabled', true);
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
        }

        $('#section_id').on('change', filterSubjects);
        if (oldSectionId) {
            $('#section_id').val(String(oldSectionId));
        }
        filterSubjects();

        $('#assignmentForm').on('submit', function() {
            $('#subject_id').prop('disabled', false);
        });

        // Show success modal if assignment was created successfully
        <?php if(session('success')): ?>
        console.log('✅ Success session detected, showing modal...');
        showSuccessModal();
        <?php endif; ?>

        // Toggle late submission penalty field
        $('#allows_late_submission').change(function() {
            if ($(this).is(':checked')) {
                $('#late_penalty_div').show();
            } else {
                $('#late_penalty_div').hide();
            }
        });

        // Toggle file upload settings
        $('#requires_file_upload').change(function() {
            if ($(this).is(':checked')) {
                $('#file_settings_div').show();
                $('#file_size_div').show();
            } else {
                $('#file_settings_div').hide();
                $('#file_size_div').hide();
            }
        });

        // Show/hide fields on page load based on current values
        if ($('#allows_late_submission').is(':checked')) {
            $('#late_penalty_div').show();
        }
        if ($('#requires_file_upload').is(':checked')) {
            $('#file_settings_div').show();
            $('#file_size_div').show();
        }

        $('#selectAllImagesBtn').on('click', function() {
            const $checks = $('.image-type-check');
            const allChecked = $checks.length && $checks.filter(':checked').length === $checks.length;
            $checks.prop('checked', !allChecked);
            $(this).text(allChecked ? 'Select all images' : 'Clear images');
        });

        // Form submission - prevent double submit
        $('#assignmentForm').on('submit', function() {
            const submitBtn = $('#submitBtn');
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Creating Assignment...');
        });
    });

    // Function to show success modal with multiple fallback methods
    function showSuccessModal() {
        const modal = document.getElementById('successModal');
        
        if (!modal) {
            console.error('❌ Success modal element not found');
            // Fallback: Use toastr if available
            if (typeof toastr !== 'undefined') {
                toastr.success('Assignment created successfully!', 'Success', {
                    timeOut: 5000,
                    closeButton: true,
                    progressBar: true
                });
            } else {
                alert('✅ Assignment created successfully!');
            }
            return;
        }
        
        try {
            // Try Bootstrap 5 Modal first
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                console.log('✅ Using Bootstrap 5 Modal');
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            }
            // Try jQuery/Bootstrap 4 Modal
            else if (typeof $ !== 'undefined' && $.fn.modal) {
                console.log('✅ Using jQuery Modal');
                $('#successModal').modal('show');
            }
            // Manual fallback
            else {
                console.log('⚠️ Using manual modal display');
                modal.classList.add('show');
                modal.style.display = 'block';
                modal.setAttribute('aria-modal', 'true');
                modal.setAttribute('role', 'dialog');
                
                // Add backdrop
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.id = 'successModalBackdrop';
                document.body.appendChild(backdrop);
                document.body.classList.add('modal-open');
                
                // Close on backdrop click
                backdrop.addEventListener('click', function() {
                    closeSuccessModal();
                });
                
                // Close on button click
                const closeBtn = modal.querySelector('.btn-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        closeSuccessModal();
                    });
                }
            }
            console.log('✅ Success modal opened');
        } catch (e) {
            console.error('❌ Error opening success modal:', e);
            alert('✅ Assignment created successfully!');
        }
    }

    // Function to close success modal
    function closeSuccessModal() {
        const modal = document.getElementById('successModal');
        const backdrop = document.getElementById('successModalBackdrop');
        
        if (modal) {
            modal.classList.remove('show');
            modal.style.display = 'none';
            modal.removeAttribute('aria-modal');
            modal.removeAttribute('role');
        }
        
        if (backdrop) {
            backdrop.remove();
        }
        
        document.body.classList.remove('modal-open');
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\assignments\create.blade.php ENDPATH**/ ?>