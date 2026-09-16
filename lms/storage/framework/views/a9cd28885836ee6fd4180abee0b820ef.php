<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Help Center</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Help</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            You are signed in as <strong><?php echo e($role); ?></strong>. Use the menus on the left for your role. If a page is not listed, your account is not allowed to open it.
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Student identifiers</h5>
                    </div>
                    <div class="card-body">
                        <p>A student can have more than one ID. They belong to the same person:</p>
                        <ul class="mb-0">
                            <li><strong>Student Number</strong> — printed on student lists and class pages (for example STD2). This comes from the admission number, or STD plus the student record number.</li>
                            <li><strong>Account ID</strong> — the login account code (for example 000006). This is the User ID used to sign in.</li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Class schedules</h5>
                    </div>
                    <div class="card-body">
                        <p>Student class pages, weekly schedules, teacher schedules, and Admin Class Schedules all read from the same Class Schedules list.</p>
                        <p class="mb-0">If a weekly schedule is empty, the Principal (Admin) needs to create the timetable under Academic Management → Class Schedules. A class can be enrolled without a weekly timetable yet.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Who can do what</h5>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li><strong>Admin (Principal)</strong> — users, sections, class schedules, promotions, reports, analytics, backup.</li>
                            <li><strong>Registrar</strong> — enrollment applications, portal, chat with families.</li>
                            <li><strong>Teacher</strong> — assigned classes, lessons, assignments, attendance, grading.</li>
                            <li><strong>Student</strong> — own classes, assignments, grades, attendance, and schedule.</li>
                            <li><strong>Parent</strong> — linked child’s section, grades, attendance, and schedule.</li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Troubleshooting</h5>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li><strong>Parent sees “no section”</strong> — ask the Registrar or Admin to confirm the child’s section on the student record. The parent view now uses the same section as the Admin student list.</li>
                            <li><strong>403 Unauthorized</strong> — that feature belongs to another role. Use Help and your sidebar instead of typing a URL.</li>
                            <li><strong>Forgot password</strong> — use Forgot Password on the login page. A reset link is sent to the account email.</li>
                            <li><strong>Cannot log in</strong> — the account may be inactive. Contact the Principal or Registrar.</li>
                            <li><strong>Wrong child or class</strong> — contact the Registrar so the parent link or subject enrollment can be corrected.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick start by role</h5>
            </div>
            <div class="card-body">
                <?php if($role === 'Admin'): ?>
                    <ol class="mb-0">
                        <li>Create or review users under User Management.</li>
                        <li>Set grade sections and subjects under Classes &amp; Subjects.</li>
                        <li>Add weekly times under Class Schedules so teachers, students, and parents see the same timetable.</li>
                        <li>Use Student Promotions at the end of the year to move students to the next grade.</li>
                    </ol>
                <?php elseif($role === 'Registrar'): ?>
                    <ol class="mb-0">
                        <li>Open Enrollment Management to review applications.</li>
                        <li>Approve complete applications so student and parent accounts are created.</li>
                        <li>Use Chat if a family needs help with documents or status.</li>
                    </ol>
                <?php elseif($role === 'Teacher'): ?>
                    <ol class="mb-0">
                        <li>Open My Classes to see assigned sections and student counts.</li>
                        <li>Publish lessons and assignments for those classes.</li>
                        <li>Record attendance and grades for the selected section and subject.</li>
                    </ol>
                <?php elseif($role === 'Student'): ?>
                    <ol class="mb-0">
                        <li>Open My Classes to view lessons and assignments.</li>
                        <li>Check Class Schedule for the official weekly timetable.</li>
                        <li>Submit work before the due date and review grades when posted.</li>
                    </ol>
                <?php elseif($role === 'Parent'): ?>
                    <ol class="mb-0">
                        <li>Select your child on the dashboard if more than one is linked.</li>
                        <li>Use Class Schedule and Attendance to follow their week.</li>
                        <li>Message the teacher or Registrar from Chat if something looks missing.</li>
                    </ol>
                <?php else: ?>
                    <p class="mb-0">Open your dashboard and follow the menus for your account type.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/help/index.blade.php ENDPATH**/ ?>