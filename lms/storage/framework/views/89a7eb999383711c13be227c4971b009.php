
<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><i class="fas fa-user-graduate me-2"></i>Student Information System (SIS)</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('student/list')); ?>">Students</a></li>
                        <li class="breadcrumb-item active">Student SIS</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="<?php echo e(route('student/list')); ?>" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Students
                    </a>
                    <a href="<?php echo e(url('view/user/edit/'.$user->user_id)); ?>" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit User
                    </a>
                </div>
            </div>
        </div>

        
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <img src="<?php echo e(asset('images/'.$user->avatar)); ?>"
                         alt="<?php echo e($student->full_name); ?>"
                         class="rounded-circle"
                         style="width: 72px; height: 72px; object-fit: cover;">
                    <div class="flex-grow-1">
                        <h4 class="mb-1"><?php echo e($student->full_name); ?></h4>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge bg-primary"><?php echo e($student->year_level); ?></span>
                            <?php if($student->enrollment_status === 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php elseif($student->enrollment_status === 'graduated'): ?>
                                <span class="badge bg-info">Graduated</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?php echo e(ucfirst($student->enrollment_status)); ?></span>
                            <?php endif; ?>
                            <small class="text-muted">Student ID: <?php echo e($student->student_id ?? 'N/A'); ?> · User ID: <?php echo e($user->user_id); ?></small>
                        </div>
                    </div>
                    <div class="d-flex gap-3 text-center">
                        <div>
                            <div class="fw-bold text-primary"><?php echo e($currentEnrollments->count()); ?></div>
                            <small class="text-muted">Subjects</small>
                        </div>
                        <div>
                            <div class="fw-bold text-success"><?php echo e($currentGPA ? number_format($currentGPA->gpa, 2) : 'N/A'); ?></div>
                            <small class="text-muted">GPA</small>
                        </div>
                        <div>
                            <div class="fw-bold text-info"><?php echo e($attendancePercentage); ?>%</div>
                            <small class="text-muted">Attendance</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="fw-bold" style="width: 40%;">Full Name:</td>
                                <td><?php echo e($student->full_name); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Date of Birth:</td>
                                <td><?php echo e($student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('M d, Y') : 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Age:</td>
                                <td><?php echo e($student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->age . ' years' : 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Gender:</td>
                                <td><?php echo e($student->gender ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Email:</td>
                                <td><?php echo e($student->email); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Phone:</td>
                                <td><?php echo e($student->phone_number ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Address:</td>
                                <td><?php echo e($student->address ?? 'N/A'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Parent/Guardian Info</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="fw-bold" style="width: 40%;">Name:</td>
                                <td><?php echo e($student->parent_name ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Relationship:</td>
                                <td><?php echo e($student->parent_relationship ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Email:</td>
                                <td><?php echo e($student->parent_email ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Phone:</td>
                                <td><?php echo e($student->parent_phone ?? 'N/A'); ?></td>
                            </tr>
                        </table>
                        <hr>
                        <h6 class="fw-bold text-danger mb-2">Emergency Contact</h6>
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="fw-bold" style="width: 40%;">Name:</td>
                                <td><?php echo e($student->emergency_contact_name ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Phone:</td>
                                <td><?php echo e($student->emergency_contact_phone ?? 'N/A'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Academic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Year Level:</strong> <?php echo e($student->year_level); ?></p>
                                <p class="mb-0"><strong>Previous School:</strong> <?php echo e($student->previous_school ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <?php if($sectionAssignment): ?>
                                    <p class="mb-2"><strong>Section:</strong> <?php echo e($sectionAssignment->name); ?></p>
                                    <p class="mb-2"><strong>Academic Year:</strong> <?php echo e($sectionAssignment->academic_year_name); ?></p>
                                    <p class="mb-0"><strong>Semester:</strong> <?php echo e($sectionAssignment->semester_name); ?></p>
                                <?php else: ?>
                                    <p class="text-muted mb-0">No section assigned yet</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-book me-2"></i>Current Subject Enrollments (<?php echo e($currentEnrollments->count()); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if($currentEnrollments->count() > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Subject</th>
                                            <th>Code</th>
                                            <th>Academic Year</th>
                                            <th>Semester</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $currentEnrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($index + 1); ?></td>
                                                <td><?php echo e($enrollment->subject->subject_name); ?></td>
                                                <td><?php echo e($enrollment->subject->subject_code ?? 'N/A'); ?></td>
                                                <td><?php echo e($enrollment->academicYear->name ?? 'N/A'); ?></td>
                                                <td><?php echo e($enrollment->semester->name ?? 'N/A'); ?></td>
                                                <td><span class="badge bg-success"><?php echo e(ucfirst($enrollment->status)); ?></span></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>No active subject enrollments found.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-folder-open me-2"></i>More Details</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Additional records are hidden to keep this page clear. Open any item below to view.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#sisQuarterlyGradesModal">
                                <i class="fas fa-table me-1"></i> Quarterly Grades
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#sisGradesModal">
                                <i class="fas fa-chart-line me-1"></i> Grades &amp; Performance
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#sisGpaModal">
                                <i class="fas fa-trophy me-1"></i> GPA History
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#sisAttendanceModal">
                                <i class="fas fa-calendar-check me-1"></i> Attendance Summary
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#sisPromotionModal">
                                <i class="fas fa-history me-1"></i> Promotion History
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#sisDocumentsModal">
                                <i class="fas fa-file-alt me-1"></i> Enrollment Documents
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="sisQuarterlyGradesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-table me-2"></i>Quarterly Grades</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if($quarterlyGrades->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Subject</th>
                                    <th>Academic Year</th>
                                    <th class="text-center">Q1</th>
                                    <th class="text-center">Q2</th>
                                    <th class="text-center">Q3</th>
                                    <th class="text-center">Q4</th>
                                    <th class="text-center">Final</th>
                                    <th class="text-center">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $quarterlyGrades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($qg->subject->subject_name ?? 'N/A'); ?></td>
                                        <td><?php echo e($qg->academicYear->name ?? 'N/A'); ?></td>
                                        <td class="text-center"><?php echo e($qg->quarter_1 !== null ? number_format($qg->quarter_1, 2) : '—'); ?></td>
                                        <td class="text-center"><?php echo e($qg->quarter_2 !== null ? number_format($qg->quarter_2, 2) : '—'); ?></td>
                                        <td class="text-center"><?php echo e($qg->quarter_3 !== null ? number_format($qg->quarter_3, 2) : '—'); ?></td>
                                        <td class="text-center"><?php echo e($qg->quarter_4 !== null ? number_format($qg->quarter_4, 2) : '—'); ?></td>
                                        <td class="text-center"><strong><?php echo e($qg->final_grade !== null ? number_format($qg->final_grade, 2) : '—'); ?></strong></td>
                                        <td class="text-center"><?php echo e($qg->remarks ?? '—'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0"><i class="fas fa-info-circle me-2"></i>No quarterly grades recorded yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="sisGradesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-chart-line me-2"></i>Grades &amp; Performance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if($grades->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Subject</th>
                                    <th>Quarter</th>
                                    <th>Grade</th>
                                    <th>Percentage</th>
                                    <th>Academic Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $grades->take(20); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($grade->subject->subject_name); ?></td>
                                        <td><?php echo e($grade->quarter); ?></td>
                                        <td><strong><?php echo e($grade->grade); ?></strong></td>
                                        <td>
                                            <span class="badge <?php echo e($grade->percentage >= 75 ? 'bg-success' : 'bg-danger'); ?>">
                                                <?php echo e(number_format($grade->percentage, 1)); ?>%
                                            </span>
                                        </td>
                                        <td><?php echo e($grade->academicYear->name ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if($grades->count() > 20): ?>
                        <p class="text-muted text-center mb-0 mt-2">Showing 20 of <?php echo e($grades->count()); ?> grades</p>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info mb-0"><i class="fas fa-info-circle me-2"></i>No grades recorded yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="sisGpaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-trophy me-2"></i>GPA History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if($gpaRecords->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Academic Year</th>
                                    <th>Semester</th>
                                    <th>GPA</th>
                                    <th>Ranking</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $gpaRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gpa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($gpa->academicYear->name ?? 'N/A'); ?></td>
                                        <td><?php echo e($gpa->semester->name ?? 'N/A'); ?></td>
                                        <td><strong class="text-success"><?php echo e(number_format($gpa->gpa, 2)); ?></strong></td>
                                        <td><?php echo e($gpa->ranking ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0"><i class="fas fa-info-circle me-2"></i>No GPA records yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="sisAttendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calendar-check me-2"></i>Attendance Summary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row text-center">
                    <div class="col-6 col-md-3 mb-3">
                        <h4 class="fw-bold text-primary"><?php echo e($totalAttendance); ?></h4>
                        <p class="text-muted mb-0">Total Records</p>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <h4 class="fw-bold text-success"><?php echo e($presentCount); ?></h4>
                        <p class="text-muted mb-0">Present</p>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <h4 class="fw-bold text-danger"><?php echo e($absentCount); ?></h4>
                        <p class="text-muted mb-0">Absent</p>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <h4 class="fw-bold text-info"><?php echo e($attendancePercentage); ?>%</h4>
                        <p class="text-muted mb-0">Attendance Rate</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="sisPromotionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-history me-2"></i>Promotion History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if($promotionHistory->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Status</th>
                                    <th>GPA</th>
                                    <th>Date</th>
                                    <th>Promoted By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $promotionHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promotion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($promotion->from_year_level); ?></td>
                                        <td><?php echo e($promotion->to_year_level); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo e($promotion->status_badge); ?>">
                                                <?php echo e(ucfirst($promotion->promotion_status)); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($promotion->final_gpa ? number_format($promotion->final_gpa, 2) : 'N/A'); ?></td>
                                        <td><?php echo e(\Carbon\Carbon::parse($promotion->promotion_date)->format('M d, Y')); ?></td>
                                        <td><?php echo e($promotion->promoter->name ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0"><i class="fas fa-info-circle me-2"></i>No promotion history yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="sisDocumentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Enrollment Documents</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if(count($enrollmentDocuments) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Document Type</th>
                                    <th>File Name</th>
                                    <th>Status</th>
                                    <th>Uploaded</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $enrollmentDocuments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e(\App\Models\EnrollmentDocument::DOCUMENT_TYPES[$doc->document_type] ?? $doc->document_type); ?></td>
                                        <td><?php echo e($doc->file_name); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo e($doc->status_badge); ?>">
                                                <?php echo e(ucfirst($doc->status)); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($doc->created_at->format('M d, Y')); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0"><i class="fas fa-info-circle me-2"></i>No enrollment documents uploaded.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\student\sis.blade.php ENDPATH**/ ?>