<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header"><h3 class="page-title">Class Performance Analysis</h3></div>
        <form method="GET" class="row mb-4">
            <div class="col-md-3">
                <select name="subject_id" class="form-control">
                    <option value="">Select Subject</option>
                    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s->id); ?>" <?php echo e($subjectId == $s->id ? 'selected' : ''); ?>><?php echo e($s->subject_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" name="section_id" class="form-control" placeholder="Section ID" value="<?php echo e($sectionId); ?>">
            </div>
            <div class="col-md-2"><button class="btn btn-primary">Analyze</button></div>
        </form>
        <?php if($classAnalysis): ?>
            <div class="row mb-4">
                <div class="col-md-3"><div class="card"><div class="card-body text-center"><h4><?php echo e($classAnalysis['average_score']); ?>%</h4><p>Average</p></div></div></div>
                <div class="col-md-3"><div class="card"><div class="card-body text-center"><h4><?php echo e($classAnalysis['total_students']); ?></h4><p>Students</p></div></div></div>
                <div class="col-md-3"><div class="card"><div class="card-body text-center"><h4><?php echo e($classAnalysis['highest_score']); ?>%</h4><p>Highest</p></div></div></div>
                <div class="col-md-3"><div class="card"><div class="card-body text-center"><h4><?php echo e($classAnalysis['lowest_score']); ?>%</h4><p>Lowest</p></div></div></div>
            </div>
        <?php endif; ?>
        <?php if($weakStudents && $weakStudents->count()): ?>
            <div class="card mb-4">
                <div class="card-header"><h5>Students Needing Support</h5></div>
                <div class="card-body">
                    <table class="table"><thead><tr><th>Student</th><th>Average</th></tr></thead>
                    <tbody><?php $__currentLoopData = $weakStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ws): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><tr><td><?php echo e($ws['student']->first_name); ?> <?php echo e($ws['student']->last_name); ?></td><td><?php echo e($ws['average_score']); ?>%</td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></tbody></table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\lessons\recommendations\class-analysis.blade.php ENDPATH**/ ?>