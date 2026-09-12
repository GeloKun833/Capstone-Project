
<?php $__env->startSection('content'); ?>


<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Enrollment Management</h3>
                    <p class="text-muted mb-0">View all enrolled students from both manual enrollment and enrollment portal</p>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Enrollments</li>
                    </ul>
                </div>
                <div class="col-auto text-right float-right ml-auto">
                    <a href="<?php echo e(route('class-subject.unified-management')); ?>" class="btn btn-success me-2">
                        <i class="fas fa-cogs"></i> Class & Subject Management
                    </a>
                    <a href="<?php echo e(route('enrollments.create')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New User
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">All Enrollments</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-center table-borderless table-striped">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Student</th>
                                        <th>Subject</th>
                                        <th>Section</th>
                                        <th>Academic Year</th>
                                        <th>Semester</th>
                                        <th>Status</th>
                                        <th>Enrollment Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $paginatedEnrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm mr-2">
                                                    <img src="<?php echo e(asset('assets/img/profiles/avatar-01.jpg')); ?>" alt="Avatar" class="rounded-circle">
                                                </div>
                                                <div>
                                                    <h6 class="mb-0"><?php echo e($enrollment['student_name']); ?></h6>
                                                    <small class="text-muted"><?php echo e($enrollment['student_email']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?php echo e($enrollment['subject_name']); ?></span>
                                            <?php if($enrollment['type'] === 'portal_student'): ?>
                                                <br><small class="text-success"><i class="fas fa-globe"></i> Portal</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary"><?php echo e($enrollment['section_name']); ?></span>
                                        </td>
                                        <td><?php echo e($enrollment['academic_year']); ?></td>
                                        <td><?php echo e($enrollment['semester']); ?></td>
                                        <td>
                                            <?php if($enrollment['status'] === 'active'): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php elseif($enrollment['status'] === 'inactive'): ?>
                                                <span class="badge badge-warning">Inactive</span>
                                            <?php elseif($enrollment['status'] === 'completed'): ?>
                                                <span class="badge badge-info">Completed</span>
                                            <?php elseif($enrollment['status'] === 'dropped'): ?>
                                                <span class="badge badge-danger">Dropped</span>
                                            <?php elseif($enrollment['status'] === 'pending'): ?>
                                                <span class="badge badge-warning">Pending</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><?php echo e($enrollment['status']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($enrollment['enrollment_date'] ? \Carbon\Carbon::parse($enrollment['enrollment_date'])->format('M d, Y') : 'N/A'); ?></td>
                                        <td>
                                            <div class="actions">
                                                <?php if($enrollment['type'] === 'enrollment'): ?>
                                                    <a href="<?php echo e(route('enrollments.show', $enrollment['id'])); ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?php echo e(route('enrollments.edit', $enrollment['id'])); ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="<?php echo e(route('enrollments.destroy', $enrollment['id'])); ?>" method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this enrollment?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <a href="<?php echo e(route('enrollment.registrar.show', $enrollment['id'])); ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <span class="btn btn-sm btn-secondary" title="Portal Student - Managed through Enrollment Portal">
                                                        <i class="fas fa-globe"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle fs-1 mb-3"></i>
                                            <h6>No enrollments found</h6>
                                            <p class="small">Create your first enrollment by clicking the "Create New User" button above.</p>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if($paginatedEnrollments->hasPages()): ?>
                        <div class="pagination-wrapper mt-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div class="pagination-info mb-2 mb-md-0">
                                    <p class="text-muted mb-0">
                                        Showing <?php echo e($paginatedEnrollments->firstItem()); ?> to <?php echo e($paginatedEnrollments->lastItem()); ?> of <?php echo e($paginatedEnrollments->total()); ?> results
                                    </p>
                                </div>
                                <div class="pagination-controls">
                                    <?php echo e($paginatedEnrollments->links('pagination::bootstrap-4')); ?>

                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<style>
    .pagination-wrapper {
        padding: 20px 0;
        border-top: 1px solid #e9ecef;
    }
    
    .pagination {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .pagination .page-item {
        list-style: none;
        margin: 0;
    }
    
    .pagination .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        background-color: #fff;
        color: #495057;
        text-decoration: none;
        border-radius: 4px;
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .pagination .page-link:hover {
        background-color: #e9ecef;
        border-color: #adb5bd;
        color: #495057;
        text-decoration: none;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
        z-index: 1;
    }
    
    .pagination .page-item.disabled .page-link {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #6c757d;
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    .pagination .page-item.disabled .page-link:hover {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #6c757d;
    }
    
    /* Next and Previous buttons styling */
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        font-weight: 600;
        min-width: 80px;
    }
    
    /* Ensure next button is clickable and styled */
    .pagination .page-item:not(.disabled) .page-link {
        cursor: pointer;
    }
    
    /* Fix for next button specifically - only when enabled */
    .pagination .page-item:last-child:not(.disabled):not(.active) .page-link {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
    }
    
    .pagination .page-item:last-child:not(.disabled):not(.active) .page-link:hover {
        background-color: #0056b3;
        border-color: #0056b3;
        color: #fff;
    }
    
    /* Fix for previous button - only when enabled */
    .pagination .page-item:first-child:not(.disabled) .page-link {
        background-color: #6c757d;
        border-color: #6c757d;
        color: #fff;
    }
    
    .pagination .page-item:first-child:not(.disabled) .page-link:hover {
        background-color: #5a6268;
        border-color: #5a6268;
        color: #fff;
    }
    
    .pagination-info {
        font-size: 0.9rem;
    }
    
    @media (max-width: 768px) {
        .pagination-wrapper .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
        }
        
        .pagination {
            flex-wrap: wrap;
            width: 100%;
        }
        
        .pagination .page-link {
            min-width: 35px;
            height: 35px;
            padding: 6px 10px;
            font-size: 0.875rem;
        }
        
        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            min-width: 70px;
        }
    }
</style> 
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\enrollments\index.blade.php ENDPATH**/ ?>