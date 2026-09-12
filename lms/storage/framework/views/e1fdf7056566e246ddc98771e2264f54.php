<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Enrollment Management'); ?> — PMSI</title>
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
        <aside class="ep-sidebar" id="epSidebar">
            <div class="ep-brand">
                <img src="<?php echo e(URL::to('assets/img/Logo.jpg')); ?>" alt="Logo">
                <div class="ep-brand-text">
                    <h1>Enrollment Admin</h1>
                    <span>Registrar Portal</span>
                </div>
            </div>
            <nav class="ep-nav">
                <div class="ep-nav-group-title">Overview</div>
                <a href="<?php echo e(route('dashboard')); ?>" class="ep-nav-link"><i class="fas fa-gauge-high"></i><span class="ep-nav-label">Main Dashboard</span></a>
                <a href="<?php echo e(route('enrollment.registrar.index')); ?>" class="ep-nav-link <?php echo e(request()->routeIs('enrollment.registrar.index') ? 'active' : ''); ?>"><i class="fas fa-inbox"></i><span class="ep-nav-label">Applications</span></a>
                <a href="<?php echo e(route('enrollment.registrar.statistics')); ?>" class="ep-nav-link <?php echo e(request()->routeIs('enrollment.registrar.statistics') ? 'active' : ''); ?>"><i class="fas fa-chart-pie"></i><span class="ep-nav-label">Analytics</span></a>
                <a href="<?php echo e(route('enrollment.registrar.archive')); ?>" class="ep-nav-link <?php echo e(request()->routeIs('enrollment.registrar.archive') ? 'active' : ''); ?>"><i class="fas fa-box-archive"></i><span class="ep-nav-label">Archive</span></a>
                <div class="ep-nav-group-title">Portal</div>
                <a href="<?php echo e(route('enrollment.portal.index')); ?>" target="_blank" class="ep-nav-link"><i class="fas fa-external-link"></i><span class="ep-nav-label">Public Portal</span></a>
            </nav>
            <div class="ep-sidebar-footer">
                <div class="ep-user-card">
                    <div class="ep-user-avatar"><?php echo e(strtoupper(substr(auth()->user()->name ?? 'R', 0, 1))); ?></div>
                    <div class="ep-user-info">
                        <div class="name"><?php echo e(auth()->user()->name ?? 'Registrar'); ?></div>
                        <div class="role"><?php echo e(auth()->user()->role_name ?? 'Admin'); ?></div>
                    </div>
                </div>
                <a href="<?php echo e(route('logout')); ?>" class="ep-nav-link mt-2" style="color:#FCA5A5;"><i class="fas fa-right-from-bracket"></i><span class="ep-nav-label">Logout</span></a>
            </div>
        </aside>
        <div class="ep-main">
            <header class="ep-topbar">
                <div class="ep-topbar-left">
                    <button class="ep-menu-toggle" data-ep-toggle-sidebar><i class="fas fa-bars"></i></button>
                    <div class="ep-page-title" style="font-size:1.1rem;margin:0;"><?php echo $__env->yieldContent('title'); ?></div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="ep-dark-toggle" data-ep-toggle-dark aria-label="Toggle dark mode">
                        <i class="fas fa-moon" data-ep-dark-icon></i>
                    </button>
                    <?php echo $__env->yieldContent('topbar-actions'); ?>
                </div>
            </header>
            <main class="ep-page-content">
                <div class="container-fluid">
                    <?php if(session('success')): ?><div class="ep-alert ep-alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
                    <?php if(session('error')): ?><div class="ep-alert ep-alert-danger"><?php echo e(session('error')); ?></div><?php endif; ?>
                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            </main>
        </div>
    </div>
    <script src="<?php echo e(URL::to('assets/plugins/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>
    <script src="<?php echo e(URL::to('assets/js/jquery-3.6.0.min.js')); ?>"></script>
    <script src="<?php echo e(URL::to('assets/js/toastr.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/enrollment-portal.js')); ?>"></script>
    <?php echo $__env->yieldContent('scripts'); ?>
    <?php echo $__env->make('partials.toastr-scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</body>
</html>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/layouts/enrollment-registrar.blade.php ENDPATH**/ ?>