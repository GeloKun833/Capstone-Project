
<?php $__env->startSection('content'); ?>


<div class="page-wrapper">
    <div class="content container-fluid dir-page">

        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Create Class Post</h3>
                    <p class="dir-subtitle">Share an announcement, resource, or reminder with a class.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-2">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('class-posts.index')); ?>">Class Posts</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ul>
                    <a href="<?php echo e(route('class-posts.index')); ?>" class="btn btn-outline-secondary dir-btn">Back</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="dir-card">
                    <div class="p-3 p-md-4">
                        
                        <?php if($errors->any()): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong><i class="fas fa-exclamation-triangle"></i> Error!</strong>
                                <ul class="mb-0 mt-2">
                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo e(route('class-posts.store')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-1">Class</h5>
                                    <p class="text-muted small mb-3">Select a section first. Subjects then show only for that section.</p>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Section <span class="text-danger">*</span></label>
                                        <select id="section_id" name="section_id" class="form-control <?php $__errorArgs = ['section_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required <?php if($sections->isEmpty()): ?> disabled <?php endif; ?>>
                                            <option value="">Select Section</option>
                                            <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($section->id); ?>" <?php echo e((string) old('section_id') === (string) $section->id ? 'selected' : ''); ?>>
                                                    <?php echo e($section->name); ?><?php echo e($section->grade_level ? ' (' . $section->grade_level . ')' : ''); ?>

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
                                        <?php if($sections->isEmpty()): ?>
                                            <small class="form-text text-muted">No sections are assigned to you yet.</small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                                        <select id="subject_id" name="subject_id" class="form-control <?php $__errorArgs = ['subject_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required disabled>
                                            <option value="">Select section first</option>
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
                                        <small class="form-text text-muted">Subjects appear after you pick a section.</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                                        <select name="academic_year_id" class="form-control <?php $__errorArgs = ['academic_year_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                            <option value="">Select Academic Year</option>
                                            <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($year->id); ?>" <?php echo e(old('academic_year_id') == $year->id ? 'selected' : ''); ?>>
                                                    <?php echo e($year->name); ?>

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
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Semester <span class="text-danger">*</span></label>
                                        <select name="semester_id" class="form-control <?php $__errorArgs = ['semester_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
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

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Type <span class="text-danger">*</span></label>
                                        <select name="type" class="form-control <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                            <option value="">Select Type</option>
                                            <option value="announcement" <?php echo e(old('type') == 'announcement' ? 'selected' : ''); ?>>Announcement</option>
                                            <option value="resource" <?php echo e(old('type') == 'resource' ? 'selected' : ''); ?>>Resource</option>
                                            <option value="discussion" <?php echo e(old('type') == 'discussion' ? 'selected' : ''); ?>>Discussion</option>
                                            <option value="reminder" <?php echo e(old('type') == 'reminder' ? 'selected' : ''); ?>>Reminder</option>
                                        </select>
                                        <?php $__errorArgs = ['type'];
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

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Priority <span class="text-danger">*</span></label>
                                        <select name="priority" class="form-control <?php $__errorArgs = ['priority'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                            <option value="">Select Priority</option>
                                            <option value="low" <?php echo e(old('priority') == 'low' ? 'selected' : ''); ?>>Low</option>
                                            <option value="normal" <?php echo e(old('priority') == 'normal' ? 'selected' : ''); ?>>Normal</option>
                                            <option value="high" <?php echo e(old('priority') == 'high' ? 'selected' : ''); ?>>High</option>
                                            <option value="urgent" <?php echo e(old('priority') == 'urgent' ? 'selected' : ''); ?>>Urgent</option>
                                        </select>
                                        <?php $__errorArgs = ['priority'];
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

                                <div class="col-md-12">
                                    <h5 class="mb-3 mt-3">Post Information</h5>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               value="<?php echo e(old('title')); ?>" placeholder="Enter post title" required>
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
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Content <span class="text-danger">*</span></label>
                                        <textarea name="content" rows="6" class="form-control <?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                  placeholder="Enter post content" required><?php echo e(old('content')); ?></textarea>
                                        <?php $__errorArgs = ['content'];
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

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Expires At (Optional)</label>
                                        <input type="date" name="expires_at" class="form-control <?php $__errorArgs = ['expires_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                               value="<?php echo e(old('expires_at')); ?>" min="<?php echo e(date('Y-m-d')); ?>">
                                        <?php $__errorArgs = ['expires_at'];
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

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Attachment (Optional)</label>
                                        <input type="file" name="post_file" class="form-control <?php $__errorArgs = ['post_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                               accept=".pdf,.docx,.pptx,.txt,.jpg,.jpeg,.png">
                                        <small class="form-text text-muted">Max size: 10MB. Allowed: PDF, Word (DOCX), PowerPoint (PPTX), TXT, JPG, PNG</small>
                                        <?php $__errorArgs = ['post_file'];
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

                                
                                <div class="col-md-12">
                                    <h5 class="mb-3 mt-3">Post Options</h5>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_pinned" id="is_pinned" 
                                               <?php echo e(old('is_pinned') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_pinned">
                                            <i class="fas fa-thumbtack"></i> Pin this post
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="allows_comments" id="allows_comments" 
                                               <?php echo e(old('allows_comments', true) ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="allows_comments">
                                            <i class="fas fa-comments"></i> Allow comments
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="requires_confirmation" id="requires_confirmation" 
                                               <?php echo e(old('requires_confirmation') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="requires_confirmation">
                                            <i class="fas fa-check-circle"></i> Require confirmation
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save"></i> Create Post
                                </button>
                                <a href="<?php echo e(route('class-posts.index')); ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <div class="modal-body text-center" style="padding: 40px;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #28a745, #20c997); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);">
                    <i class="fas fa-check" style="font-size: 40px; color: white;"></i>
                </div>
                <h4 style="color: #2c323f; font-weight: 700; margin-bottom: 15px; font-size: 1.5rem;">Post Created Successfully!</h4>
                <p style="color: #6c757d; margin-bottom: 30px; font-size: 1rem;">Your class post has been published and is now visible to students.</p>
                <button type="button" class="btn btn-success" onclick="closeSuccessModal()" style="padding: 12px 40px; font-size: 1rem; font-weight: 600; border-radius: 8px; background: linear-gradient(135deg, #28a745, #20c997); border: none; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);">
                    <i class="fas fa-check"></i> Got it!
                </button>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914k">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
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
    const oldSubjectId = <?php echo json_encode(old('subject_id'), 15, 512) ?>;
    let restoreSubjectId = oldSubjectId;

    function filterSubjects() {
        const sectionId = $('#section_id').val();
        const $subject = $('#subject_id');
        const current = restoreSubjectId || $subject.val();

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

    $('#section_id').on('change', function() {
        restoreSubjectId = null;
        filterSubjects();
    });

    filterSubjects();
});
</script>
<script>
// Show success modal if there's a success message
<?php if(session('success')): ?>
    $(document).ready(function() {
        // Show immediate Toastr notification
        toastr.success('<?php echo e(session('success')); ?>', 'Success', {
            closeButton: true,
            progressBar: true,
            timeOut: 3000
        });
        
        // Also show the modal
        setTimeout(function() {
            showSuccessModal();
        }, 100);
    });
<?php endif; ?>

function showSuccessModal() {
    const modal = document.getElementById('successModal');
    
    // Try Bootstrap 5 modal first
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    } 
    // Try jQuery/Bootstrap 4 modal
    else if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
        $('#successModal').modal('show');
    }
    // Pure JavaScript fallback
    else {
        modal.style.display = 'block';
        modal.classList.add('show');
        modal.classList.add('d-block');
        document.body.classList.add('modal-open');
        
        // Create backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'customBackdrop';
        document.body.appendChild(backdrop);
    }
}

function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    
    // Try Bootstrap 5 modal first
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        }
    }
    // Try jQuery/Bootstrap 4 modal
    else if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
        $('#successModal').modal('hide');
    }
    // Pure JavaScript fallback
    else {
        modal.style.display = 'none';
        modal.classList.remove('show');
        modal.classList.remove('d-block');
        document.body.classList.remove('modal-open');
        
        // Remove backdrop
        const backdrop = document.getElementById('customBackdrop');
        if (backdrop) {
            backdrop.remove();
        }
    }
}

// Client-side file validation
document.querySelector('input[name="post_file"]')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Check file size (10MB = 10240 KB)
        const maxSize = 10240 * 1024; // 10MB in bytes
        if (file.size > maxSize) {
            alert('File size must not exceed 10MB.');
            this.value = '';
            return;
        }
        
        // Check file type
        const allowedExtensions = ['pdf', 'docx', 'pptx', 'txt', 'jpg', 'jpeg', 'png'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        
        if (!allowedExtensions.includes(fileExtension)) {
            alert('Invalid file type. Allowed: PDF, Word (DOCX), PowerPoint (PPTX), TXT, JPG, PNG.');
            this.value = '';
            return;
        }
    }
});

// Add loading state to submit button
document.querySelector('form').addEventListener('submit', function() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/class-posts/create.blade.php ENDPATH**/ ?>