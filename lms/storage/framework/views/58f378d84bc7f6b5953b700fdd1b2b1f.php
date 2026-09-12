<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Teachers</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                        <li class="breadcrumb-item active">Teachers</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="student-group-form">
            <form method="GET" action="<?php echo e(route('teacher/list/page')); ?>">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <input type="text" name="search_id" class="form-control" placeholder="Search by ID ..." value="<?php echo e(request('search_id')); ?>">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <input type="text" name="search_name" class="form-control" placeholder="Search by Name ..." value="<?php echo e(request('search_name')); ?>">
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <input type="text" name="search_phone" class="form-control" placeholder="Search by Phone ..." value="<?php echo e(request('search_phone')); ?>">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="search-student-btn">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <a href="<?php echo e(route('teacher/list/page')); ?>" class="btn btn-secondary">Clear</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <div class="page-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="page-title">Teachers</h3>
                                </div>
                                <div class="col-auto text-end float-end ms-auto download-grp">
                                    <form action="<?php echo e(route('teacher/sync-users')); ?>" method="POST" style="display: inline;">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-warning me-2" title="Sync existing teacher users">
                                            <i class="fas fa-sync"></i> Sync
                                        </button>
                                    </form>
                                    <a href="teachers.html" class="btn btn-outline-gray me-2 active">
                                        <i class="fa fa-list" aria-hidden="true"></i>
                                    <a href="<?php echo e(route('teacher/grid/page')); ?>" class="btn btn-outline-gray me-2">
                                        <i class="fa fa-th" aria-hidden="true"></i>
                                    <a href="#" class="btn btn-outline-primary me-2"><i
                                            class="fas fa-download"></i> Download</a>
                                    <a href="<?php echo e(route('teacher/add/page')); ?>" class="btn btn-primary"><i class="fas fa-plus"></i></a>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="DataList" class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                <thead class="student-thread"> 
                                    <tr>
                                        <th>
                                            <div class="form-check check-tables">
                                                <input class="form-check-input" type="checkbox" value="something">
                                            </div>
                                        </th>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Class</th>
                                        <th>Gender</th>
                                        <th>Subject</th>
                                        <th>Section</th>
                                        <th>Mobile Number</th>
                                        <th>Address</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $listTeacher; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <div class="form-check check-tables">
                                                <input class="form-check-input" type="checkbox"
                                                    value="something">
                                            </div>
                                        </td>
                                        <td hidden class="user_id
                                        "><?php echo e($list->user_id); ?></td>
                                        <td><?php echo e($list->user_id); ?></td>
                                        <td>
                                            <h2 class="table-avatar">
                                                <a href="<?php echo e(url('teacher/sis/'.$list->user_id)); ?>" class="avatar avatar-sm me-2">
                                                    <?php if(!empty($list->avatar)): ?>
                                                        <img class="avatar-img rounded-circle" src="<?php echo e(URL::to('images/'.$list->avatar)); ?>" alt="<?php echo e($list->full_name ?: ($list->user->name ?? 'Teacher')); ?>">
                                                    <?php else: ?>
                                                        <img class="avatar-img rounded-circle" src="<?php echo e(URL::to('images/photo_defaults.jpg')); ?>" alt="<?php echo e($list->full_name ?: ($list->user->name ?? 'Teacher')); ?>">
                                                    <?php endif; ?>
                                                </a>
                                                <a href="<?php echo e(url('teacher/sis/'.$list->user_id)); ?>">
                                                    <strong><?php echo e($list->full_name ?: ($list->user_name ?? ($list->user->name ?? 'Unnamed Teacher'))); ?></strong>
                                                </a>
                                            </h2>
                                        </td>
                                        <td>
                                            <?php if($list->subjects && $list->subjects->isNotEmpty()): ?>
                                                <?php echo e($list->subjects->pluck('class')->unique()->implode(', ')); ?>

                                            <?php else: ?>
                                                <span class="text-muted">Not assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($list->gender ?: 'Not specified'); ?></td>
                                        <td>
                                            <?php if($list->subjects && $list->subjects->isNotEmpty()): ?>
                                                <?php echo e($list->subjects->pluck('subject_name')->implode(', ')); ?>

                                            <?php else: ?>
                                                <span class="text-muted">Not assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($list->sections && $list->sections->isNotEmpty()): ?>
                                                <?php echo e($list->sections->pluck('name')->implode(', ')); ?>

                                            <?php else: ?>
                                                <span class="text-muted">Not assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($list->phone_number ?: 'Not specified'); ?></td>
                                        <td><?php echo e($list->address ?: 'Not specified'); ?></td>
                                        <td class="text-end">
                                            <div class="actions">
                                                <a href="<?php echo e(url('teacher/sis/'.$list->user_id)); ?>" class="btn btn-sm bg-success-light" title="View Teacher Information System">
                                                    <i class="fas fa-info-circle me-1"></i>TIS
                                                </a>
                                                <a href="<?php echo e(url('teacher/edit/'.$list->user_id)); ?>" class="btn btn-sm bg-danger-light" title="Edit Teacher">
                                                    <i class="far fa-edit me-1"></i>
                                                </a>
                                                <a class="btn btn-sm bg-danger-light teacher_delete" data-bs-toggle="modal" data-bs-target="#teacherDelete" title="Delete Teacher">
                                                    <i class="far fa-trash-alt me-1"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                            <?php if(method_exists($listTeacher, 'links')): ?>
                                <div class="d-flex justify-content-center mt-3"><?php echo e($listTeacher->links()); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal custom-modal fade" id="teacherDelete" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="form-header">
                    <h3>Delete Teacher</h3>
                    <p>Are you sure want to delete?</p>
                </div>
                <div class="modal-btn delete-action">
                    <form action="<?php echo e(route('teacher/delete')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="row">
                            <input type="hidden" name="id" class="e_user_id" value="">
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary continue-btn submit-btn" style="border-radius: 5px !important;">Delete</button>
                            </div>
                            <div class="col-6">
                                <a href="#" data-bs-dismiss="modal"class="btn btn-primary paid-cancel-btn">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startSection('script'); ?>
    
    <script>
        $(document).on('click','.teacher_delete',function()
        {
            var _this = $(this).parents('tr');
            $('.e_user_id').val(_this.find('.user_id').text());
        });
    </script>
<?php $__env->stopSection(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/teacher/list-teachers.blade.php ENDPATH**/ ?>