<?php $__env->startSection('content'); ?>

<?php
    $teacher = $teacher ?? null;
    $student = $student ?? null;
    $children = $children ?? collect();

    $avatarUrl = \App\Support\AvatarUploader::url($user->avatar);
    if ($user->role_name === \App\Models\User::ROLE_STUDENT && !empty($student?->upload)) {
        $avatarUrl = \Illuminate\Support\Facades\Storage::url('student-photos/'.$student->upload);
    }

    $displayName = $user->name;
    if ($user->role_name === \App\Models\User::ROLE_STUDENT && !empty($student?->full_name)) {
        $displayName = $student->full_name;
    } elseif ($user->role_name === \App\Models\User::ROLE_TEACHER && !empty($teacher?->full_name)) {
        $displayName = $teacher->full_name;
    }

    $phone = $user->phone_number
        ?: ($teacher->phone_number ?? null)
        ?: ($student->phone_number ?? null);

    $status = strtolower((string) ($user->status ?: 'active'));
    $statusClass = $status === 'active' ? 'dir-badge--active' : 'dir-badge--disabled';
    $joinDate = $user->join_date ? \Carbon\Carbon::parse($user->join_date)->format('M d, Y') : null;
    $dob = $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('M d, Y') : null;
    if ($student && $student->date_of_birth) {
        $dob = \Carbon\Carbon::parse($student->date_of_birth)->format('M d, Y');
    } elseif ($teacher && $teacher->date_of_birth) {
        $dob = \Carbon\Carbon::parse($teacher->date_of_birth)->format('M d, Y');
    }

    $locationParts = [];
    if ($teacher) {
        foreach (['address', 'city', 'state', 'country'] as $locField) {
            if (!empty($teacher->{$locField})) {
                $locationParts[] = $teacher->{$locField};
            }
        }
    } elseif ($student && !empty($student->address)) {
        $locationParts[] = $student->address;
    }
    $location = implode(', ', $locationParts);
    $pwdErrors = $errors->has('current_password') || $errors->has('new_password') || $errors->has('new_password_confirmation');
