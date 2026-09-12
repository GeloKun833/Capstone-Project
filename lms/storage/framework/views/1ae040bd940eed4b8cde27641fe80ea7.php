
<?php $__env->startSection('content'); ?>

    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Edit Announcement</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo e(route('announcements.index')); ?>">Announcements</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo e(route('announcements.show', $announcement->id)); ?>">View Announcement</a></li>
                            <li class="breadcrumb-item active">Edit Announcement</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo e(route('announcements.update', $announcement->id)); ?>" method="POST" id="announcementForm" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="form-title"><span>Announcement Information</span></h5>
                                    </div>
                                    
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
                                                   name="title" value="<?php echo e(old('title', $announcement->title)); ?>" required>
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
                                                <option value="general" <?php echo e(old('type', $announcement->type) == 'general' ? 'selected' : ''); ?>>General</option>
                                                <option value="academic" <?php echo e(old('type', $announcement->type) == 'academic' ? 'selected' : ''); ?>>Academic</option>
                                                <option value="event" <?php echo e(old('type', $announcement->type) == 'event' ? 'selected' : ''); ?>>Event</option>
                                                <option value="reminder" <?php echo e(old('type', $announcement->type) == 'reminder' ? 'selected' : ''); ?>>Reminder</option>
                                                <option value="emergency" <?php echo e(old('type', $announcement->type) == 'emergency' ? 'selected' : ''); ?>>Emergency</option>
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
                                                <option value="low" <?php echo e(old('priority', $announcement->priority) == 'low' ? 'selected' : ''); ?>>Low</option>
                                                <option value="normal" <?php echo e(old('priority', $announcement->priority) == 'normal' ? 'selected' : ''); ?>>Normal</option>
                                                <option value="high" <?php echo e(old('priority', $announcement->priority) == 'high' ? 'selected' : ''); ?>>High</option>
                                                <option value="urgent" <?php echo e(old('priority', $announcement->priority) == 'urgent' ? 'selected' : ''); ?>>Urgent</option>
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
                                                <option value="all" <?php echo e(old('target_audience', $announcement->target_audience) == 'all' ? 'selected' : ''); ?>>All Users</option>
                                                <option value="students" <?php echo e(old('target_audience', $announcement->target_audience) == 'students' ? 'selected' : ''); ?>>Students Only</option>
                                                <option value="teachers" <?php echo e(old('target_audience', $announcement->target_audience) == 'teachers' ? 'selected' : ''); ?>>Teachers Only</option>
                                                <option value="parents" <?php echo e(old('target_audience', $announcement->target_audience) == 'parents' ? 'selected' : ''); ?>>Parents Only</option>
                                                <option value="admins" <?php echo e(old('target_audience', $announcement->target_audience) == 'admins' ? 'selected' : ''); ?>>Admins Only</option>
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
                                                      name="content" rows="6" required><?php echo e(old('content', $announcement->content)); ?></textarea>
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
                                            <label>Current Attachments</label>
                                            <?php $files = $announcement->attachments ?? []; ?>
                                            <?php if(empty($files)): ?>
                                                <p class="text-muted mb-2">No files attached yet.</p>
                                            <?php else: ?>
                                                <div class="list-group mb-3">
                                                    <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <label class="list-group-item d-flex justify-content-between align-items-center">
                                                            <span>
                                                                <i class="fas fa-paperclip me-2"></i>
                                                                <?php echo e($file['original_name'] ?? basename($file['path'] ?? 'file')); ?>

                                                            </span>
                                                            <span>
                                                                <a class="btn btn-sm btn-outline-primary me-2" target="_blank"
                                                                   href="<?php echo e(asset('storage/' . ($file['path'] ?? ''))); ?>">View</a>
                                                                <input type="checkbox" name="remove_attachments[]" value="<?php echo e($file['path'] ?? ''); ?>"> Remove
                                                            </span>
                                                        </label>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            <?php endif; ?>
                                            <label class="mt-2">Add more files</label>
                                            <input type="file" class="form-control" name="attachments[]" multiple
                                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp,.txt,.xls,.xlsx,.ppt,.pptx,image/*">
                                            <small class="form-text text-muted">PDF, Word, Excel, PowerPoint, images, or TXT. Max 5 total, 10MB each.</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <h5 class="form-title"><span>Advanced Options</span></h5>
                                    </div>
                                    
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label>Specific Roles (Optional)</label>
                                            <select class="form-control select2" name="target_roles[]" multiple>
                                                <option value="students" <?php echo e(in_array('students', old('target_roles', $announcement->target_roles ?? [])) ? 'selected' : ''); ?>>Students</option>
                                                <option value="teachers" <?php echo e(in_array('teachers', old('target_roles', $announcement->target_roles ?? [])) ? 'selected' : ''); ?>>Teachers</option>
                                                <option value="parents" <?php echo e(in_array('parents', old('target_roles', $announcement->target_roles ?? [])) ? 'selected' : ''); ?>>Parents</option>
                                                <option value="admins" <?php echo e(in_array('admins', old('target_roles', $announcement->target_roles ?? [])) ? 'selected' : ''); ?>>Admins</option>
                                            </select>
                                            <small class="form-text text-muted">Leave empty to use target audience above</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label>Specific Sections (Optional)</label>
                                            <select class="form-control select2" name="target_sections[]" multiple>
                                                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($section->id); ?>" 
                                                            <?php echo e(in_array($section->id, old('target_sections', $announcement->target_sections ?? [])) ? 'selected' : ''); ?>>
                                                        <?php echo e($section->name); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <small class="form-text text-muted">Leave empty to target all sections</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label>Scheduled Date (Optional)</label>
                                            <input type="datetime-local" class="form-control" name="scheduled_at" 
                                                   value="<?php echo e(old('scheduled_at', $announcement->scheduled_at ? $announcement->scheduled_at->format('Y-m-d\TH:i') : '')); ?>">
                                            <small class="form-text text-muted">Leave empty to publish immediately</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label>Expiration Date (Optional)</label>
                                            <input type="datetime-local" class="form-control" name="expires_at" 
                                                   value="<?php echo e(old('expires_at', $announcement->expires_at ? $announcement->expires_at->format('Y-m-d\TH:i') : '')); ?>">
                                            <small class="form-text text-muted">Leave empty for no expiration</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="is_pinned" id="is_pinned" 
                                                       value="1" <?php echo e(old('is_pinned', $announcement->is_pinned) ? 'checked' : ''); ?>>
                                                <label class="form-check-label" for="is_pinned">
                                                    Pin this announcement to the top
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="is_scheduled" id="is_scheduled" 
                                                       value="1" <?php echo e(old('is_scheduled', $announcement->is_scheduled) ? 'checked' : ''); ?>>
                                                <label class="form-check-label" for="is_scheduled">
                                                    Schedule this announcement for later
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">Update Announcement</button>
                                        <a href="<?php echo e(route('announcements.show', $announcement->id)); ?>" class="btn btn-secondary">Cancel</a>
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

<?php $__env->startPush('scripts'); ?>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: "Select options",
            allowClear: true
        });
        
        // Form validation
        $('#announcementForm').on('submit', function(e) {
            let isValid = true;
            
            // Check required fields
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
        
        // Scheduled date toggle
        $('#is_scheduled').change(function() {
            if ($(this).is(':checked')) {
                $('input[name="scheduled_at"]').prop('required', true);
            } else {
                $('input[name="scheduled_at"]').prop('required', false);
            }
        });
    });
</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\announcements\edit.blade.php ENDPATH**/ ?>