<?php $__env->startSection('content'); ?>

    
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">System Settings</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo e(route('setting/page')); ?>">Settings</a></li>
                            <li class="breadcrumb-item active">General Settings</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="settings-menu-links mb-4">
                <ul class="nav nav-tabs menu-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo e(route('setting/page')); ?>">
                            <i class="fas fa-cog me-2"></i>General Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('setting.access-limits')); ?>">
                            <i class="fas fa-user-lock me-2"></i>Access Control
                        </a>
                    </li>
                </ul>
            </div>

            <form action="<?php echo e(route('setting/update')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="row">
                    <!-- Website Basic Details -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-globe me-2 text-primary"></i>Website Basic Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Website Name -->
                                <div class="form-group mb-3">
                                    <label class="form-label">Website Name <span class="text-danger">*</span></label>
                                    <input type="text" name="website_name" class="form-control <?php $__errorArgs = ['website_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           placeholder="Enter Website Name" value="<?php echo e(old('website_name', $settings->website_name)); ?>" required>
                                    <?php $__errorArgs = ['website_name'];
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

                                <!-- Logo Upload -->
                                <div class="form-group mb-3">
                                    <label class="form-label">Logo <span class="text-danger">*</span></label>
                                    <div class="settings-btn">
                                        <input type="file" accept="image/png,image/jpg,image/jpeg" name="logo" id="logo-file"
                                            class="form-control <?php $__errorArgs = ['logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" onchange="previewLogo(event)">
                                        <?php $__errorArgs = ['logo'];
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
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Recommended image size is <strong>150px x 150px</strong>. Max 2MB.
                                    </small>
                                    
                                    <?php if($settings->logo): ?>
                                        <div class="upload-images mt-2" id="current-logo">
                                            <img src="<?php echo e(asset('storage/' . $settings->logo)); ?>" alt="Logo" class="img-thumbnail" style="max-width: 150px;">
                                            <button type="button" class="btn btn-sm btn-danger ms-2" onclick="deleteFile('logo')">
                                                <i class="fas fa-times"></i> Remove
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="upload-images mt-2 d-none" id="logo-preview">
                                        <img src="" alt="Logo Preview" class="img-thumbnail" style="max-width: 150px;">
                                    </div>
                                </div>

                                <!-- Favicon Upload -->
                                <div class="form-group mb-3">
                                    <label class="form-label">Favicon <span class="text-danger">*</span></label>
                                    <div class="settings-btn">
                                        <input type="file" accept="image/png,.ico" name="favicon" id="favicon-file"
                                            class="form-control <?php $__errorArgs = ['favicon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" onchange="previewFavicon(event)">
                                        <?php $__errorArgs = ['favicon'];
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
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Recommended size: <strong>16px x 16px or 32px x 32px</strong><br>
                                        Accepted formats: <strong>PNG and ICO only</strong>. Max 1MB.
                                    </small>
                                    
                                    <?php if($settings->favicon): ?>
                                        <div class="upload-images mt-2" id="current-favicon">
                                            <img src="<?php echo e(asset('storage/' . $settings->favicon)); ?>" alt="Favicon" class="img-thumbnail" style="max-width: 32px;">
                                            <button type="button" class="btn btn-sm btn-danger ms-2" onclick="deleteFile('favicon')">
                                                <i class="fas fa-times"></i> Remove
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="upload-images mt-2 d-none" id="favicon-preview">
                                        <img src="" alt="Favicon Preview" class="img-thumbnail" style="max-width: 32px;">
                                    </div>
                                </div>

                                <!-- RTL Toggle -->
                                <div class="form-group mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="rtl_enabled" id="rtl_enabled" 
                                               <?php echo e($settings->rtl_enabled ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="rtl_enabled">
                                            <i class="fas fa-text-width me-2"></i>Enable RTL (Right-to-Left) Layout
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Enable this for Arabic, Hebrew, or other RTL languages</small>
                                </div>

                                <!-- Contact Info -->
                                <div class="form-group mb-3">
                                    <label class="form-label">Contact Email</label>
                                    <input type="email" name="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           placeholder="contact@school.edu" value="<?php echo e(old('email', $settings->email)); ?>">
                                    <?php $__errorArgs = ['email'];
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

                                <div class="form-group mb-0">
                                    <label class="form-label">Contact Phone</label>
                                    <input type="text" name="phone" class="form-control <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           placeholder="+1234567890" value="<?php echo e(old('phone', $settings->phone)); ?>">
                                    <?php $__errorArgs = ['phone'];
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

                    <!-- Address Details -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-map-marker-alt me-2 text-success"></i>Address Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                                    <input type="text" name="address_line_1" class="form-control <?php $__errorArgs = ['address_line_1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           placeholder="Enter Address Line 1" value="<?php echo e(old('address_line_1', $settings->address_line_1)); ?>">
                                    <?php $__errorArgs = ['address_line_1'];
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

                                <div class="form-group mb-3">
                                    <label class="form-label">Address Line 2</label>
                                    <input type="text" name="address_line_2" class="form-control <?php $__errorArgs = ['address_line_2'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           placeholder="Enter Address Line 2" value="<?php echo e(old('address_line_2', $settings->address_line_2)); ?>">
                                    <?php $__errorArgs = ['address_line_2'];
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

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">City <span class="text-danger">*</span></label>
                                            <input type="text" name="city" class="form-control <?php $__errorArgs = ['city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                   placeholder="City" value="<?php echo e(old('city', $settings->city)); ?>">
                                            <?php $__errorArgs = ['city'];
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
                                        <div class="form-group mb-3">
                                            <label class="form-label">State/Province <span class="text-danger">*</span></label>
                                            <input type="text" name="state_province" class="form-control <?php $__errorArgs = ['state_province'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                   placeholder="State/Province" value="<?php echo e(old('state_province', $settings->state_province)); ?>">
                                            <?php $__errorArgs = ['state_province'];
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

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Zip/Postal Code <span class="text-danger">*</span></label>
                                            <input type="text" name="zip_postal_code" class="form-control <?php $__errorArgs = ['zip_postal_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                   placeholder="Zip Code" value="<?php echo e(old('zip_postal_code', $settings->zip_postal_code)); ?>">
                                            <?php $__errorArgs = ['zip_postal_code'];
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
                                        <div class="form-group mb-3">
                                            <label class="form-label">Country <span class="text-danger">*</span></label>
                                            <input type="text" name="country" class="form-control <?php $__errorArgs = ['country'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                   placeholder="Country" value="<?php echo e(old('country', $settings->country)); ?>">
                                            <?php $__errorArgs = ['country'];
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

                                <!-- School Description -->
                                <div class="form-group mb-0">
                                    <label class="form-label">School Description</label>
                                    <textarea name="description" class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                              rows="4" placeholder="Brief description of your school..."><?php echo e(old('description', $settings->description)); ?></textarea>
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
                                    <small class="form-text text-muted">This will appear on public pages and reports</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Settings
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        // Preview logo before upload
        function previewLogo(event) {
            const preview = document.getElementById('logo-preview');
            const previewImg = preview.querySelector('img');
            const file = event.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            }
        }

        // Preview favicon before upload
        function previewFavicon(event) {
            const preview = document.getElementById('favicon-preview');
            const previewImg = preview.querySelector('img');
            const file = event.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            }
        }

        // Delete file (logo or favicon)
        function deleteFile(fileType) {
            if (!confirm('Are you sure you want to delete this ' + fileType + '?')) {
                return;
            }

            fetch('<?php echo e(route("setting/delete-file")); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                body: JSON.stringify({ file_type: fileType })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('current-' + fileType).remove();
                    toastr.success(data.message);
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Failed to delete file');
            });
        }
    </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/setting/settings.blade.php ENDPATH**/ ?>