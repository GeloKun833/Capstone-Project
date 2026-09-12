
<?php $__env->startSection('content'); ?>


<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Edit Assignment</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('assignments.index')); ?>">Assignments</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('assignments.show', $assignment->id)); ?>"><?php echo e($assignment->title); ?></a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Edit Assignment: <?php echo e($assignment->title); ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo e(route('assignments.update', $assignment->id)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    
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
                                                   id="title" name="title" value="<?php echo e(old('title', $assignment->title)); ?>" required>
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
                                                   id="max_score" name="max_score" value="<?php echo e(old('max_score', $assignment->max_score)); ?>" min="1" max="1000" required>
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
                                            <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                                            <select class="form-control <?php $__errorArgs = ['subject_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="subject_id" name="subject_id" required>
                                                <option value="">Select Subject</option>
                                                <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($subject->id); ?>" <?php echo e(old('subject_id', $assignment->subject_id) == $subject->id ? 'selected' : ''); ?>>
                                                        <?php echo e($subject->name); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <?php $__errorArgs = ['subject_id'];
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
                                            <label for="section_id" class="form-label">Section <span class="text-danger">*</span></label>
                                            <select class="form-control <?php $__errorArgs = ['section_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="section_id" name="section_id" required>
                                                <option value="">Select Section</option>
                                                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($section->id); ?>" <?php echo e(old('section_id', $assignment->section_id) == $section->id ? 'selected' : ''); ?>>
                                                        <?php echo e($section->name); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <?php $__errorArgs = ['section_id'];
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
                                                    <option value="<?php echo e($academicYear->id); ?>" <?php echo e(old('academic_year_id', $assignment->academic_year_id) == $academicYear->id ? 'selected' : ''); ?>>
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
                                                    <option value="<?php echo e($semester->id); ?>" <?php echo e(old('semester_id', $assignment->semester_id) == $semester->id ? 'selected' : ''); ?>>
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
                                                   id="due_date" name="due_date" value="<?php echo e(old('due_date', $assignment->due_date)); ?>" required>
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
                                                   id="due_time" name="due_time" value="<?php echo e(old('due_time', $assignment->due_time)); ?>">
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
                                                  id="description" name="description" rows="5" required><?php echo e(old('description', $assignment->description)); ?></textarea>
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

                                    <div class="mb-4">
                                        <label for="submission_instructions" class="form-label">Submission Instructions</label>
                                        <textarea class="form-control <?php $__errorArgs = ['submission_instructions'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                  id="submission_instructions" name="submission_instructions" rows="3"><?php echo e(old('submission_instructions', $assignment->submission_instructions)); ?></textarea>
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

                                <div class="col-md-4">
                                    
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">Assignment Settings</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="allows_late_submission" 
                                                           name="allows_late_submission" <?php echo e(old('allows_late_submission', $assignment->allows_late_submission) ? 'checked' : ''); ?>>
                                                    <label class="form-check-label" for="allows_late_submission">
                                                        Allow Late Submissions
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="mb-3" id="late_penalty_div" style="display: <?php echo e($assignment->allows_late_submission ? 'block' : 'none'); ?>;">
                                                <label for="late_submission_penalty" class="form-label">Late Submission Penalty (%)</label>
                                                <input type="number" class="form-control" id="late_submission_penalty" 
                                                       name="late_submission_penalty" value="<?php echo e(old('late_submission_penalty', $assignment->late_submission_penalty)); ?>" 
                                                       min="0" max="100">
                                            </div>

                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="requires_file_upload" 
                                                           name="requires_file_upload" <?php echo e(old('requires_file_upload', $assignment->requires_file_upload) ? 'checked' : ''); ?>>
                                                    <label class="form-check-label" for="requires_file_upload">
                                                        Require File Upload
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="mb-3" id="file_settings_div" style="display: <?php echo e($assignment->requires_file_upload ? 'block' : 'none'); ?>;">
                                                <label class="form-label mb-2">Allowed File Types</label>
                                                <?php
                                                    $allowedTypes = old('allowed_file_types', is_array($assignment->allowed_file_types) ? $assignment->allowed_file_types : []);
                                                    $allowedTypes = array_map('strtolower', (array) $allowedTypes);
                                                ?>
                                                <div class="border rounded p-3 bg-light">
                                                    <div class="mb-3">
                                                        <div class="fw-semibold text-dark mb-2"><i class="fas fa-file-alt me-1"></i> Documents</div>
                                                        <div class="d-flex flex-wrap gap-3">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="allowed_file_types[]" value="pdf" id="type_pdf"
                                                                       <?php echo e(in_array('pdf', $allowedTypes, true) ? 'checked' : ''); ?>>
                                                                <label class="form-check-label" for="type_pdf">PDF</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="allowed_file_types[]" value="docx" id="type_docx"
                                                                       <?php echo e(in_array('docx', $allowedTypes, true) ? 'checked' : ''); ?>>
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
                                                                           <?php echo e(in_array($value, $allowedTypes, true) ? 'checked' : ''); ?>>
                                                                    <label class="form-check-label" for="type_<?php echo e($value); ?>"><?php echo e($label); ?></label>
                                                                </div>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted">Tick the formats students may submit. JPEG is accepted when JPG is selected.</small>
                                            </div>

                                            <div class="mb-3" id="file_size_div" style="display: <?php echo e($assignment->requires_file_upload ? 'block' : 'none'); ?>;">
                                                <label for="max_file_size" class="form-label">Maximum File Size (MB)</label>
                                                <input type="number" class="form-control" id="max_file_size" 
                                                       name="max_file_size" value="<?php echo e(old('max_file_size', $assignment->max_file_size)); ?>" 
                                                       min="1" max="50">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="<?php echo e(route('assignments.show', $assignment->id)); ?>" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Assignment
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

<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
    $(document).ready(function() {
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

        $('#selectAllImagesBtn').on('click', function() {
            const $checks = $('.image-type-check');
            const allChecked = $checks.length && $checks.filter(':checked').length === $checks.length;
            $checks.prop('checked', !allChecked);
            $(this).text(allChecked ? 'Select all images' : 'Clear images');
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\assignments\edit.blade.php ENDPATH**/ ?>