
<div class="row">
    
    <div class="col-lg-4">
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td class="fw-bold" style="width: 45%;">Full Name:</td>
                        <td><?php echo e($student->full_name); ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Student ID:</td>
                        <td><?php echo e($student->student_id ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Date of Birth:</td>
                        <td><?php echo e(\Carbon\Carbon::parse($student->date_of_birth)->format('M d, Y')); ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Age:</td>
                        <td><?php echo e(\Carbon\Carbon::parse($student->date_of_birth)->age); ?> years</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Gender:</td>
                        <td><?php echo e($student->gender); ?></td>
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
                        <td><?php echo e($student->address); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-users me-2"></i>Parent/Guardian Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td class="fw-bold" style="width: 45%;">Name:</td>
                        <td><?php echo e($student->parent_name); ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Relationship:</td>
                        <td><?php echo e($student->parent_relationship ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Email:</td>
                        <td><?php echo e($student->parent_email); ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Phone:</td>
                        <td><?php echo e($student->parent_phone); ?></td>
                    </tr>
                </table>
                
                <?php if($student->emergency_contact_name): ?>
                    <hr>
                    <h6 class="fw-bold text-danger mb-2"><i class="fas fa-phone-alt me-1"></i>Emergency Contact</h6>
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="fw-bold" style="width: 45%;">Name:</td>
                            <td><?php echo e($student->emergency_contact_name); ?></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Phone:</td>
                            <td><?php echo e($student->emergency_contact_phone); ?></td>
                        </tr>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Account Status</h6>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Status:</strong> 
                    <?php if($student->enrollment_status === 'active'): ?>
                        <span class="badge bg-success">Active</span>
                    <?php elseif($student->enrollment_status === 'graduated'): ?>
                        <span class="badge bg-info">Graduated</span>
                    <?php else: ?>
                        <span class="badge bg-secondary"><?php echo e(ucfirst($student->enrollment_status)); ?></span>
                    <?php endif; ?>
                </p>
                <p class="mb-0">
                    <strong>Year Level:</strong> 
                    <span class="badge bg-primary"><?php echo e($student->year_level); ?></span>
                </p>
            </div>
        </div>
    </div>

    
    <div class="col-lg-8">
        
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Academic Performance Overview</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="p-3">
                            <h4 class="fw-bold text-primary"><?php echo e($currentEnrollments->count()); ?></h4>
                            <p class="text-muted mb-0 small">Current Subjects</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <h4 class="fw-bold text-success"><?php echo e($currentGPA ? number_format($currentGPA->gpa, 2) : 'N/A'); ?></h4>
                            <p class="text-muted mb-0 small">Current GPA</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <h4 class="fw-bold text-info"><?php echo e($attendancePercentage); ?>%</h4>
                            <p class="text-muted mb-0 small">Attendance Rate</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <h4 class="fw-bold text-warning"><?php echo e($student->year_level); ?></h4>
                            <p class="text-muted mb-0 small">Year Level</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <?php if($sectionAssignment): ?>
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-school me-2"></i>Academic Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Section:</strong> <?php echo e($sectionAssignment->name); ?></p>
                        <p><strong>Grade Level:</strong> <?php echo e($sectionAssignment->grade_level); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Academic Year:</strong> <?php echo e($sectionAssignment->academic_year_name); ?></p>
                        <p><strong>Semester:</strong> <?php echo e($sectionAssignment->semester_name); ?></p>
                    </div>
                </div>
                <?php if($student->previous_school): ?>
                    <p class="mb-0"><strong>Previous School:</strong> <?php echo e($student->previous_school); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-book me-2"></i>My Current Subjects (<?php echo e($currentEnrollments->count()); ?>)</h6>
            </div>
            <div class="card-body">
                <?php if($currentEnrollments->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Subject Name</th>
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
                                        <td>
                                            <span class="badge bg-success"><?php echo e(ucfirst($enrollment->status)); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>No active subject enrollments found.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <?php if($grades->count() > 0): ?>
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-star me-2"></i>Recent Grades (Last 10)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
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
                            <?php $__currentLoopData = $grades->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                <div class="text-center mt-2">
                    <a href="<?php echo e(route('student.grades')); ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-chart-bar me-1"></i>View All Grades
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if($gpaRecords->count() > 0): ?>
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-trophy me-2"></i>GPA History</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
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
                                    <td>
                                        <strong class="text-success"><?php echo e(number_format($gpa->gpa, 2)); ?></strong>
                                    </td>
                                    <td><?php echo e($gpa->ranking ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Attendance Summary</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h5 class="fw-bold text-primary"><?php echo e($totalAttendance); ?></h5>
                        <p class="text-muted mb-0 small">Total Records</p>
                    </div>
                    <div class="col-md-3">
                        <h5 class="fw-bold text-success"><?php echo e($presentCount); ?></h5>
                        <p class="text-muted mb-0 small">Present</p>
                    </div>
                    <div class="col-md-3">
                        <h5 class="fw-bold text-danger"><?php echo e($absentCount); ?></h5>
                        <p class="text-muted mb-0 small">Absent</p>
                    </div>
                    <div class="col-md-3">
                        <h5 class="fw-bold text-info"><?php echo e($attendancePercentage); ?>%</h5>
                        <p class="text-muted mb-0 small">Attendance Rate</p>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="<?php echo e(route('student.attendance')); ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-calendar-check me-1"></i>View Full Attendance
                    </a>
                </div>
            </div>
        </div>

        
        <?php if($promotionHistory->count() > 0): ?>
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-history me-2"></i>Promotion History</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>From</th>
                                <th>To</th>
                                <th>Status</th>
                                <th>GPA</th>
                                <th>Date</th>
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
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if(count($enrollmentDocuments) > 0): ?>
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Enrollment Documents</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
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
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/dashboard/partials/student_sis.blade.php ENDPATH**/ ?>