<?php $__env->startSection('title', 'Enrollment Applications'); ?>

<?php $__env->startSection('topbar-actions'); ?>
<a href="<?php echo e(route('enrollment.portal.index')); ?>" target="_blank" class="ep-btn ep-btn-outline ep-btn-sm"><i class="fas fa-external-link"></i> Portal</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $pending = $applications->where('status', 'pending')->count();
    $approved = $applications->where('status', 'approved')->count();
    $rejected = $applications->where('status', 'rejected')->count();
    $review = $applications->where('status', 'under_review')->count();
?>

<div class="mb-4">
    <h1 class="ep-page-title">Enrollment Dashboard</h1>
    <p class="ep-page-subtitle">Manage applications, review documents, and approve enrollments.</p>
</div>

<div class="ep-stat-grid mb-4">
    <div class="ep-stat-card">
        <div class="ep-stat-icon blue"><i class="fas fa-inbox"></i></div>
        <div class="ep-stat-value"><?php echo e($applications->total()); ?></div>
        <div class="ep-stat-label">Total Applications</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon amber"><i class="fas fa-clock"></i></div>
        <div class="ep-stat-value"><?php echo e($pending); ?></div>
        <div class="ep-stat-label">Pending Review</div>
        <?php if($pending > 0): ?><div class="ep-stat-trend down"><i class="fas fa-arrow-up"></i> Needs attention</div><?php endif; ?>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="ep-stat-value"><?php echo e($approved); ?></div>
        <div class="ep-stat-label">Approved</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon red"><i class="fas fa-times-circle"></i></div>
        <div class="ep-stat-value"><?php echo e($rejected); ?></div>
        <div class="ep-stat-label">Rejected</div>
    </div>
</div>

<div class="ep-card mb-4">
    <div class="ep-card-header"><h3><i class="fas fa-filter me-2"></i>Search & Filter</h3></div>
    <div class="ep-card-body">
        <form method="GET" action="<?php echo e(route('enrollment.registrar.index')); ?>" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="ep-label">Search</label>
                <input type="text" class="form-control" name="search" value="<?php echo e(request('search')); ?>" placeholder="Name, email, or application #">
            </div>
            <div class="col-md-3">
                <label class="ep-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All Statuses</option>
                    <?php $__currentLoopData = ['pending','under_review','approved','rejected','needs_documents']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($st); ?>" <?php echo e(request('status') == $st ? 'selected' : ''); ?>><?php echo e(ucfirst(str_replace('_',' ',$st))); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="ep-label">Sort By</label>
                <select class="form-select" name="sort_by">
                    <option value="created_at" <?php echo e(request('sort_by') == 'created_at' ? 'selected' : ''); ?>>Date Submitted</option>
                    <option value="first_name" <?php echo e(request('sort_by') == 'first_name' ? 'selected' : ''); ?>>Name</option>
                    <option value="status" <?php echo e(request('sort_by') == 'status' ? 'selected' : ''); ?>>Status</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="ep-btn ep-btn-primary flex-fill"><i class="fas fa-search"></i></button>
                <a href="<?php echo e(route('enrollment.registrar.index')); ?>" class="ep-btn ep-btn-outline"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="ep-card">
    <div class="ep-card-header">
        <h3>Applications</h3>
        <div class="d-flex gap-2">
            <button class="ep-btn ep-btn-sm ep-btn-outline" onclick="window.print()"><i class="fas fa-print"></i></button>
            <button class="ep-btn ep-btn-sm ep-btn-outline" onclick="exportTable()"><i class="fas fa-download"></i> Export</button>
        </div>
    </div>
    <div class="ep-card-body p-0">
        <?php if($applications->count() > 0): ?>
        <div class="ep-table-wrap">
            <table class="ep-table" id="applicationsTable">
                <thead>
                    <tr>
                        <th>Application #</th>
                        <th>Student</th>
                        <th>Grade</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Reviewer</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $applications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $application): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $chips = ['pending'=>'ep-chip-pending','under_review'=>'ep-chip-review','approved'=>'ep-chip-approved','rejected'=>'ep-chip-rejected','needs_documents'=>'ep-chip-docs'];
                    ?>
                    <tr>
                        <td><strong class="text-primary"><?php echo e($application->application_number); ?></strong></td>
                        <td>
                            <div class="fw-semibold"><?php echo e($application->full_name); ?></div>
                            <small class="text-muted"><?php echo e($application->email); ?></small>
                        </td>
                        <td><span class="ep-chip ep-chip-review"><?php echo e($application->grade_level_applying_for); ?></span></td>
                        <td><span class="ep-chip <?php echo e($chips[$application->status] ?? 'ep-chip-docs'); ?>"><?php echo e(ucfirst(str_replace('_',' ',$application->status))); ?></span></td>
                        <td><?php echo e($application->created_at->format('M d, Y')); ?></td>
                        <td><?php echo e($application->reviewer->name ?? '—'); ?></td>
                        <td>
                            <div class="dropdown">
                                <button class="ep-btn ep-btn-sm ep-btn-ghost dropdown-toggle" data-bs-toggle="dropdown">Actions</button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li><a class="dropdown-item" href="<?php echo e(route('enrollment.registrar.show', $application->id)); ?>"><i class="fas fa-eye me-2 text-primary"></i>View Details</a></li>
                                    <?php if($application->status === 'pending'): ?>
                                    <li><form action="<?php echo e(route('enrollment.registrar.mark-under-review', $application->id)); ?>" method="POST"><?php echo csrf_field(); ?><button class="dropdown-item"><i class="fas fa-search me-2 text-warning"></i>Mark Under Review</button></form></li>
                                    <?php endif; ?>
                                    <?php if($application->status === 'under_review'): ?>
                                    <li><form action="<?php echo e(route('enrollment.registrar.approve', $application->id)); ?>" method="POST"><?php echo csrf_field(); ?><button class="dropdown-item text-success"><i class="fas fa-check me-2"></i>Approve</button></form></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><form action="<?php echo e(route('enrollment.registrar.destroy', $application->id)); ?>" method="POST" onsubmit="return confirm('Delete this application permanently?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Delete</button></form></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-muted">Showing <?php echo e($applications->firstItem() ?? 0); ?>–<?php echo e($applications->lastItem() ?? 0); ?> of <?php echo e($applications->total()); ?></small>
            <?php echo e($applications->links()); ?>

        </div>
        <?php else: ?>
        <div class="ep-empty">
            <i class="fas fa-inbox"></i>
            <h5>No applications found</h5>
            <p>Try adjusting your filters or wait for new submissions.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function exportTable() {
    const table = document.getElementById('applicationsTable');
    if (!table) return;
    let csv = [];
    table.querySelectorAll('tr').forEach(row => {
        const cols = [...row.querySelectorAll('th,td')].slice(0, -1);
        csv.push(cols.map(c => '"' + c.innerText.replace(/"/g,'""').trim() + '"').join(','));
    });
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
    a.download = 'enrollment_applications.csv'; a.click();
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.enrollment-registrar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/enrollment/registrar/index.blade.php ENDPATH**/ ?>