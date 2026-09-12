<?php if(!isset($student['hasStudent']) || !$student['hasStudent']): ?>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-user-graduate text-warning" style="font-size: 4rem; opacity: 0.6;"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-3">Student Profile Not Found</h4>
                    <p class="text-muted mb-4">Your student profile is being set up. Please contact the registrar's office if this issue persists.</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> If you just completed enrollment, your student profile may take a few minutes to be created.
                    </div>
                    <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Return to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
<div class="page-header">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-sub-header">
                <h3 class="page-title">Welcome <?php echo e($student['student']->first_name ?? $student['student']->name ?? 'Student'); ?>!</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Student</li>
                </ul>
            </div>
        </div>
    </div>
</div>


<style>
.student-hero-card {
    background: #ffffff;
    border-radius: 24px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    border: 1px solid #e5e7eb;
    overflow: hidden;
    position: relative;
}

.student-photo-wrapper {
    position: relative;
    width: 140px;
    height: 140px;
    margin: 0 auto;
}

.student-photo {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    border: 4px solid #e5e7eb;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    object-fit: cover;
    transition: all 0.4s ease;
}

.student-photo:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.info-pill {
    background: #f9fafb;
    border-radius: 16px;
    padding: 16px 20px;
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
}

.info-pill:hover {
    background: #f3f4f6;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.info-pill i {
    font-size: 1.5rem;
    margin-bottom: 8px;
    color: #4b5563;
}

