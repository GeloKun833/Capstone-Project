<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Teachers</h3>
                    <p class="dir-subtitle">Search, review, and manage teacher profiles.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Teachers</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="dir-card dir-filters">
            <form method="GET" action="<?php echo e(route('teacher/list/page')); ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Teacher ID</label>
                        <input type="text" name="search_id" class="form-control" placeholder="Search by ID" value="<?php echo e(request('search_id')); ?>">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="search_name" class="form-control" placeholder="Search by name" value="<?php echo e(request('search_name')); ?>">
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="search_phone" class="form-control" placeholder="Search by phone" value="<?php echo e(request('search_phone')); ?>">
                    </div>
                    <div class="col-lg-2 col-md-6 pb-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary dir-btn flex-fill">Search</button>
                            <a href="<?php echo e(route('teacher/list/page')); ?>" class="btn btn-outline-secondary dir-btn">Clear</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="dir-card">
            <div class="dir-toolbar">
                <div>
                    <h5 class="dir-toolbar-title">All teachers</h5>
                    <span class="dir-count mt-1"><?php echo e(method_exists($listTeacher, 'total') ? $listTeacher->total() : $listTeacher->count()); ?> records</span>
                </div>
                <div class="dir-actions">
                    <form action="<?php echo e(route('teacher/sync-users')); ?>" method="POST" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-outline-secondary dir-btn btn-sm" title="Sync existing teacher users">
                            <i class="fas fa-sync-alt me-1"></i> Sync
                        </button>
                    </form>
                    <div class="dir-toggle" role="group" aria-label="View">
                        <a href="<?php echo e(route('teacher/list/page')); ?>" class="is-active" title="List view"><i class="fa fa-list"></i></a>
                        <a href="<?php echo e(route('teacher/grid/page')); ?>" title="Grid view"><i class="fa fa-th"></i></a>
                    </div>
                    <a href="<?php echo e(route('teacher/add/page')); ?>" class="btn btn-primary dir-btn btn-sm">
                        <i class="fas fa-plus me-1"></i> Add
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="DataList" class="table dir-table mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Gender</th>
                            <th>Subject</th>
                            <th>Section</th>
                            <th>Mobile</th>
                            <th>Address</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $listTeacher; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $tName = $list->full_name ?: ($list->user_name ?? ($list->user->name ?? 'Unnamed Teacher'));
                                $tPhoto = \App\Support\AvatarUploader::url(optional($list->user)->avatar ?? $list->avatar ?? null);
                                $classes = ($list->subjects && $list->subjects->isNotEmpty()) ? $list->subjects->pluck('class')->unique()->filter()->implode(', ') : null;
                                $subjects = ($list->subjects && $list->subjects->isNotEmpty()) ? $list->subjects->pluck('subject_name')->filter()->implode(', ') : null;
                                $sections = ($list->sections && $list->sections->isNotEmpty()) ? $list->sections->pluck('name')->filter()->implode(', ') : null;
                            ?>
                            <tr>
                                <td hidden class="user_id"><?php echo e($list->user_id); ?></td>
                                <td class="text-muted"><?php echo e($list->user_id); ?></td>
                                <td>
                                    <div class="dir-person">
                                        <a href="<?php echo e(url('teacher/sis/'.$list->user_id)); ?>">
                                            <img src="<?php echo e($tPhoto); ?>" alt="<?php echo e($tName); ?>" onerror="this.onerror=null;this.src='<?php echo e(asset('images/photo_defaults.jpg')); ?>';">
                                        </a>
                                        <span>
                                            <a href="<?php echo e(url('teacher/sis/'.$list->user_id)); ?>" class="dir-person-name"><?php echo e($tName); ?></a>
                                            <span class="dir-person-meta">Teacher</span>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <?php if($classes): ?>
                                        <span class="dir-chip" title="<?php echo e($classes); ?>"><?php echo e(\Illuminate\Support\Str::limit($classes, 28)); ?></span>
                                    <?php else: ?>
                                        <span class="dir-muted">Not assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($list->gender ?: '—'); ?></td>
                                <td>
                                    <?php if($subjects): ?>
                                        <span class="dir-muted" title="<?php echo e($subjects); ?>"><?php echo e(\Illuminate\Support\Str::limit($subjects, 42)); ?></span>
                                    <?php else: ?>
                                        <span class="dir-muted">Not assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($sections): ?>
                                        <span class="dir-chip dir-chip--soft"><?php echo e(\Illuminate\Support\Str::limit($sections, 24)); ?></span>
                                    <?php else: ?>
                                        <span class="dir-muted">Not assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($list->phone_number ?: '—'); ?></td>
                                <td><span class="dir-muted"><?php echo e($list->address ?: '—'); ?></span></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1 justify-content-end">
                                        <a href="<?php echo e(url('teacher/sis/'.$list->user_id)); ?>" class="dir-icon-btn is-success" title="Teacher Information System">
                                            <i class="fas fa-id-card"></i>
                                        </a>
                                        <a href="<?php echo e(url('teacher/edit/'.$list->user_id)); ?>" class="dir-icon-btn" title="Edit teacher">
                                            <i class="far fa-edit"></i>
                                        </a>
                                        <a class="dir-icon-btn is-danger teacher_delete" data-bs-toggle="modal" data-bs-target="#teacherDelete" title="Delete teacher">
                                            <i class="far fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9">
                                    <div class="dir-empty">
                                        <div><i class="fas fa-chalkboard-teacher"></i></div>
                                        <strong>No teachers found</strong>
                                        <div class="small mt-1">Try a different search or add a new teacher.</div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if(method_exists($listTeacher, 'links')): ?>
                <div class="d-flex justify-content-center py-3"><?php echo e($listTeacher->links()); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade dir-modal" id="teacherDelete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Delete teacher?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted mb-0">This will remove the teacher record. This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <form action="<?php echo e(route('teacher/delete')); ?>" method="POST" class="d-flex gap-2 w-100 justify-content-end">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" class="e_user_id" value="">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->startSection('script'); ?>
<script>
    $(document).on('click', '.teacher_delete', function () {
        var _this = $(this).closest('tr');
        $('.e_user_id').val(_this.find('.user_id').text());
    });
</script>
<?php $__env->stopSection(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914b">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/teacher/list-teachers.blade.php ENDPATH**/ ?>