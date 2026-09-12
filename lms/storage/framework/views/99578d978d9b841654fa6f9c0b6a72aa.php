
<?php $__env->startSection('content'); ?>

    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Activity Rubric Management</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo e(route('lessons.index')); ?>">Lesson Planner</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo e(route('lessons.show', $lesson)); ?>"><?php echo e($lesson->title); ?></a></li>
                            <li class="breadcrumb-item"><a href="<?php echo e(route('lessons.activities.index', $lesson)); ?>">Activities</a></li>
                            <li class="breadcrumb-item active">Rubric: <?php echo e($activity->title); ?></li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto download-grp">
                        <a href="<?php echo e(route('lessons.activities.index', $lesson)); ?>" class="btn btn-outline-primary me-2">
                            <i class="fas fa-arrow-left"></i> Back to Activities
                        </a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRubricModal">
                            <i class="fas fa-plus"></i> Add Rubric Category
                        </button>
                    </div>
                </div>
            </div>

            <!-- Activity Summary Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="card-title"><?php echo e($activity->title); ?></h5>
                                    <p class="card-text"><?php echo e($activity->instructions); ?></p>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i> Due: <?php echo e($activity->due_date->format('M d, Y')); ?>

                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">
                                                <i class="fas fa-upload"></i> Submissions: <?php echo e($activity->submissions->count()); ?>

                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">
                                                <i class="fas fa-check-circle"></i> Graded: <?php echo e($activity->submissions->where('status', 'graded')->count()); ?>

                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="alert alert-info mb-0">
                                        <strong>Total Weight: <span id="totalWeight">0</span>%</strong><br>
                                        <small>Rubric categories should total 100%</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rubric Categories -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Rubric Categories</h3>
                                    </div>
                                </div>
                            </div>

                            <?php if($rubrics->count() > 0): ?>
                                <div class="table-responsive">
                                    <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                        <thead class="student-thread">
                                            <tr>
                                                <th>Category</th>
                                                <th>Description</th>
                                                <th>Max Score</th>
                                                <th>Weight (%)</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $rubrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rubric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a><?php echo e($rubric->category_name); ?></a>
                                                        </h2>
                                                    </td>
                                                    <td><?php echo e(Str::limit($rubric->description, 50)); ?></td>
                                                    <td><strong><?php echo e($rubric->max_score); ?></strong></td>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo e($rubric->weight); ?>%</span>
                                                    </td>
                                                    <td>
                                                        <?php if($rubric->is_active): ?>
                                                            <span class="badge bg-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="actions">
                                                            <button type="button" class="btn btn-sm btn-outline-primary me-2" 
                                                                    onclick="editRubric(<?php echo e($rubric->id); ?>)">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <form action="<?php echo e(route('lessons.activities.destroy-rubric', [$lesson, $activity, $rubric])); ?>" 
                                                                  method="POST" class="d-inline">
                                                                <?php echo csrf_field(); ?>
                                                                <?php echo method_field('DELETE'); ?>
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                        onclick="return confirm('Are you sure you want to delete this rubric category?')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Rubric Categories</h5>
                                    <p class="text-muted">Add rubric categories to start grading this activity.</p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRubricModal">
                                        <i class="fas fa-plus"></i> Add First Category
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submissions Overview -->
            <?php if($activity->allows_submission && $activity->submissions->count() > 0): ?>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card card-table">
                            <div class="card-body">
                                <div class="page-header">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h3 class="page-title">Student Submissions</h3>
                                        </div>
                                        <div class="col-auto text-end float-end ms-auto download-grp">
                                            <a href="<?php echo e(route('lessons.activities.submissions', [$lesson, $activity])); ?>" class="btn btn-primary">
                                                <i class="fas fa-eye"></i> View All Submissions
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                        <thead class="student-thread">
                                            <tr>
                                                <th>Student</th>
                                                <th>Submitted</th>
                                                <th>File</th>
                                                <th>Status</th>
                                                <th>Score</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $activity->submissions->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $submission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a><?php echo e($submission->student->first_name); ?> <?php echo e($submission->student->last_name); ?></a>
                                                        </h2>
                                                    </td>
                                                    <td><?php echo e($submission->created_at->format('M d, Y H:i')); ?></td>
                                                    <td>
                                                        <?php if($submission->file_path): ?>
                                                            <a href="<?php echo e(asset('storage/' . $submission->file_path)); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-download"></i> Download
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">No file</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($submission->status === 'submitted'): ?>
                                                            <span class="badge bg-warning">Submitted</span>
                                                        <?php elseif($submission->status === 'graded'): ?>
                                                            <span class="badge bg-success">Graded</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Pending</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($submission->status === 'graded'): ?>
                                                            <strong><?php echo e($submission->total_score); ?>/<?php echo e($submission->max_possible_score); ?></strong>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="actions">
                                                            <?php if($submission->status === 'submitted'): ?>
                                                                <a href="<?php echo e(route('lessons.activities.grade-submission', [$lesson, $activity, $submission])); ?>" 
                                                                   class="btn btn-sm btn-primary">
                                                                    <i class="fas fa-star"></i> Grade
                                                                </a>
                                                            <?php elseif($submission->status === 'graded'): ?>
                                                                <a href="<?php echo e(route('lessons.activities.view-grade', [$lesson, $activity, $submission])); ?>" 
                                                                   class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Rubric Modal -->
    <div class="modal fade" id="addRubricModal" tabindex="-1" aria-labelledby="addRubricModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="<?php echo e(route('lessons.activities.store-rubric', [$lesson, $activity])); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRubricModalLabel">Add Rubric Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_name">Category Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="category_name" name="category_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_score">Max Score <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="max_score" name="max_score" min="1" max="100" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="form-group" id="weightGroup">
                            <label for="weight">Weight (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="weight" name="weight" min="1" max="100"
                                   value="<?php echo e(($activity->rubrics->count() ?? 0) === 0 ? 100 : max(1, 100 - ($activity->rubrics->sum('weight') ?? 0))); ?>"
                                   <?php if(($activity->rubrics->count() ?? 0) === 0): ?> readonly <?php endif; ?>
                                   required>
                            <?php if(($activity->rubrics->count() ?? 0) === 0): ?>
                                <small class="text-muted">Single category is automatically set to <strong>100%</strong>.</small>
                            <?php else: ?>
                                <small class="text-muted">Remaining to 100%: <?php echo e(max(0, 100 - ($activity->rubrics->sum('weight') ?? 0))); ?>%.</small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Rubric Modal -->
    <div class="modal fade" id="editRubricModal" tabindex="-1" aria-labelledby="editRubricModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editRubricForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="editRubricModalLabel">Edit Rubric Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_category_name">Category Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_max_score">Max Score <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_max_score" name="max_score" min="1" max="100" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_description">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="edit_weight">Weight (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_weight" name="weight" min="1" max="100" required>
                            <small class="text-muted" id="editWeightHelp">Adjust so all categories total 100%.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .table-avatar h2 a {
        color: #333;
        font-weight: 500;
        text-decoration: none;
    }
    
    .badge {
        font-size: 0.75rem;
    }
    
    .actions {
        display: flex;
        gap: 5px;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // Calculate total weight
    function updateTotalWeight() {
        let total = 0;
        $('.badge.bg-primary').each(function() {
            total += parseInt($(this).text().replace('%', ''));
        });
        $('#totalWeight').text(total);
        
        if (total > 100) {
            $('#totalWeight').parent().removeClass('alert-info').addClass('alert-warning');
        } else if (total === 100) {
            $('#totalWeight').parent().removeClass('alert-info alert-warning').addClass('alert-success');
        } else {
            $('#totalWeight').parent().removeClass('alert-success alert-warning').addClass('alert-info');
        }
    }
    
    updateTotalWeight();
    
    // Weight validation
    $('#weight, #edit_weight').on('input', function() {
        let value = parseInt($(this).val());
        if (value > 100) {
            $(this).addClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
            $(this).after('<div class="invalid-feedback">Weight cannot exceed 100%</div>');
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });
});

function editRubric(rubricId) {
    const rubricCount = <?php echo e($activity->rubrics->count() ?? 0); ?>;
    // Fetch rubric data and populate modal
    $.get(`/lessons/<?php echo e($lesson->id); ?>/activities/<?php echo e($activity->id); ?>/rubric/${rubricId}/edit`, function(data) {
        $('#edit_category_name').val(data.category_name);
        $('#edit_max_score').val(data.max_score);
        $('#edit_description').val(data.description);

        if (rubricCount <= 1) {
            $('#edit_weight').val(100).prop('readonly', true);
            $('#editWeightHelp').html('Single category is automatically set to <strong>100%</strong>.');
        } else {
            $('#edit_weight').val(data.weight).prop('readonly', false);
            $('#editWeightHelp').text('Adjust so all categories total 100%.');
        }

        $('#editRubricForm').attr('action', `/lessons/<?php echo e($lesson->id); ?>/activities/<?php echo e($activity->id); ?>/rubric/${rubricId}`);
        $('#editRubricModal').modal('show');
    });
}
</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\activities\rubric.blade.php ENDPATH**/ ?>