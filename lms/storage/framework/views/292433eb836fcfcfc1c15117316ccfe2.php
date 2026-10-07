<?php $__env->startSection('content'); ?>
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Transfer Teacher Assignments</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(url('view/user/edit/'.$user->user_id)); ?>">Edit Teacher</a></li>
                            <li class="breadcrumb-item active">Transfer Assignments</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning">
                This teacher cannot be deactivated yet. The teacher still has active assignments for Academic Year <strong><?php echo e($year->displayName()); ?></strong>. Please transfer all assignments to another available teacher before deactivating the account.
            </div>

            <?php if($blocked): ?>
                <div class="alert alert-danger">
                    Teacher cannot be deactivated. There is no available teacher who can take over the current assignment. Please assign or activate another eligible teacher first.
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <p class="mb-1"><strong>Teacher:</strong> <?php echo e($teacher->full_name ?: $user->name); ?></p>
                    <p class="mb-3"><strong>Academic Year:</strong> <?php echo e($year->displayName()); ?></p>

                    <form method="POST" action="<?php echo e(route('teacher.deactivate.review', $user->user_id)); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Section</th>
                                        <th>Grade Level</th>
                                        <th>Subject</th>
                                        <th>Schedule</th>
                                        <th>Transfer To</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($row['section']); ?></td>
                                            <td><?php echo e($row['grade']); ?></td>
                                            <td><?php echo e($row['subject']); ?></td>
                                            <td><?php echo e($row['schedule']); ?></td>
                                            <td>
                                                <select class="form-control" name="replacements[<?php echo e($row['key']); ?>]" <?php if($blocked || ! $row['has_eligible']): echo 'disabled'; endif; ?> required>
                                                    <option value="">Select Teacher</option>
                                                    <?php $__currentLoopData = $row['choices']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $choice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($choice['id']); ?>" <?php if(! $choice['eligible']): echo 'disabled'; endif; ?> <?php if((string) old('replacements.'.$row['key']) === (string) $choice['id']): echo 'selected'; endif; ?>>
                                                            <?php echo e($choice['name']); ?><?php echo e($choice['reason'] ? ' — schedule conflict' : ''); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <?php $__currentLoopData = $row['choices']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $choice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if($choice['reason']): ?>
                                                        <small class="text-danger d-block"><?php echo e($choice['reason']); ?></small>
                                                    <?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php $__errorArgs = ['replacements.'.$row['key']];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <small class="text-danger d-block"><?php echo e($message); ?></small>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                <?php $__errorArgs = [$row['key']];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <small class="text-danger d-block"><?php echo e($message); ?></small>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?php echo e(url('view/user/edit/'.$user->user_id)); ?>" class="btn btn-outline-secondary">Cancel</a>
                            <?php if (! ($blocked)): ?>
                                <button type="submit" class="btn btn-primary">Review Transfer</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/usermanagement/teacher_transfer.blade.php ENDPATH**/ ?>