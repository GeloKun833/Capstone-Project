
<?php $__env->startSection('content'); ?>

    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Student Submissions</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo e(route('lessons.index')); ?>">Lesson Planner</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo e(route('lessons.show', $lesson)); ?>"><?php echo e($lesson->title); ?></a></li>
                            <li class="breadcrumb-item"><a href="<?php echo e(route('lessons.activities.index', $lesson)); ?>">Activities</a></li>
                            <li class="breadcrumb-item active">Submissions: <?php echo e($activity->title); ?></li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto download-grp">
                        <a href="<?php echo e(route('lessons.activities.index', $lesson)); ?>" class="btn btn-outline-primary me-2">
                            <i class="fas fa-arrow-left"></i> Back to Activities
                        </a>
                        <button type="button" class="btn btn-primary" onclick="exportSubmissions()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
            </div>

            <!-- Activity Summary -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="card-title"><?php echo e($activity->title); ?></h5>
                                    <p class="card-text"><?php echo e($activity->instructions); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="stat-item">
                                                <h4><?php echo e($submissions->count()); ?></h4>
                                                <p>Total Submissions</p>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="stat-item">
                                                <h4><?php echo e($submissions->where('status', 'graded')->count()); ?></h4>
                                                <p>Graded</p>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="stat-item">
                                                <h4><?php echo e($submissions->where('status', 'submitted')->count()); ?></h4>
                                                <p>Pending</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submissions Table -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">All Submissions</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <div class="form-group">
                                            <select class="form-control" id="statusFilter">
                                                <option value="">All Status</option>
                                                <option value="submitted">Submitted</option>
                                                <option value="graded">Graded</option>
                                                <option value="late">Late</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if($submissions->count() > 0): ?>
                                <div class="table-responsive">
                                    <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                        <thead class="student-thread">
                                            <tr>
                                                <th>
                                                    <div class="form-check check-tables">
                                                        <input class="form-check-input" type="checkbox" value="something" id="selectAll">
                                                    </div>
                                                </th>
                                                <th>Student</th>
                                                <th>Submitted</th>
                                                <th>File</th>
                                                <th>Status</th>
                                                <th>Score</th>
                                                <th>Letter Grade</th>
                                                <th>Teacher Comments</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $submissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $submission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $isGraded = $submission->status === 'graded' || $submission->total_score !== null;
                                                    $pct = $submission->percentage;
                                                    if ($pct === null && $submission->max_possible_score > 0 && $submission->total_score !== null) {
                                                        $pct = round(($submission->total_score / $submission->max_possible_score) * 100, 1);
                                                    }
                                                ?>
                                                <tr data-status="<?php echo e($isGraded ? 'graded' : $submission->status); ?>">
                                                    <td>
                                                        <div class="form-check check-tables">
                                                            <input class="form-check-input submission-checkbox" type="checkbox" value="<?php echo e($submission->id); ?>">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="fw-semibold"><?php echo e($submission->student->first_name); ?> <?php echo e($submission->student->last_name); ?></div>
                                                        <small class="text-muted"><?php echo e($submission->student->email); ?></small>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo e(optional($submission->submitted_at ?? $submission->created_at)->format('M d, Y')); ?></strong><br>
                                                            <small class="text-muted"><?php echo e(optional($submission->submitted_at ?? $submission->created_at)->format('g:i A')); ?></small>
                                                        </div>
                                                        <?php if(($submission->submitted_at ?? $submission->created_at) && $activity->due_date && ($submission->submitted_at ?? $submission->created_at)->gt($activity->due_date)): ?>
                                                            <span class="badge bg-danger">Late</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($submission->file_path): ?>
                                                            <a href="<?php echo e(asset('storage/' . $submission->file_path)); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-download"></i> Download
                                                            </a>
                                                            <br>
                                                            <small class="text-muted"><?php echo e(\Illuminate\Support\Str::limit($submission->file_name, 28)); ?></small>
                                                        <?php else: ?>
                                                            <span class="text-muted">No file</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($isGraded): ?>
                                                            <span class="badge" style="background:#198754;color:#fff;">Graded</span>
                                                        <?php elseif($submission->status === 'submitted'): ?>
                                                            <span class="badge" style="background:#ffc107;color:#212529;">Needs Grading</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary"><?php echo e(ucfirst($submission->status)); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($isGraded): ?>
                                                            <div class="fw-bold" style="font-size:1rem;color:#0d6efd;">
                                                                <?php echo e($submission->total_score); ?> / <?php echo e($submission->max_possible_score ?? 100); ?>

                                                            </div>
                                                            <small class="text-muted"><?php echo e(number_format((float) $pct, 1)); ?>%</small>
                                                        <?php else: ?>
                                                            <span class="text-muted">Not graded yet</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($isGraded && $submission->letter_grade): ?>
                                                            <span class="badge" style="background:#198754;color:#fff;font-size:0.95rem;min-width:2rem;">
                                                                <?php echo e($submission->letter_grade); ?>

                                                            </span>
                                                        <?php elseif($isGraded): ?>
                                                            <span class="text-muted">—</span>
                                                        <?php else: ?>
                                                            <span class="text-muted">Not graded yet</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="max-width:220px;">
                                                        <?php if($isGraded && $submission->feedback): ?>
                                                            <div class="small" style="white-space:pre-line;"><?php echo e(\Illuminate\Support\Str::limit($submission->feedback, 80)); ?></div>
                                                        <?php elseif($isGraded): ?>
                                                            <span class="text-muted small">No comments</span>
                                                        <?php else: ?>
                                                            <span class="text-muted small">—</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end" style="min-width: 220px;">
                                                        <div class="submission-actions">
                                                            <?php if(!$isGraded): ?>
                                                                <a href="<?php echo e(route('lessons.activities.grade-submission', [$lesson, $activity, $submission])); ?>"
                                                                   class="submission-action-btn btn-grade">
                                                                    Grade
                                                                </a>
                                                            <?php else: ?>
                                                                <a href="<?php echo e(route('lessons.activities.view-grade', [$lesson, $activity, $submission])); ?>"
                                                                   class="submission-action-btn btn-view">
                                                                    View
                                                                </a>
                                                                <a href="<?php echo e(route('lessons.activities.edit-grade', [$lesson, $activity, $submission])); ?>"
                                                                   class="submission-action-btn btn-edit">
                                                                    Edit
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Bulk Actions -->
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <select class="form-control" id="bulkAction">
                                                <option value="">Bulk Actions</option>
                                                <option value="grade">Grade Selected</option>
                                                <option value="export">Export Selected</option>
                                                <option value="delete">Delete Selected</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <button type="button" class="btn btn-primary" id="applyBulkAction" disabled>
                                            Apply Action
                                        </button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Submissions Yet</h5>
                                    <p class="text-muted">Students haven't submitted any work for this activity.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submission Details Modal -->
    <div class="modal fade" id="submissionDetailsModal" tabindex="-1" aria-labelledby="submissionDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="submissionDetailsModalLabel">Submission Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="submissionDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .stat-item {
        padding: 15px;
        border-radius: 8px;
        background-color: #f8f9fa;
    }
    
    .stat-item h4 {
        margin: 0;
        color: #3d5ee1;
        font-weight: 600;
    }
    
    .stat-item p {
        margin: 5px 0 0 0;
        color: #6c757d;
        font-size: 0.875rem;
    }
    
    .table-avatar h2 a {
        color: #333;
        font-weight: 500;
        text-decoration: none;
    }
    
    .table-avatar small {
        color: #6c757d;
        font-size: 0.875rem;
    }
    
    .badge.bg-success {
        background-color: #7bb13c !important;
    }
    
    .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
    }
    
    .badge.bg-danger {
        background-color: #dc3545 !important;
    }
    
    .badge.bg-secondary {
        background-color: #6c757d !important;
    }
    
    .actions {
        display: flex;
        gap: 5px;
        justify-content: flex-end;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .submission-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 8px;
        min-width: 200px;
    }

    .submission-action-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        min-width: 72px;
        width: auto !important;
        height: auto !important;
        padding: 8px 14px !important;
        border-radius: 6px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        line-height: 1.2 !important;
        text-decoration: none !important;
        white-space: nowrap !important;
        overflow: visible !important;
        box-sizing: border-box;
    }

    .submission-action-btn.btn-grade {
        background: #0d6efd !important;
        border: 1px solid #0d6efd !important;
        color: #fff !important;
    }

    .submission-action-btn.btn-view {
        background: #fff !important;
        border: 1px solid #0d6efd !important;
        color: #0d6efd !important;
    }

    .submission-action-btn.btn-edit {
        background: #fff !important;
        border: 1px solid #fd7e14 !important;
        color: #fd7e14 !important;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // Select all functionality
    $('#selectAll').on('change', function() {
        $('.submission-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkActionButton();
    });
    
    // Individual checkbox change
    $('.submission-checkbox').on('change', function() {
        updateBulkActionButton();
        
        // Update select all checkbox
        let totalCheckboxes = $('.submission-checkbox').length;
        let checkedCheckboxes = $('.submission-checkbox:checked').length;
        
        if (checkedCheckboxes === 0) {
            $('#selectAll').prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $('#selectAll').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#selectAll').prop('indeterminate', true);
        }
    });
    
    // Status filter
    $('#statusFilter').on('change', function() {
        let status = $(this).val();
        if (status) {
            $('tbody tr').hide();
            $('tbody tr[data-status="' + status + '"]').show();
        } else {
            $('tbody tr').show();
        }
    });
    
    // Bulk action
    $('#applyBulkAction').on('click', function() {
        let action = $('#bulkAction').val();
        let selectedIds = $('.submission-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (!action) {
            alert('Please select an action');
            return;
        }
        
        if (selectedIds.length === 0) {
            alert('Please select at least one submission');
            return;
        }
        
        if (action === 'delete') {
            if (!confirm('Are you sure you want to delete the selected submissions?')) {
                return;
            }
        }
        
        // Perform bulk action
        performBulkAction(action, selectedIds);
    });
});

function updateBulkActionButton() {
    let checkedCount = $('.submission-checkbox:checked').length;
    $('#applyBulkAction').prop('disabled', checkedCount === 0);
}

function performBulkAction(action, ids) {
    // Implementation for bulk actions
    console.log('Performing', action, 'on', ids);
    // Add AJAX call here
}

function viewSubmissionDetails(submissionId) {
    $.get(`/lessons/<?php echo e($lesson->id); ?>/activities/<?php echo e($activity->id); ?>/submissions/${submissionId}/details`, function(data) {
        $('#submissionDetailsContent').html(data);
        $('#submissionDetailsModal').modal('show');
    });
}

function exportSubmissions() {
    window.location.href = `<?php echo e(route('lessons.activities.export-submissions', [$lesson, $activity])); ?>`;
}
</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/activities/submissions.blade.php ENDPATH**/ ?>