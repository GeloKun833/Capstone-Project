<?php $__env->startSection('content'); ?>
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">Teachers</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo e(route('student/list')); ?>">Teacher</a></li>
                                <li class="breadcrumb-item active">All Teachers</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table comman-shadow">
                        <div class="card-body pb-0">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Teachers</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="<?php echo e(route('teacher/list/page')); ?>" class="btn btn-outline-gray me-2"><i class="fa fa-list"></i></a>
                                        <a href="<?php echo e(route('teacher/grid/page')); ?>" class="btn btn-outline-gray me-2 active"><i class="fa fa-th"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div class="student-pro-list">
                                <div class="row">
                                    <?php $__currentLoopData = $teacherGrid; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="col-xl-3 col-lg-4 col-md-6 d-flex">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="student-box flex-fill">
                                                    <div class="student-img">
                                                        <a href="<?php echo e(url('teacher/profile/'.$list->id)); ?>">
                                                            <img class="img-fluid" alt="Teachers Info" src="<?php echo e(URL::to('images/photo_defaults.jpg')); ?>" width="20%" height="20%">
                                                        </a>
                                                    </div>
                                                    <div class="student-content pb-0">
                                                        <h5><a href="<?php echo e(url('teacher/profile/'.$list->id)); ?>"><?php echo e($list->full_name); ?></a></h5>
                                                        <h6>Teacher</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <?php if(method_exists($teacherGrid, 'links')): ?>
                                    <div class="d-flex justify-content-center mt-3"><?php echo e($teacherGrid->links()); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\teacher\teachers-grid.blade.php ENDPATH**/ ?>