?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Profile</h3>
                    <p class="dir-subtitle">Your account details from the school records.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <div class="dir-card mb-3">
            <div class="dir-profile-hero">
                <img class="dir-avatar" src="<?php echo e($avatarUrl); ?>" alt="<?php echo e($displayName); ?>">
                <div class="flex-grow-1">
                    <h4 class="mb-1" style="font-weight:750;"><?php echo e($displayName); ?></h4>
                    <div class="d-flex flex-wrap gap-1 mb-1">
                        <span class="dir-chip"><?php echo e($user->role_name); ?></span>
                        <?php if($user->user_id): ?>
                            <span class="dir-chip dir-chip--soft">ID <?php echo e($user->user_id); ?></span>
                        <?php endif; ?>
                        <span class="dir-badge <?php echo e($statusClass); ?>"><?php echo e(ucfirst($status)); ?></span>
                    </div>
                    <div class="dir-person-meta"><?php echo e($user->email); ?></div>
                    <?php if($location): ?>
                        <div class="dir-person-meta"><i class="fas fa-map-marker-alt me-1"></i><?php echo e($location); ?></div>
                    <?php endif; ?>
                </div>
                <a href="<?php echo e(route('user/profile/edit')); ?>" class="btn btn-primary dir-btn">
                    <i class="fas fa-pen me-1"></i> Edit
                </a>
            </div>
            <ul class="nav dir-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link <?php echo e($pwdErrors ? '' : 'active'); ?>" data-bs-toggle="tab" href="#per_details_tab">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e($pwdErrors ? 'active' : ''); ?>" data-bs-toggle="tab" href="#password_tab">Password</a>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade <?php echo e($pwdErrors ? '' : 'show active'); ?>" id="per_details_tab">
                <?php if($user->role_name === \App\Models\User::ROLE_STUDENT && isset($student)): ?>
                    <?php echo $__env->make('dashboard.partials.student_sis', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php else: ?>
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <div class="dir-card">
                                <div class="dir-toolbar">
                                    <h5 class="dir-toolbar-title mb-0">Personal details</h5>
                                </div>
                                <div class="p-4">
                                    <dl class="dir-dl">
                                        <dt>Full name</dt>
                                        <dd><?php echo e($displayName); ?></dd>
                                        <dt>Email</dt>
                                        <dd><?php echo e($user->email); ?></dd>
                                        <dt>Mobile</dt>
                                        <dd><?php echo e($phone ?: '—'); ?></dd>
                                        <dt>Role</dt>
                                        <dd><?php echo e($user->role_name); ?></dd>
                                        <?php if($user->position): ?>
                                            <dt>Position</dt>
                                            <dd><?php echo e($user->position); ?></dd>
                                        <?php endif; ?>
                                        <?php if($user->department): ?>
                                            <dt>Department</dt>
                                            <dd><?php echo e($user->department); ?></dd>
                                        <?php endif; ?>
                                        <?php if($user->user_id): ?>
                                            <dt>User ID</dt>
                                            <dd><?php echo e($user->user_id); ?></dd>
                                        <?php endif; ?>
                                        <?php if($joinDate): ?>
                                            <dt>Joined</dt>
                                            <dd><?php echo e($joinDate); ?></dd>
                                        <?php endif; ?>
                                        <?php if($dob): ?>
                                            <dt>Date of birth</dt>
                                            <dd><?php echo e($dob); ?></dd>
                                        <?php endif; ?>
                                    </dl>
                                </div>
                            </div>

                            <?php if($teacher): ?>
                                <div class="dir-card mt-3">
                                    <div class="dir-toolbar">
                                        <h5 class="dir-toolbar-title mb-0">Teacher record</h5>
                                    </div>
                                    <div class="p-4">
                                        <dl class="dir-dl">
                                            <?php if($teacher->teacher_id): ?>
                                                <dt>Teacher ID</dt>
                                                <dd><?php echo e($teacher->teacher_id); ?></dd>
                                            <?php endif; ?>
                                            <?php if($teacher->qualification): ?>
                                                <dt>Qualification</dt>
                                                <dd><?php echo e($teacher->qualification); ?></dd>
                                            <?php endif; ?>
                                            <?php if($teacher->experience): ?>
                                                <dt>Experience</dt>
                                                <dd><?php echo e($teacher->experience); ?></dd>
                                            <?php endif; ?>
                                            <?php if($teacher->gender): ?>
                                                <dt>Gender</dt>
                                                <dd><?php echo e($teacher->gender); ?></dd>
                                            <?php endif; ?>
                                            <?php if($location): ?>
                                                <dt>Address</dt>
                                                <dd><?php echo e($location); ?></dd>
                                            <?php endif; ?>
                                        </dl>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if($user->role_name === \App\Models\User::ROLE_PARENT && $children->count()): ?>
                                <div class="dir-card mt-3">
                                    <div class="dir-toolbar">
                                        <h5 class="dir-toolbar-title mb-0">Linked children</h5>
                                    </div>
                                    <div class="p-4">
                                        <?php $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="mb-2">
                                                <span class="dir-person-name"><?php echo e($child->full_name); ?></span>
                                                <span class="dir-person-meta"><?php echo e($child->year_level); ?> · <?php echo e($child->admission_id ?? $child->student_id); ?></span>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-lg-4">
                            <div class="dir-card">
                                <div class="dir-toolbar">
                                    <h5 class="dir-toolbar-title mb-0">Account status</h5>
                                </div>
                                <div class="p-4">
                                    <span class="dir-badge <?php echo e($statusClass); ?>"><?php echo e(ucfirst($status)); ?></span>
                                    <p class="dir-person-meta mt-3 mb-0">This status comes from your user account, not a placeholder.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div id="password_tab" class="tab-pane fade <?php echo e($pwdErrors ? 'show active' : ''); ?>">
                <div class="dir-card">
                    <div class="dir-toolbar">
                        <h5 class="dir-toolbar-title mb-0">Change password</h5>
                    </div>
                    <div class="p-4">
                        <form action="<?php echo e(route('user/password/update')); ?>" method="POST" class="col-lg-6 px-0">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label>Current password</label>
                                <input type="password" class="form-control <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="current_password" autocomplete="current-password">
                                <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="invalid-feedback" role="alert"><strong><?php echo e($message); ?></strong></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="form-group">
                                <label>New password</label>
                                <input type="password" class="form-control <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="new_password" autocomplete="new-password">
                                <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="invalid-feedback" role="alert"><strong><?php echo e($message); ?></strong></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="form-group">
                                <label>Confirm new password</label>
                                <input type="password" class="form-control <?php $__errorArgs = ['new_password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="new_password_confirmation" autocomplete="new-password">
                                <?php $__errorArgs = ['new_password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="invalid-feedback" role="alert"><strong><?php echo e($message); ?></strong></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <button type="submit" class="btn btn-primary dir-btn">Save password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914c">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/dashboard/profile.blade.php ENDPATH**/ ?>