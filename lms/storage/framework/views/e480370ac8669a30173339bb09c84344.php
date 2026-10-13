<li class="xfer-row">
    <div>
        <div class="xfer-subject"><?php echo e($primary); ?></div>
        <?php if($meta && $meta !== '—'): ?>
            <span class="xfer-meta"><?php echo e($meta); ?></span>
        <?php endif; ?>
    </div>
    <div>
        <label class="visually-hidden" for="replacement-<?php echo e($row['key']); ?>">Transfer <?php echo e($primary); ?></label>
        <select id="replacement-<?php echo e(md5($row['key'])); ?>" class="form-control" name="replacements[<?php echo e($row['key']); ?>]" <?php if($blocked || ! $row['has_eligible']): echo 'disabled'; endif; ?>>
            <option value="">Select a teacher</option>
            <?php $__currentLoopData = $row['choices']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $choice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($choice['id']); ?>" <?php if(! $choice['eligible']): echo 'disabled'; endif; ?> <?php if((string) old('replacements.'.$row['key']) === (string) $choice['id']): echo 'selected'; endif; ?>>
                    <?php echo e($choice['name']); ?><?php echo e($choice['reason'] ? ' — schedule conflict' : ''); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['replacements.'.$row['key']];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <small class="text-danger d-block mt-1"><?php echo e($message); ?></small>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <?php $__errorArgs = [$row['key']];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <small class="text-danger d-block mt-1"><?php echo e($message); ?></small>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
</li>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/usermanagement/partials/transfer-row.blade.php ENDPATH**/ ?>