
<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Create Announcement</h3>
                    <p class="dir-subtitle">Publish a notice for students, teachers, parents, or staff.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('announcements.index')); ?>">Announcements</a></li>
                        <li class="breadcrumb-item active">Create Announcement</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="dir-card">
            <div class="p-4">
                <form action="<?php echo e(route('announcements.store')); ?>" method="POST" id="announcementForm" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <h5 class="dir-toolbar-title mb-3">Announcement information</h5>
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       name="title" value="<?php echo e(old('title')); ?>" required>
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
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Type <span class="text-danger">*</span></label>
                                <select class="form-control <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="type" required>
                                    <option value="">Select Type</option>
                                    <?php $__currentLoopData = ['general','academic','event','reminder','emergency']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($type); ?>" <?php echo e(old('type') == $type ? 'selected' : ''); ?>><?php echo e(ucfirst($type)); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Priority <span class="text-danger">*</span></label>
                                <select class="form-control <?php $__errorArgs = ['priority'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="priority" required>
                                    <option value="">Select Priority</option>
                                    <?php $__currentLoopData = ['low','normal','high','urgent']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($priority); ?>" <?php echo e(old('priority') == $priority ? 'selected' : ''); ?>><?php echo e(ucfirst($priority)); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Target Audience <span class="text-danger">*</span></label>
                                <select class="form-control <?php $__errorArgs = ['target_audience'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="target_audience" required>
                                    <option value="">Select Audience</option>
                                    <option value="all" <?php echo e(old('target_audience') == 'all' ? 'selected' : ''); ?>>All Users</option>
                                    <option value="students" <?php echo e(old('target_audience') == 'students' ? 'selected' : ''); ?>>Students Only</option>
                                    <option value="teachers" <?php echo e(old('target_audience') == 'teachers' ? 'selected' : ''); ?>>Teachers Only</option>
                                    <option value="parents" <?php echo e(old('target_audience') == 'parents' ? 'selected' : ''); ?>>Parents Only</option>
                                    <option value="admins" <?php echo e(old('target_audience') == 'admins' ? 'selected' : ''); ?>>Admins Only</option>
                                </select>
                                <?php $__errorArgs = ['target_audience'];
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
                        <div class="col-12">
                            <div class="form-group">
                                <label>Content <span class="text-danger">*</span></label>
                                <textarea class="form-control <?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                          name="content" rows="6" required><?php echo e(old('content')); ?></textarea>
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
                        <div class="col-12">
                            <div class="form-group">
                                <label>Attachments <span class="text-muted">(optional)</span></label>
                                <input type="file" class="form-control <?php $__errorArgs = ['attachments'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> <?php $__errorArgs = ['attachments.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       name="attachments[]" multiple
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp,.txt,.xls,.xlsx,.ppt,.pptx,image/*">
                                <small class="text-muted">PDF, Word, Excel, PowerPoint, images, or TXT. Max 5 files, 10MB each.</small>
                                <?php $__errorArgs = ['attachments'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <?php $__errorArgs = ['attachments.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>

                    <h5 class="dir-toolbar-title mt-2 mb-3">Advanced options</h5>
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Specific Roles (Optional)</label>
                                <select class="form-control select2" name="target_roles[]" multiple>
                                    <option value="students">Students</option>
                                    <option value="teachers">Teachers</option>
                                    <option value="parents">Parents</option>
                                    <option value="admins">Admins</option>
                                </select>
                                <small class="text-muted">Leave empty to use target audience above</small>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Specific Sections (Optional)</label>
                                <select class="form-control select2" name="target_sections[]" multiple>
                                    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($section->id); ?>"><?php echo e($section->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <small class="text-muted">Leave empty to target all sections</small>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group mdp-field">
                                <label>Scheduled Date (Optional)</label>
                                <input type="datetime-local" class="form-control js-event-datetime" name="scheduled_at"
                                       value="<?php echo e(old('scheduled_at')); ?>" autocomplete="off" placeholder="YYYY-MM-DD HH:mm">
                                <small class="text-muted">Leave empty to publish immediately</small>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group mdp-field">
                                <label>Expiration Date (Optional)</label>
                                <input type="datetime-local" class="form-control js-event-datetime" name="expires_at"
                                       value="<?php echo e(old('expires_at')); ?>" autocomplete="off" placeholder="YYYY-MM-DD HH:mm">
                                <small class="text-muted">Leave empty for no expiration</small>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 mb-3">
                            <label class="dir-check mb-0">
                                <input type="checkbox" class="form-check-input m-0" name="is_pinned" id="is_pinned"
                                       value="1" <?php echo e(old('is_pinned') ? 'checked' : ''); ?>>
                                Pin this announcement to the top
                            </label>
                        </div>
                        <div class="col-12 col-sm-6 mb-3">
                            <label class="dir-check mb-0">
                                <input type="checkbox" class="form-check-input m-0" name="is_scheduled" id="is_scheduled"
                                       value="1" <?php echo e(old('is_scheduled') ? 'checked' : ''); ?>>
                                Schedule this announcement for later
                            </label>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary dir-btn">Create Announcement</button>
                            <a href="<?php echo e(route('announcements.index')); ?>" class="btn btn-outline-secondary dir-btn">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914c">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select options",
            allowClear: true,
            width: '100%'
        });

        $('#announcementForm').on('submit', function(e) {
            let isValid = true;
            $('input[required], select[required], textarea[required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });

        $('#is_scheduled').change(function() {
            $('input[name="scheduled_at"]').prop('required', $(this).is(':checked'));
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/announcements/create.blade.php ENDPATH**/ ?>