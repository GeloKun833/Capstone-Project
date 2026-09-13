<?php $__env->startSection('content'); ?>
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">Students</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo e(route('student/list')); ?>">Student</a></li>
                                <li class="breadcrumb-item active">All Students</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="student-group-form">
                <form method="GET" action="<?php echo e(route('student/list')); ?>">
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
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group">
                                <input type="text" name="search_class" class="form-control" placeholder="Search by Class ..." value="<?php echo e(request('search_class')); ?>">
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group">
                                <input type="text" name="search_year_level" class="form-control" placeholder="Search by Year Level ..." value="<?php echo e(request('search_year_level')); ?>">
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
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="row align-items-center mb-3">
                <div class="col">
                    <?php if($showingArchived): ?>
                        <h4>Archived Students</h4>
                    <?php else: ?>
                        <h4>Active Students</h4>
                    <?php endif; ?>
                </div>
                <div class="col-auto">
                    <?php if($showingArchived): ?>
                        <a href="<?php echo e(route('student/list')); ?>" class="btn btn-secondary">Show Active</a>
                    <?php else: ?>
                        <a href="<?php echo e(route('student/list')); ?>?archived=1" class="btn btn-warning">Show Archived</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table comman-shadow">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Students</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="<?php echo e(route('student/list')); ?>" class="btn btn-outline-gray me-2 active">
                                            <i class="fa fa-list" aria-hidden="true"></i>
                                        </a>
                                        <a href="<?php echo e(route('student/grid')); ?>" class="btn btn-outline-gray me-2">
                                            <i class="fa fa-th" aria-hidden="true"></i>
                                        </a>
                                        <a href="#" class="btn btn-outline-primary me-2"><i class="fas fa-download"></i> Download</a>
                                        <a href="<?php echo e(route('student/add/page')); ?>" class="btn btn-primary"><i class="fas fa-plus"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table
                                    class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
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
                                            <th>DOB</th>
                                            <th>Parent Name</th>
                                            <th>Mobile Number</th>
                                            <th>Address</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $studentList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <div class="form-check check-tables">
                                                    <input class="form-check-input" type="checkbox" value="something">
                                                </div>
                                            </td>
                                            <td>STD<?php echo e(++$key); ?></td>
                                            <td hidden class="id"><?php echo e($list->id); ?></td>
                                            <td hidden class="avatar"><?php echo e($list->upload); ?></td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="student-details.html"class="avatar avatar-sm me-2">
                                                        <img class="avatar-img rounded-circle" src="<?php echo e(Storage::url('student-photos/'.$list->upload)); ?>" alt="">
                                                    </a>
                                                    <a href="student-details.html"><?php echo e($list->first_name); ?> <?php echo e($list->last_name); ?></a>
                                                </h2>
                                            </td>
                                            <td><?php echo e($list->class); ?> <?php echo e($list->section); ?></td>
                                            <td><?php echo e($list->date_of_birth); ?></td>
                                            <td>Soeng Soeng</td>
                                            <td><?php echo e($list->phone_number); ?></td>
                                            <td>110 Sen Sok Steet,PP</td>
                                            <td class="text-end">
                                                <div class="actions d-flex flex-wrap gap-2 justify-content-end align-items-center">
                                                    <?php if(!empty($list->user_id)): ?>
                                                        <a href="<?php echo e(route('student.sis', $list->user_id)); ?>" class="btn btn-sm btn-success" title="View SIS">
                                                            <i class="fas fa-database me-1"></i>SIS
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="<?php echo e(url('student/edit/'.$list->id)); ?>" class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="far fa-edit"></i>
                                                    </a>
                                                    <?php if($showingArchived): ?>
                                                        <form action="<?php echo e(url('student/restore/'.$list->id)); ?>" method="POST" class="d-inline">
                                                            <?php echo csrf_field(); ?>
                                                            <button type="submit" class="btn btn-sm btn-outline-success">Restore</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <a class="btn btn-sm btn-danger student_delete" data-bs-toggle="modal" data-bs-target="#studentUser" title="Delete">
                                                            <i class="far fa-trash-alt"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                                <?php if(method_exists($studentList, 'links')): ?>
                                    <div class="d-flex justify-content-center mt-3"><?php echo e($studentList->links()); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal custom-modal fade" id="studentUser" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>Delete Student</h3>
                        <p>Are you sure want to delete?</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="<?php echo e(route('student/delete')); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <div class="row">
                                <input type="hidden" name="id" class="e_id" value="">
                                <input type="hidden" name="avatar" class="e_avatar" value="">
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
        $(document).on('click','.student_delete',function()
        {
            var _this = $(this).parents('tr');
            $('.e_id').val(_this.find('.id').text());
            $('.e_avatar').val(_this.find('.avatar').text());
        });
    </script>
    <?php $__env->stopSection(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/student/student.blade.php ENDPATH**/ ?>