<?php $__env->startSection('content'); ?>
    <div class="page-wrapper">
        <div class="content container-fluid dir-page">
            <div class="page-header">
                <div class="row align-items-start">
                    <div class="col">
                        <h3 class="page-title mb-1">Students</h3>
                        <p class="dir-subtitle">Search active or archived students and open their records.</p>
                    </div>
                    <div class="col-auto text-end">
                        <ul class="breadcrumb justify-content-end mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">All Students</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="dir-card dir-filters">
                <form method="GET" action="<?php echo e(route('student/list')); ?>">
                    <?php if($showingArchived): ?>
                        <input type="hidden" name="archived" value="1">
                    <?php endif; ?>
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">Student ID</label>
                            <input type="text" name="search_id" class="form-control" placeholder="Search by ID" value="<?php echo e(request('search_id')); ?>">
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="search_name" class="form-control" placeholder="Search by name" value="<?php echo e(request('search_name')); ?>">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">Class</label>
                            <input type="text" name="search_class" class="form-control" placeholder="Search by class" value="<?php echo e(request('search_class')); ?>">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">Year level</label>
                            <input type="text" name="search_year_level" class="form-control" placeholder="Year level" value="<?php echo e(request('search_year_level')); ?>">
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="search_phone" class="form-control" placeholder="Search by phone" value="<?php echo e(request('search_phone')); ?>">
                        </div>
                        <div class="col-12 pb-3">
                            <button type="submit" class="btn btn-primary dir-btn">Search</button>
                            <a href="<?php echo e($showingArchived ? route('student/list').'?archived=1' : route('student/list')); ?>" class="btn btn-outline-secondary dir-btn">Clear</a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="dir-card">
                <div class="dir-toolbar">
                    <div>
                        <h5 class="dir-toolbar-title"><?php echo e($showingArchived ? 'Archived students' : 'Active students'); ?></h5>
                        <span class="dir-count mt-1"><?php echo e(method_exists($studentList, 'total') ? $studentList->total() : $studentList->count()); ?> records</span>
                    </div>
                    <div class="dir-actions">
                        <?php if($showingArchived): ?>
                            <a href="<?php echo e(route('student/list')); ?>" class="btn btn-outline-secondary dir-btn btn-sm">Show active</a>
                        <?php else: ?>
                            <a href="<?php echo e(route('student/list')); ?>?archived=1" class="btn btn-outline-secondary dir-btn btn-sm">Show archived</a>
                        <?php endif; ?>
                        <div class="dir-toggle" role="group" aria-label="View">
                            <a href="<?php echo e(route('student/list')); ?><?php echo e($showingArchived ? '?archived=1' : ''); ?>" class="is-active" title="List view"><i class="fa fa-list"></i></a>
                            <a href="<?php echo e(route('student/grid')); ?><?php echo e($showingArchived ? '?archived=1' : ''); ?>" title="Grid view"><i class="fa fa-th"></i></a>
                        </div>
                        <a href="<?php echo e(route('student/add/page')); ?>" class="btn btn-primary dir-btn btn-sm">
                            <i class="fas fa-plus me-1"></i> Add
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table dir-table mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>DOB</th>
                                <th>Parent</th>
                                <th>Mobile</th>
                                <th>Address</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $studentList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $sName = trim(($list->first_name ?? '').' '.($list->last_name ?? ''));
                                    $sPhoto = $list->upload
                                        ? \Illuminate\Support\Facades\Storage::url('student-photos/'.$list->upload)
                                        : asset('images/photo_defaults.jpg');
                                    $sId = $list->admission_id ?: ('STD'.$list->id);
                                    $klass = trim(($list->year_level ?: $list->class).' '.($list->section ?? ''));
                                    try {
                                        $dob = $list->date_of_birth ? \Carbon\Carbon::parse($list->date_of_birth)->format('M j, Y') : '—';
                                    } catch (\Exception $e) {
                                        $dob = $list->date_of_birth ?: '—';
                                    }
                                ?>
                                <tr>
                                    <td hidden class="id"><?php echo e($list->id); ?></td>
                                    <td hidden class="avatar"><?php echo e($list->upload); ?></td>
                                    <td class="text-muted"><?php echo e($sId); ?></td>
                                    <td>
                                        <div class="dir-person">
                                            <img src="<?php echo e($sPhoto); ?>" alt="<?php echo e($sName); ?>" onerror="this.onerror=null;this.src='<?php echo e(asset('images/photo_defaults.jpg')); ?>';">
                                            <span>
                                                <?php if(!empty($list->user_id)): ?>
                                                    <a href="<?php echo e(route('student.sis', $list->user_id)); ?>" class="dir-person-name"><?php echo e($sName ?: 'Unnamed student'); ?></a>
                                                <?php else: ?>
                                                    <span class="dir-person-name"><?php echo e($sName ?: 'Unnamed student'); ?></span>
                                                <?php endif; ?>
                                                <span class="dir-person-meta">Student</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($klass): ?>
                                            <span class="dir-chip"><?php echo e($klass); ?></span>
                                        <?php else: ?>
                                            <span class="dir-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($dob); ?></td>
                                    <td><?php echo e($list->parent_name ?: '—'); ?></td>
                                    <td><?php echo e($list->phone_number ?: '—'); ?></td>
                                    <td><span class="dir-muted"><?php echo e($list->address ?: '—'); ?></span></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1 justify-content-end">
                                            <?php if(!empty($list->user_id)): ?>
                                                <a href="<?php echo e(route('student.sis', $list->user_id)); ?>" class="dir-icon-btn is-success" title="View SIS">
                                                    <i class="fas fa-id-card"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?php echo e(url('student/edit/'.$list->id)); ?>" class="dir-icon-btn" title="Edit student">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <?php if($showingArchived): ?>
                                                <form action="<?php echo e(url('student/restore/'.$list->id)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="dir-icon-btn is-success" title="Restore">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <a class="dir-icon-btn is-danger student_delete" data-bs-toggle="modal" data-bs-target="#studentUser" title="Delete student">
                                                    <i class="far fa-trash-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8">
                                        <div class="dir-empty">
                                            <div><i class="fas fa-user-graduate"></i></div>
                                            <strong>No students found</strong>
                                            <div class="small mt-1">Try a different search or add a new student.</div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if(method_exists($studentList, 'links')): ?>
                    <div class="d-flex justify-content-center py-3"><?php echo e($studentList->links()); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade dir-modal" id="studentUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">Delete student?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="text-muted mb-0">This will archive or remove the student record. You can restore archived students later.</p>
                </div>
                <div class="modal-footer border-0">
                    <form action="<?php echo e(route('student/delete')); ?>" method="POST" class="d-flex gap-2 w-100 justify-content-end">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" class="e_id" value="">
                        <input type="hidden" name="avatar" class="e_avatar" value="">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startSection('script'); ?>
    <script>
        $(document).on('click', '.student_delete', function () {
            var _this = $(this).closest('tr');
            $('.e_id').val(_this.find('.id').text());
            $('.e_avatar').val(_this.find('.avatar').text());
        });
    </script>
    <?php $__env->stopSection(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914b">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/student/student.blade.php ENDPATH**/ ?>