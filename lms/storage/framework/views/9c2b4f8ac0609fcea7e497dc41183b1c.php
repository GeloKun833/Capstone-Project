
<?php $__env->startSection('content'); ?>


<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Assignment Management</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">All Assignments</li>
                    </ul>
                </div>
                <div class="col-auto text-right float-right ml-auto">
                    <a href="<?php echo e(route('assignments.create')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Assignment
                    </a>
                </div>
            </div>
        </div>

        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="<?php echo e(route('assignments.index')); ?>" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo e(request('search')); ?>" placeholder="Search assignments...">
                            </div>
                            <div class="col-md-2">
                                <label for="subject_id" class="form-label">Subject</label>
                                <select class="form-control" id="subject_id" name="subject_id">
                                    <option value="">All Subjects</option>
                                    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($subject->id); ?>" <?php echo e(request('subject_id') == $subject->id ? 'selected' : ''); ?>>
                                            <?php echo e($subject->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="section_id" class="form-label">Section</label>
                                <select class="form-control" id="section_id" name="section_id">
                                    <option value="">All Sections</option>
                                    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($section->id); ?>" <?php echo e(request('section_id') == $section->id ? 'selected' : ''); ?>>
                                            <?php echo e($section->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="draft" <?php echo e(request('status') == 'draft' ? 'selected' : ''); ?>>Draft</option>
                                    <option value="published" <?php echo e(request('status') == 'published' ? 'selected' : ''); ?>>Published</option>
                                    <option value="closed" <?php echo e(request('status') == 'closed' ? 'selected' : ''); ?>>Closed</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="<?php echo e(route('assignments.index')); ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">All Assignments</h5>
                    </div>
                    <div class="card-body">
                        <?php if($assignments->count() > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Title</th>
                                            <th>Subject</th>
                                            <th>Section</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Submissions</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-3">
                                                            <i class="fas fa-tasks text-primary"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0"><?php echo e($assignment->title); ?></h6>
                                                            <small class="text-muted"><?php echo e(Str::limit($assignment->description, 50)); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo e($assignment->subject->name ?? 'N/A'); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo e($assignment->section->name ?? 'N/A'); ?></span>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold"><?php echo e($assignment->due_date ? \Carbon\Carbon::parse($assignment->due_date)->format('M d, Y') : 'N/A'); ?></span>
                                                        <?php if($assignment->due_date): ?>
                                                            <small class="text-muted"><?php echo e(\Carbon\Carbon::parse($assignment->due_date)->diffForHumans()); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php switch($assignment->status):
                                                        case ('draft'): ?>
                                                            <span class="badge bg-warning">Draft</span>
                                                            <?php break; ?>
                                                        <?php case ('published'): ?>
                                                            <span class="badge bg-success">Published</span>
                                                            <?php break; ?>
                                                        <?php case ('closed'): ?>
                                                            <span class="badge bg-danger">Closed</span>
                                                            <?php break; ?>
                                                        <?php default: ?>
                                                            <span class="badge bg-secondary"><?php echo e(ucfirst($assignment->status)); ?></span>
                                                    <?php endswitch; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column align-items-center">
                                                        <span class="fw-bold"><?php echo e($assignment->submissions()->count()); ?></span>
                                                        <small class="text-muted">submissions</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                            Actions
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a class="dropdown-item" href="<?php echo e(route('assignments.show', $assignment->id)); ?>">
                                                                    <i class="fas fa-eye me-2"></i>View
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="<?php echo e(route('assignments.edit', $assignment->id)); ?>">
                                                                    <i class="fas fa-edit me-2"></i>Edit
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="<?php echo e(route('assignments.submissions', $assignment->id)); ?>">
                                                                    <i class="fas fa-check-circle me-2"></i>Grade Submissions
                                                                </a>
                                                            </li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <?php if($assignment->status === 'draft'): ?>
                                                                <li>
                                                                    <form action="<?php echo e(route('assignments.publish', $assignment->id)); ?>" method="POST" class="d-inline">
                                                                        <?php echo csrf_field(); ?>
                                                                        <button type="submit" class="dropdown-item text-success">
                                                                            <i class="fas fa-publish me-2"></i>Publish
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            <?php elseif($assignment->status === 'published'): ?>
                                                                <li>
                                                                    <form action="<?php echo e(route('assignments.close', $assignment->id)); ?>" method="POST" class="d-inline">
                                                                        <?php echo csrf_field(); ?>
                                                                        <button type="submit" class="dropdown-item text-warning">
                                                                            <i class="fas fa-lock me-2"></i>Close
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            <?php endif; ?>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <form action="<?php echo e(route('assignments.destroy', $assignment->id)); ?>" method="POST" class="d-inline">
                                                                    <?php echo csrf_field(); ?>
                                                                    <?php echo method_field('DELETE'); ?>
                                                                    <button type="submit" class="dropdown-item text-danger" 
                                                                            onclick="return confirm('Are you sure you want to delete this assignment?')">
                                                                        <i class="fas fa-trash me-2"></i>Delete
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>

                            
                            <div class="d-flex justify-content-center mt-4">
                                <?php echo e($assignments->links()); ?>

                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-tasks fa-4x text-muted"></i>
                                </div>
                                <h5 class="text-muted">No assignments found</h5>
                                <p class="text-muted mb-4">Create your first assignment to get started</p>
                                <a href="<?php echo e(route('assignments.create')); ?>" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create Assignment
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\assignments\index.blade.php ENDPATH**/ ?>