<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Enrollment Portal'); ?> — Panorama Montessori School</title>

    <link rel="stylesheet" href="<?php echo e(URL::to('assets/plugins/bootstrap/css/bootstrap.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(URL::to('assets/plugins/fontawesome/css/all.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(URL::to('assets/css/toastr.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/enrollment-portal.css')); ?>">
    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body class="ep-body">
<script>try{if(localStorage.getItem('ep-dark-mode')==='true')document.body.classList.add('ep-dark');}catch(e){}</script>
    <div class="ep-overlay" id="epOverlay"></div>

    <div class="ep-app">
        
        <aside class="ep-sidebar" id="epSidebar" aria-label="Main navigation">
            <div class="ep-brand">
                <img src="<?php echo e(URL::to('assets/img/Logo.jpg')); ?>" alt="School Logo">
                <div class="ep-brand-text">
                    <h1>Panorama Montessori</h1>
                    <span>Enrollment Portal</span>
                </div>
            </div>

            <nav class="ep-nav">
                <div class="ep-nav-group-title">Main</div>
                <a href="<?php echo e(route('enrollment.portal.index')); ?>" class="ep-nav-link <?php echo e(request()->routeIs('enrollment.portal.index') ? 'active' : ''); ?>">
                    <i class="fas fa-house"></i><span class="ep-nav-label">Home</span>
                </a>
                <a href="<?php echo e(route('enrollment.portal.create')); ?>" class="ep-nav-link <?php echo e(request()->routeIs('enrollment.portal.create') ? 'active' : ''); ?>">
                    <i class="fas fa-file-circle-plus"></i><span class="ep-nav-label">Apply Now</span>
                </a>
                <a href="<?php echo e(route('enrollment.portal.status')); ?>" class="ep-nav-link <?php echo e(request()->routeIs('enrollment.portal.status') || request()->routeIs('enrollment.portal.check-status') ? 'active' : ''); ?>">
                    <i class="fas fa-magnifying-glass"></i><span class="ep-nav-label">Check Status</span>
                </a>

                <div class="ep-nav-group-title">Returning Students</div>
                <a href="<?php echo e(route('enrollment.old-student.login')); ?>" class="ep-nav-link <?php echo e(request()->routeIs('enrollment.old-student.*') ? 'active' : ''); ?>">
                    <i class="fas fa-right-to-bracket"></i><span class="ep-nav-label">Old Student Login</span>
                </a>

                <div class="ep-nav-group-title">Support</div>
                <a href="<?php echo e(route('login')); ?>" class="ep-nav-link">
                    <i class="fas fa-graduation-cap"></i><span class="ep-nav-label">LMS Login</span>
                </a>
            </nav>

            <div class="ep-sidebar-footer">
                <div class="ep-user-card">
                    <div class="ep-user-avatar"><i class="fas fa-school"></i></div>
                    <div class="ep-user-info">
                        <div class="name">PMSI Admissions</div>
                        <div class="role">enrollment@panoramamontessori.edu.ph</div>
                    </div>
                </div>
            </div>
        </aside>

        
        <div class="ep-main">
            <header class="ep-topbar">
                <div class="ep-topbar-left">
                    <button class="ep-menu-toggle" data-ep-toggle-sidebar aria-label="Toggle navigation">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <div class="ep-page-title" style="font-size:1.1rem;margin:0;"><?php echo $__env->yieldContent('title', 'Enrollment Portal'); ?></div>
                    </div>
                </div>
                <div class="ep-topbar-right d-flex align-items-center gap-2">
                    <button type="button" class="ep-dark-toggle" data-ep-toggle-dark aria-label="Toggle dark mode">
                        <i class="fas fa-moon" data-ep-dark-icon></i>
                    </button>
                    <a href="<?php echo e(route('enrollment.portal.create')); ?>" class="ep-btn ep-btn-primary ep-btn-sm d-none d-md-inline-flex">
                        <i class="fas fa-plus"></i> New Application
                    </a>
                </div>
            </header>

            <main class="ep-page-content">
                <div class="container-fluid">
                    <?php if(session('success')): ?>
                        <div class="ep-alert ep-alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-circle-check me-2"></i><?php echo e(session('success')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if(session('error')): ?>
                        <div class="ep-alert ep-alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-circle-exclamation me-2"></i><?php echo e(session('error')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if($errors->any()): ?>
                        <div class="ep-alert ep-alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-triangle-exclamation me-2"></i>
                            <strong>Please fix the following:</strong>
                            <ul class="mb-0 mt-2 ps-3"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            </main>
        </div>
    </div>

    <a href="<?php echo e(route('enrollment.portal.create')); ?>" class="ep-fab d-md-none" aria-label="Apply now">
        <i class="fas fa-plus"></i>
    </a>

    <script src="<?php echo e(URL::to('assets/plugins/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>
    <script src="<?php echo e(URL::to('assets/js/jquery-3.6.0.min.js')); ?>"></script>
    <script src="<?php echo e(URL::to('assets/js/toastr.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/enrollment-portal.js')); ?>"></script>
    <?php echo $__env->yieldContent('scripts'); ?>
    <?php echo $__env->make('partials.toastr-scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</body>
</html>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/layouts/enrollment-portal.blade.php ENDPATH**/ ?>