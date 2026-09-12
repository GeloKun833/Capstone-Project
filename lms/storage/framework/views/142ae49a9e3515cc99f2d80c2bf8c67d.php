<?php $__env->startSection('content'); ?>

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Notifications</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Notifications</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">All Notifications</h4>
                        </div>
                        <div class="card-body">
                            <?php if(isset($notifications) && $notifications->count() > 0): ?>
                                <div class="notification-list">
                                    <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="notification-item <?php echo e($notification->read_at ? '' : 'unread'); ?>">
                                            <div class="notification-content">
                                                <div class="notification-icon">
                                                    <i class="fas fa-info-circle text-primary"></i>
                                                </div>
                                                <div class="notification-details">
                                                    <h6><?php echo e($notification->data['title'] ?? 'Notification'); ?></h6>
                                                    <p><?php echo e($notification->data['message'] ?? \Illuminate\Support\Str::limit(strip_tags($notification->data['content'] ?? 'You have a new notification'), 160)); ?></p>
                                                    <small class="text-muted"><?php echo e($notification->created_at->diffForHumans()); ?></small>
                                                </div>
                                                <?php if(!$notification->read_at): ?>
                                                    <div class="notification-actions">
                                                        <button class="btn btn-sm btn-primary" onclick="markAsRead('<?php echo e($notification->id); ?>')">
                                                            Mark as Read
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>

                                <div class="d-flex justify-content-center mt-4">
                                    <?php echo e($notifications->links()); ?>

                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                                    <h5>No notifications</h5>
                                    <p class="text-muted">You don't have any notifications yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .notification-item {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 15px;
        padding: 15px;
        transition: all 0.3s ease;
    }

    .notification-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .notification-item.unread {
        background-color: #f8f9fa;
        border-left: 4px solid #007bff;
    }

    .notification-content {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .notification-icon {
        font-size: 1.5rem;
        width: 40px;
        text-align: center;
    }

    .notification-details {
        flex: 1;
    }

    .notification-details h6 {
        margin-bottom: 5px;
        color: #333;
    }

    .notification-details p {
        margin-bottom: 5px;
        color: #666;
    }

    .notification-actions {
        margin-left: auto;
    }
    </style>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\notifications\index.blade.php ENDPATH**/ ?>