.info-pill small {
    display: block;
    color: #6b7280;
    font-size: 0.75rem;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-pill .value {
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0;
    color: #111827;
}
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="card student-hero-card">
            <div class="card-body p-4 p-md-5" style="position: relative; z-index: 1;">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center mb-4 mb-md-0">
                        <div class="student-photo-wrapper">
                            <?php if(!empty($student['student']->upload)): ?>
                                <img src="<?php echo e(asset('storage/' . $student['student']->upload)); ?>" alt="Student Photo" class="student-photo">
                            <?php else: ?>
                                <img src="<?php echo e(URL::to('assets/img/profiles/avatar-01.jpg')); ?>" alt="Student Photo" class="student-photo">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <h2 class="text-dark mb-1 fw-bold" style="font-size: 2rem;">
                            <?php echo e($student['student']->first_name); ?> <?php echo e($student['student']->middle_name); ?> <?php echo e($student['student']->last_name); ?>

                        </h2>
                        <p class="mb-4 text-muted" style="font-size: 1.1rem;">Student Dashboard</p>
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="info-pill text-center">
                                    <i class="fas fa-id-card"></i>
                                    <small>Student ID</small>
                                    <div class="value"><?php echo e($student['student']->user_id ?? 'N/A'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-pill text-center">
                                    <i class="fas fa-graduation-cap"></i>
                                    <small>Grade Level</small>
                                    <div class="value"><?php echo e($student['student']->year_level ?? 'Not Set'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-pill text-center">
                                    <i class="fas fa-users"></i>
                                    <small>My Section</small>
                                    <?php
                                        $studentSection = $student['student']->sections->first();
                                    ?>
                                    <?php if($studentSection): ?>
                                        <div class="value"><?php echo e($studentSection->name); ?></div>
                                        <?php if($studentSection->adviser): ?>
                                            <small class="text-muted" style="font-size: 0.7rem; text-transform: none;">Adviser: <?php echo e($studentSection->adviser->full_name ?? 'TBA'); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="value"><span class="badge bg-warning text-dark" style="font-size: 0.85rem;">Not Assigned</span></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.modern-card {
    border-radius: 20px;
    border: none;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
}

.modern-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
}

.modern-card .card-header {
    background: #ffffff;
    border-bottom: 2px solid #e5e7eb;
    padding: 20px 24px;
}

.modern-card .card-title {
    font-weight: 700;
    font-size: 1.2rem;
    margin: 0;
    color: #111827;
}

.course-card {
    background: linear-gradient(135deg, #f6f8fb 0%, #ffffff 100%);
    border-radius: 16px;
    padding: 18px;
    margin-bottom: 16px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.course-card:hover {
    border-color: #d1d5db;
    transform: translateX(4px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.course-icon {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4b5563;
    font-size: 1.4rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.lesson-table thead th {
    background: #f9fafb;
    color: #374151;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.lesson-table tbody tr {
    transition: all 0.3s ease;
}

.lesson-table tbody tr:hover {
    background: #f9fafb;
    transform: scale(1.01);
}

.lesson-table tbody td {
    padding: 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f0f0f0;
}

.status-badge {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}
</style>

<div class="row">
    <div class="col-12 col-lg-12 col-xl-8">
        <div class="card modern-card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-book-open me-2"></i>Today's Lessons
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover lesson-table mb-0">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Lesson</th>
                                <th>Academic Period</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = isset($student['enrollments']) ? $student['enrollments'] : []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="subject-icon me-3" style="width: 40px; height: 40px; background: #f3f4f6; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #4b5563;">
                                            <i class="fas fa-book"></i>
                                        </div>
                                        <strong><?php echo e($enrollment->subject->subject_name ?? 'Subject'); ?></strong>
                                    </div>
                                </td>
                                <td><?php echo e($enrollment->subject->description ?? 'Lesson Description'); ?></td>
                                <td>
                                    <div>
                                        <div class="fw-semibold"><?php echo e($enrollment->academicYear->name ?? 'N/A'); ?></div>
                                        <small class="text-muted"><?php echo e($enrollment->semester->name ?? 'N/A'); ?></small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="status-badge">Active</span>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <div>
                                        <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                        <p class="mb-0">No lessons available yet</p>
                                        <small>Your enrollments will appear here once approved</small>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-12 col-xl-4">
        <div class="card modern-card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-graduation-cap me-2"></i>My Courses
                </h5>
            </div>
            <div class="card-body">
                <?php $__empty_1 = true; $__currentLoopData = isset($student['enrollments']) ? $student['enrollments'] : []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="course-card">
                    <div class="d-flex align-items-center">
                        <div class="course-icon me-3">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold"><?php echo e($enrollment->subject->subject_name ?? 'Subject'); ?></h6>
                            <p class="text-muted mb-0 small"><?php echo e($enrollment->academicYear->name ?? 'N/A'); ?> - <?php echo e($enrollment->semester->name ?? 'N/A'); ?></p>
                        </div>
                        <div>
                            <span class="status-badge">Active</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                    <p class="mb-0">No courses yet</p>
                    <small>Courses will appear after enrollment</small>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php
    $enrollmentApplication = null;
    if (isset($student['student']) && $student['student']->enrollmentApplication) {
        $enrollmentApplication = $student['student']->enrollmentApplication;
    }
?>

<?php if($enrollmentApplication): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card modern-card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-file-alt me-2"></i>My Enrollment Application
                </h5>
            </div>
            <div class="card-body">
                <?php
                    $requiredDocuments = [
                        'birth_certificate' => 'Birth Certificate',
                        'sf9' => 'SF9 (Learner\'s Permanent Record)',
                        'sf10' => 'SF10 (Report Card)',
                        'good_moral' => 'Certificate of Good Moral Character',
                        'id_photo' => 'ID Photo (2x2)',
                        'parent_guardian_id' => 'Parent/Guardian ID'
                    ];
                    
                    $uploadedTypes = $enrollmentApplication->documents->pluck('document_type')->toArray();
                    $missingDocuments = array_diff(array_keys($requiredDocuments), $uploadedTypes);
                ?>
                
                <div class="row mb-4 g-3">
                    <div class="col-md-6">
                        <div class="info-pill" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(96, 165, 250, 0.1) 100%); border: 2px solid rgba(59, 130, 246, 0.2); color: #1e40af;">
                            <i class="fas fa-info-circle" style="color: #3b82f6;"></i>
                            <small style="color: #1e40af;">Application Status</small>
                            <div class="value">
                                <span class="badge" style="background: linear-gradient(135deg, <?php echo e($enrollmentApplication->status === 'approved' ? '#10b981, #059669' : ($enrollmentApplication->status === 'rejected' ? '#ef4444, #dc2626' : '#f59e0b, #d97706')); ?>); padding: 8px 20px; font-size: 0.9rem;">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $enrollmentApplication->status))); ?>

                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-pill" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.1) 100%); border: 2px solid rgba(16, 185, 129, 0.2); color: #065f46;">
                            <i class="fas fa-file-upload" style="color: #10b981;"></i>
                            <small style="color: #065f46;">Documents Uploaded</small>
                            <div class="value"><?php echo e($enrollmentApplication->documents->count()); ?>/6 Complete</div>
                        </div>
                    </div>
                </div>
                
                <?php if(count($missingDocuments) > 0): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Missing Required Documents:</strong>
                        <ul class="mb-0 mt-2">
                            <?php $__currentLoopData = $missingDocuments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $missingType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($requiredDocuments[$missingType]); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                        <small class="mt-2 d-block">
                            <i class="fas fa-info-circle me-1"></i>
                            Please upload these documents to complete your enrollment. Contact the registrar's office for assistance.
                        </small>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>All Required Documents Uploaded!</strong>
                        <p class="mb-0 mt-1">Your enrollment application has all the necessary documents for review.</p>
                    </div>
                <?php endif; ?>
                
                <div class="text-center">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#studentEnrollmentModal">
                        <i class="fas fa-eye me-2"></i>View Full Application Details
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="studentEnrollmentModal" tabindex="-1" aria-labelledby="studentEnrollmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="studentEnrollmentModalLabel">
                    <i class="fas fa-file-alt me-2"></i>My Enrollment Application Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                <div class="alert alert-<?php echo e($enrollmentApplication->status === 'approved' ? 'success' : ($enrollmentApplication->status === 'rejected' ? 'danger' : 'info')); ?>">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Application Status</h6>
                            <p class="mb-0 mt-1">
                                <strong>Status:</strong> 
                                <span class="badge bg-<?php echo e($enrollmentApplication->status === 'approved' ? 'success' : ($enrollmentApplication->status === 'rejected' ? 'danger' : 'warning')); ?>">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $enrollmentApplication->status))); ?>

                                </span>
                            </p>
                            <p class="mb-0"><strong>Application Number:</strong> <?php echo e($enrollmentApplication->application_number); ?></p>
                            <p class="mb-0"><strong>Submitted:</strong> <?php echo e($enrollmentApplication->created_at->format('M d, Y g:i A')); ?></p>
                            <?php if($enrollmentApplication->reviewed_at): ?>
                                <p class="mb-0"><strong>Reviewed:</strong> <?php echo e($enrollmentApplication->reviewed_at->format('M d, Y g:i A')); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="fw-bold" style="width: 40%;">Full Name:</td>
                                        <td><?php echo e($enrollmentApplication->full_name); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Date of Birth:</td>
                                        <td><?php echo e(\Carbon\Carbon::parse($enrollmentApplication->date_of_birth)->format('M d, Y')); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Gender:</td>
                                        <td><?php echo e($enrollmentApplication->gender); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Email:</td>
                                        <td><?php echo e($enrollmentApplication->email); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="fw-bold" style="width: 40%;">Phone:</td>
                                        <td><?php echo e($enrollmentApplication->phone_number); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Address:</td>
                                        <td><?php echo e($enrollmentApplication->address); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Grade Level:</td>
                                        <td><span class="badge bg-primary"><?php echo e($enrollmentApplication->grade_level_applying_for); ?></span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Previous School:</td>
                                        <td><?php echo e($enrollmentApplication->previous_school ?: 'N/A'); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-users me-2"></i>Parent/Guardian Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="fw-bold" style="width: 40%;">Name:</td>
                                        <td><?php echo e($enrollmentApplication->parent_name); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Email:</td>
                                        <td><?php echo e($enrollmentApplication->parent_email); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="fw-bold" style="width: 40%;">Phone:</td>
                                        <td><?php echo e($enrollmentApplication->parent_phone); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Relationship:</td>
                                        <td><?php echo e($enrollmentApplication->parent_relationship ?? 'N/A'); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="card mb-3">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-phone-alt me-2"></i>Emergency Contact</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> <?php echo e($enrollmentApplication->emergency_contact_name ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Phone:</strong> <?php echo e($enrollmentApplication->emergency_contact_phone ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-file-upload me-2"></i>Uploaded Documents (<?php echo e($enrollmentApplication->documents->count()); ?>/6)</h6>
                    </div>
                    <div class="card-body">
                        <?php if($enrollmentApplication->documents->count() > 0): ?>
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
                                        <?php $__currentLoopData = $enrollmentApplication->documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e(\App\Models\EnrollmentDocument::DOCUMENT_TYPES[$doc->document_type] ?? $doc->document_type); ?></td>
                                                <td><?php echo e($doc->file_name); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo e($doc->status === 'verified' ? 'success' : ($doc->status === 'rejected' ? 'danger' : 'warning')); ?>">
                                                        <?php echo e(ucfirst($doc->status)); ?>

                                                    </span>
                                                    <?php if($doc->verification_notes): ?>
                                                        <br><small class="text-muted"><?php echo e($doc->verification_notes); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo e($doc->created_at->format('M d, Y')); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">No documents uploaded yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                
                <?php if($enrollmentApplication->grade_level_applying_for): ?>
                    <?php
                        $subjects = \App\Helpers\GradeSubjectsHelper::getSubjectsForGrade($enrollmentApplication->grade_level_applying_for);
                    ?>
                    <?php if($subjects): ?>
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-book me-2"></i>Subjects for <?php echo e($enrollmentApplication->grade_level_applying_for); ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-md-4 col-sm-6 mb-2">
                                            <div class="badge bg-success fs-6 p-2 w-100 text-start">
                                                <i class="fas fa-book me-2"></i><?php echo e($subject); ?>

                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <div class="mt-3 text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Total: <?php echo e(count($subjects)); ?> subjects
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                
                <?php if($enrollmentApplication->notes): ?>
                    <div class="alert alert-info mt-3">
                        <h6 class="fw-bold"><i class="fas fa-sticky-note me-2"></i>Registrar Notes:</h6>
                        <p class="mb-0"><?php echo e($enrollmentApplication->notes); ?></p>
                    </div>
                <?php endif; ?>

                <?php if($enrollmentApplication->rejection_reason): ?>
                    <div class="alert alert-danger mt-3">
                        <h6 class="fw-bold"><i class="fas fa-times-circle me-2"></i>Rejection Reason:</h6>
                        <p class="mb-0"><?php echo e($enrollmentApplication->rejection_reason); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>


<?php if($enrollmentApplication && $enrollmentApplication->grade_level_applying_for): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-book text-success me-2"></i>My Subjects for <?php echo e($enrollmentApplication->grade_level_applying_for); ?>

                </h5>
            </div>
            <div class="card-body">
                <?php
                    $subjects = \App\Helpers\GradeSubjectsHelper::getSubjectsForGrade($enrollmentApplication->grade_level_applying_for);
                    $subjectCount = count($subjects);
                ?>
                
                <?php if($subjects): ?>
                    <div class="row">
                        <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-4 col-sm-6 mb-2">
                                <div class="badge bg-success fs-6 p-2 w-100 text-start">
                                    <i class="fas fa-book me-2"></i><?php echo e($subject); ?>

                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    
                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            You will be enrolled in <?php echo e($subjectCount); ?> subjects for <?php echo e($enrollmentApplication->grade_level_applying_for); ?>

                        </small>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Subjects Not Available</strong>
                        <p class="mb-0 mt-1">Subject information for <?php echo e($enrollmentApplication->grade_level_applying_for); ?> is not yet configured.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/partials/student_dashboard.blade.php ENDPATH**/ ?>