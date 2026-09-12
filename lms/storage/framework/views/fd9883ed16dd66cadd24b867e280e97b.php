
<?php $__env->startSection('content'); ?>


<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Grade Submissions</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('assignments.index')); ?>">Assignments</a></li>
                        <li class="breadcrumb-item active">Grade Submissions</li>
                    </ul>
                </div>
                <div class="col-auto text-right float-right ml-auto">
                    <a href="<?php echo e(route('assignments.show', $assignment->id)); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Assignment
                    </a>
                </div>
            </div>
        </div>

        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Assignment: <?php echo e($assignment->title); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted">Subject</h6>
                                    <p class="fw-bold"><?php echo e($assignment->subject->name ?? 'N/A'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted">Section</h6>
                                    <p class="fw-bold"><?php echo e($assignment->section->name ?? 'N/A'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted">Due Date</h6>
                                    <p class="fw-bold"><?php echo e($assignment->due_date ? \Carbon\Carbon::parse($assignment->due_date)->format('M d, Y') : 'N/A'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted">Max Score</h6>
                                    <p class="fw-bold"><?php echo e($assignment->max_score); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="card-title">Student Submissions (<?php echo e($submissions->count()); ?>)</h5>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary" onclick="filterSubmissions('all')">All</button>
                                    <button type="button" class="btn btn-outline-warning" onclick="filterSubmissions('pending')">Pending</button>
                                    <button type="button" class="btn btn-outline-success" onclick="filterSubmissions('graded')">Graded</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if($submissions->count() > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped" id="submissionsTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Student</th>
                                            <th>Submission Date</th>
                                            <th>Status</th>
                                            <th>Score</th>
                                            <th>Feedback</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $submissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $submission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr class="submission-row" data-status="<?php echo e($submission->status); ?>">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-3">
                                                            <i class="fas fa-user text-primary"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0"><?php echo e($submission->student->full_name ?? 'Student'); ?></h6>
                                                            <small class="text-muted"><?php echo e($submission->student->email ?? 'N/A'); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold"><?php echo e($submission->submitted_at ? \Carbon\Carbon::parse($submission->submitted_at)->format('M d, Y H:i') : 'N/A'); ?></span>
                                                        <?php if($submission->submitted_at): ?>
                                                            <small class="text-muted">
                                                                <?php if($submission->isLate()): ?>
                                                                    <span class="text-danger">Late</span>
                                                                <?php else: ?>
                                                                    <span class="text-success">On Time</span>
                                                                <?php endif; ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php switch($submission->status):
                                                        case ('submitted'): ?>
                                                            <span class="badge bg-warning">Pending Review</span>
                                                            <?php break; ?>
                                                        <?php case ('graded'): ?>
                                                            <span class="badge bg-success">Graded</span>
                                                            <?php break; ?>
                                                        <?php case ('late'): ?>
                                                            <span class="badge bg-danger">Late</span>
                                                            <?php break; ?>
                                                        <?php default: ?>
                                                            <span class="badge bg-secondary"><?php echo e(ucfirst($submission->status)); ?></span>
                                                    <?php endswitch; ?>
                                                </td>
                                                <td>
                                                    <?php if($submission->status === 'graded'): ?>
                                                        <span class="fw-bold text-success"><?php echo e($submission->score); ?>/<?php echo e($assignment->max_score); ?></span>
                                                        <?php if($submission->score_percentage): ?>
                                                            <br><small class="text-muted"><?php echo e(number_format($submission->score_percentage, 1)); ?>%</small>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not graded</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($submission->feedback): ?>
                                                        <span class="text-truncate d-inline-block" style="max-width: 150px;" title="<?php echo e($submission->feedback); ?>">
                                                            <?php echo e(Str::limit($submission->feedback, 30)); ?>

                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">No feedback</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                onclick="viewSubmission(<?php echo e($submission->id); ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if($submission->status !== 'graded'): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                                    onclick="gradeSubmission(<?php echo e($submission->id); ?>)">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                    onclick="editGrade(<?php echo e($submission->id); ?>)">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>

                            
                            <div class="d-flex justify-content-center mt-4">
                                <?php echo e($submissions->links()); ?>

                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-inbox fa-4x text-muted"></i>
                                </div>
                                <h5 class="text-muted">No submissions yet</h5>
                                <p class="text-muted mb-4">Students haven't submitted any work for this assignment</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="gradeModal" tabindex="-1" aria-labelledby="gradeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gradeModalLabel">Grade Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="gradeForm" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div id="submissionDetails"></div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label for="score" class="form-label">Score <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="score" name="score" 
                                   min="0" max="<?php echo e($assignment->max_score); ?>" required>
                            <small class="form-text text-muted">Maximum score: <?php echo e($assignment->max_score); ?></small>
                        </div>
                        <div class="col-md-6">
                            <label for="score_percentage" class="form-label">Percentage</label>
                            <input type="text" class="form-control" id="score_percentage" readonly>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label for="feedback" class="form-label">Feedback</label>
                        <textarea class="form-control" id="feedback" name="feedback" rows="4" 
                                  placeholder="Provide constructive feedback to the student..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Grade</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">View Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewSubmissionContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="customErrorModal" tabindex="-1" aria-labelledby="customErrorModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #2d3748; color: #e2e8f0; border-radius: 0.75rem; border: none; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);">
            <div class="modal-header" style="border-bottom: none; padding-bottom: 0;">
                <h5 class="modal-title fw-bold" id="customErrorModalLabel" style="color: #e2e8f0;">
                    <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                    <span id="errorTitle">Error</span>
                </h5>
            </div>
            <div class="modal-body" style="padding-top: 0.5rem; padding-bottom: 1.5rem; font-size: 1rem; line-height: 1.5;">
                <p id="errorMessage" class="mb-0"></p>
            </div>
            <div class="modal-footer" style="border-top: none; padding-top: 0; justify-content: flex-end;">
                <button type="button" class="btn" onclick="closeErrorModal()" style="background-color: #2563eb; border: 1px solid #2563eb; color: #ffffff; font-weight: 600; padding: 0.5rem 1.5rem; border-radius: 0.5rem;">
                    <i class="fas fa-check me-2"></i>OK
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="customSuccessModal" tabindex="-1" aria-labelledby="customSuccessModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #2d3748; color: #e2e8f0; border-radius: 0.75rem; border: none; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);">
            <div class="modal-header" style="border-bottom: none; padding-bottom: 0;">
                <h5 class="modal-title fw-bold" id="customSuccessModalLabel" style="color: #e2e8f0;">
                    <i class="fas fa-check-circle me-2 text-success"></i>
                    <span id="successTitle">Success</span>
                </h5>
            </div>
            <div class="modal-body" style="padding-top: 0.5rem; padding-bottom: 1.5rem; font-size: 1rem; line-height: 1.5;">
                <p id="successMessage" class="mb-0"></p>
            </div>
            <div class="modal-footer" style="border-top: none; padding-top: 0; justify-content: flex-end;">
                <button type="button" class="btn" onclick="closeSuccessModal()" style="background-color: #10b981; border: 1px solid #10b981; color: #ffffff; font-weight: 600; padding: 0.5rem 1.5rem; border-radius: 0.5rem;">
                    <i class="fas fa-check me-2"></i>OK
                </button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
    let currentSubmissionId = null;

    // Filter submissions by status
    function filterSubmissions(status) {
        const rows = document.querySelectorAll('.submission-row');
        rows.forEach(row => {
            if (status === 'all' || row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // View submission details
    function viewSubmission(submissionId) {
        // Load submission details via AJAX
        fetch(`/assignments/submissions/${submissionId}/view`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('viewSubmissionContent').innerHTML = data.html;
                new bootstrap.Modal(document.getElementById('viewModal')).show();
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal('Error loading submission details', 'Unable to load the submission details. Please try again or contact support if the problem persists.');
            });
    }

    // Grade submission
    function gradeSubmission(submissionId) {
        currentSubmissionId = submissionId;
        document.getElementById('gradeForm').action = `/assignments/submissions/${submissionId}/grade`;
        document.getElementById('gradeModalLabel').textContent = 'Grade Submission';
        
        // Load submission details
        fetch(`/assignments/submissions/${submissionId}/details`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('submissionDetails').innerHTML = data.html;
                new bootstrap.Modal(document.getElementById('gradeModal')).show();
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal('Error loading submission details', 'Unable to load the submission details for grading. Please try again.');
            });
    }

    // Edit existing grade
    function editGrade(submissionId) {
        currentSubmissionId = submissionId;
        document.getElementById('gradeForm').action = `/assignments/submissions/${submissionId}/grade`;
        document.getElementById('gradeModalLabel').textContent = 'Edit Grade';
        
        // Load existing grade data
        fetch(`/assignments/submissions/${submissionId}/grade-data`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('score').value = data.score;
                document.getElementById('feedback').value = data.feedback;
                updateScorePercentage();
                new bootstrap.Modal(document.getElementById('gradeModal')).show();
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal('Error Loading Grade Data', 'Unable to load the existing grade data. Please try again.');
            });
    }

    // Update score percentage
    function updateScorePercentage() {
        const score = document.getElementById('score').value;
        const maxScore = parseInt('<?php echo e($assignment->max_score); ?>');
        if (score && maxScore) {
            const percentage = (score / maxScore) * 100;
            document.getElementById('score_percentage').value = percentage.toFixed(1) + '%';
        }
    }

    // Show custom error modal
    function showErrorModal(title, message) {
        document.getElementById('errorTitle').textContent = title;
        document.getElementById('errorMessage').textContent = message;
        
        const modal = document.getElementById('customErrorModal');
        if (modal) {
            try {
                // Try Bootstrap 5 Modal first
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                }
                // Try jQuery/Bootstrap 4 Modal
                else if (typeof $ !== 'undefined' && $.fn.modal) {
                    $('#customErrorModal').modal('show');
                }
                // Manual fallback
                else {
                    modal.classList.add('show');
                    modal.style.display = 'block';
                    modal.setAttribute('aria-modal', 'true');
                    modal.setAttribute('role', 'dialog');
                    
                    // Add backdrop
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    backdrop.id = 'customErrorBackdrop';
                    document.body.appendChild(backdrop);
                    document.body.classList.add('modal-open');
                }
            } catch (e) {
                console.error('Error showing modal:', e);
                alert(title + ': ' + message);
            }
        }
    }

    // Close custom error modal
    function closeErrorModal() {
        const modal = document.getElementById('customErrorModal');
        const backdrop = document.getElementById('customErrorBackdrop');
        
        if (modal) {
            try {
                // Try Bootstrap 5
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }
                }
                // Try jQuery
                else if (typeof $ !== 'undefined' && $.fn.modal) {
                    $('#customErrorModal').modal('hide');
                }
                // Manual close
                else {
                    modal.classList.remove('show');
                    modal.style.display = 'none';
                    modal.removeAttribute('aria-modal');
                    modal.removeAttribute('role');
                    
                    if (backdrop) {
                        backdrop.remove();
                    }
                    
                    document.body.classList.remove('modal-open');
                }
            } catch (e) {
                console.error('Error closing modal:', e);
            }
        }
    }

    // Show custom success modal
    function showSuccessModal(title, message) {
        document.getElementById('successTitle').textContent = title;
        document.getElementById('successMessage').textContent = message;
        
        const modal = document.getElementById('customSuccessModal');
        if (modal) {
            try {
                // Try Bootstrap 5 Modal first
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                }
                // Try jQuery/Bootstrap 4 Modal
                else if (typeof $ !== 'undefined' && $.fn.modal) {
                    $('#customSuccessModal').modal('show');
                }
                // Manual fallback
                else {
                    modal.classList.add('show');
                    modal.style.display = 'block';
                    modal.setAttribute('aria-modal', 'true');
                    modal.setAttribute('role', 'dialog');
                    
                    // Add backdrop
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    backdrop.id = 'customSuccessBackdrop';
                    document.body.appendChild(backdrop);
                    document.body.classList.add('modal-open');
                }
            } catch (e) {
                console.error('Error showing modal:', e);
                alert(title + ': ' + message);
            }
        }
    }

    // Close custom success modal
    function closeSuccessModal() {
        const modal = document.getElementById('customSuccessModal');
        const backdrop = document.getElementById('customSuccessBackdrop');
        
        if (modal) {
            try {
                // Try Bootstrap 5
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }
                }
                // Try jQuery
                else if (typeof $ !== 'undefined' && $.fn.modal) {
                    $('#customSuccessModal').modal('hide');
                }
                // Manual close
                else {
                    modal.classList.remove('show');
                    modal.style.display = 'none';
                    modal.removeAttribute('aria-modal');
                    modal.removeAttribute('role');
                    
                    if (backdrop) {
                        backdrop.remove();
                    }
                    
                    document.body.classList.remove('modal-open');
                }
                
                // Reload page after closing success modal
                setTimeout(() => {
                    location.reload();
                }, 300);
            } catch (e) {
                console.error('Error closing modal:', e);
                location.reload();
            }
        }
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Update percentage when score changes
        document.getElementById('score').addEventListener('input', updateScorePercentage);
        
        // Handle form submission
        document.getElementById('gradeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // Disable submit button and show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Save Grade';
                
                if (data.success) {
                    // Close grading modal
                    const gradeModal = bootstrap.Modal.getInstance(document.getElementById('gradeModal'));
                    if (gradeModal) {
                        gradeModal.hide();
                    }
                    
                    // Show success modal
                    showSuccessModal('Grade Saved Successfully', data.message);
                } else {
                    showErrorModal('Error Saving Grade', data.message || 'Unable to save the grade. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Save Grade';
                
                showErrorModal('Error Saving Grade', 'Unable to save the grade. Please check your connection and try again.');
            });
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\assignments\submissions.blade.php ENDPATH**/ ?>