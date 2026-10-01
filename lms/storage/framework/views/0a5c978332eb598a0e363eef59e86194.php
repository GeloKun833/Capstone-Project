
<script>
    if (typeof toastr !== 'undefined') {
        toastr.options = Object.assign({
            closeButton: true,
            progressBar: true,
            newestOnTop: true,
            preventDuplicates: true,
            positionClass: 'toast-top-right',
            timeOut: 5000,
            extendedTimeOut: 2000
        }, toastr.options || {});
    }
</script>
<?php echo Toastr::message(); ?>

<script>
    (function () {
        if (typeof toastr === 'undefined') {
            return;
        }
        <?php
            $flashKeys = array_merge(
                (array) session()->get('_flash.old', []),
                (array) session()->get('_flash.new', [])
            );
            $accountStatuses = ['active', 'inactive', 'disable', 'disabled', 'pending', 'blocked'];
            $statusToast = session('status');
            $showStatusToast = in_array('status', $flashKeys, true)
                && filled($statusToast)
                && ! in_array(strtolower(trim((string) $statusToast)), $accountStatuses, true)
                && ! in_array('success', $flashKeys, true);
        ?>
        <?php if(in_array('success', $flashKeys, true)): ?>
            toastr.success(<?php echo json_encode(session('success'), 15, 512) ?>, 'Success');
        <?php endif; ?>
        <?php if(in_array('error', $flashKeys, true)): ?>
            toastr.error(<?php echo json_encode(session('error'), 15, 512) ?>, 'Error');
        <?php endif; ?>
        <?php if(in_array('warning', $flashKeys, true)): ?>
            toastr.warning(<?php echo json_encode(session('warning'), 15, 512) ?>, 'Warning');
        <?php endif; ?>
        <?php if(in_array('info', $flashKeys, true)): ?>
            toastr.info(<?php echo json_encode(session('info'), 15, 512) ?>, 'Info');
        <?php endif; ?>
        <?php if($showStatusToast): ?>
            toastr.success(<?php echo json_encode($statusToast, 15, 512) ?>, 'Success');
        <?php endif; ?>
        <?php if(isset($errors) && $errors->any() && ! in_array('error', $flashKeys, true)): ?>
            toastr.error(<?php echo json_encode($errors->first(), 15, 512) ?>, 'Validation');
        <?php endif; ?>
    })();
</script>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\partials\toastr-scripts.blade.php ENDPATH**/ ?>