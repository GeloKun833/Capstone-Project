<?php $__env->startSection('content'); ?>
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Edit User</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('list/users')); ?>">Users</a></li>
                            <li class="breadcrumb-item active">Edit User</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo e(route('user/update')); ?>" method="POST" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="form-title"><span>Edit User</span></h5>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Name <span class="login-danger">*</span></label>
                                            <input type="text" class="form-control" name="name" value="<?php echo e(old('name', $users->name)); ?>" pattern="[\p{L}\p{M}\s'\-\.]+" title="Letters only — no emojis">
                                            <input type="hidden" class="form-control" name="user_id" value="<?php echo e($users->user_id); ?>">
                                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Email <span class="login-danger">*</span></label>
                                            <input type="email" class="form-control" name="email" value="<?php echo e(old('email', $users->email)); ?>">
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Phone Number <span class="login-danger">*</span></label>
                                            <input type="tel" class="form-control" name="phone_number" value="<?php echo e(old('phone_number', $users->phone_number)); ?>" inputmode="numeric" pattern="[0-9+\-\s()]+" title="Numbers only">
                                            <?php $__errorArgs = ['phone_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Date Of Birth</label>
                                            <input type="date" class="form-control js-dob" name="date_of_birth" max="<?php echo e(date('Y-m-d')); ?>" min="1950-01-01" value="<?php echo e(old('date_of_birth', $users->date_of_birth ? \Illuminate\Support\Str::of($users->date_of_birth)->substr(0, 10) : '')); ?>">
                                            <?php $__errorArgs = ['date_of_birth'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Status <span class="login-danger">*</span></label>
                                            <?php $currentStatus = old('status', $users->status); ?>
                                            <select class="form-control" name="status" <?php if(!empty($isSoleAdmin)): ?> data-sole-admin="1" <?php endif; ?>>
                                                <option value="Active" <?php echo e(\App\Models\User::isActiveStatus($currentStatus) ? 'selected' : ''); ?>>Active</option>
                                                <option value="Inactive" <?php echo e(strtolower((string) $currentStatus) === 'inactive' ? 'selected' : ''); ?> <?php if(!empty($isSoleAdmin)): ?> disabled <?php endif; ?>>Inactive</option>
                                                <option value="Disable" <?php echo e(in_array(strtolower((string) $currentStatus), ['disable', 'disabled'], true) ? 'selected' : ''); ?> <?php if(!empty($isSoleAdmin)): ?> disabled <?php endif; ?>>Disable</option>
                                            </select>
                                            <?php if(!empty($isSoleAdmin)): ?>
                                                <small class="text-warning">This is the only active Admin — status is locked.</small>
                                                <input type="hidden" name="status" value="<?php echo e($users->status); ?>">
                                            <?php endif; ?>
                                            <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Role</label>
                                            <input type="text" class="form-control" value="<?php echo e($users->role_name); ?>" readonly>
                                            <small class="form-text text-muted">Role is set when the account is created and cannot be changed here.</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Profile Image</label>
                                            <input type="file" class="form-control <?php $__errorArgs = ['avatar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                   name="avatar" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                            <small class="form-text text-muted">Optional. JPG, PNG, GIF, or WEBP. Max 2MB.</small>
                                            <?php $__errorArgs = ['avatar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            <?php if(!empty($users->avatar)): ?>
                                                <div class="user-img mt-2">
                                                    <img class="rounded-circle" src="<?php echo e(\App\Support\AvatarUploader::url($users->avatar)); ?>"
                                                         alt="Current profile" style="width:64px;height:64px;object-fit:cover;">
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <input type="hidden" name="hidden_avatar" value="<?php echo e($users->avatar); ?>">
                                    </div>

                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Position <span class="login-danger">*</span></label>
                                            <input type="text" class="form-control" name="position" value="<?php echo e(old('position', $users->position)); ?>">
                                            <?php $__errorArgs = ['position'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Department <span class="login-danger">*</span></label>
                                            <input type="text" class="form-control" name="department" value="<?php echo e(old('department', $users->department)); ?>">
                                            <?php $__errorArgs = ['department'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Updated Date</label>
                                            <input type="text" class="form-control" name="updated_at" value="<?php echo e($users->updated_at); ?>" readonly>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <h5 class="form-title"><span>Reset Password (Admin)</span></h5>
                                        <p class="text-muted small">Optional. Use this if the user forgot their password and cannot use email reset. Leave blank to keep the current password.</p>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group local-forms">
                                            <label>New Password</label>
                                            <input type="password" class="form-control <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                   name="new_password" autocomplete="new-password" placeholder="Leave blank to keep current">
                                            <?php $__errorArgs = ['new_password'];
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
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group local-forms">
                                            <label>Confirm New Password</label>
                                            <input type="password" class="form-control" name="new_password_confirmation" autocomplete="new-password" placeholder="Confirm new password">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="student-submit">
                                            <button type="submit" class="btn btn-primary">Update</button>
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

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/usermanagement/user_update.blade.php ENDPATH**/ ?>