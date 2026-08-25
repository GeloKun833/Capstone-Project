{{--
    Toastr must run AFTER jquery + toastr.js.
    The package emits toastr.success(...) immediately; placing it in the page body
    (before scripts) causes a JS error and silently drops CRUD feedback.
--}}
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
{!! Toastr::message() !!}
<script>
    (function () {
        if (typeof toastr === 'undefined') {
            return;
        }
        @php
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
        @endphp
        @if(in_array('success', $flashKeys, true))
            toastr.success(@json(session('success')), 'Success');
        @endif
        @if(in_array('error', $flashKeys, true))
            toastr.error(@json(session('error')), 'Error');
        @endif
        @if(in_array('warning', $flashKeys, true))
            toastr.warning(@json(session('warning')), 'Warning');
        @endif
        @if(in_array('info', $flashKeys, true))
            toastr.info(@json(session('info')), 'Info');
        @endif
        @if($showStatusToast)
            toastr.success(@json($statusToast), 'Success');
        @endif
        @if(isset($errors) && $errors->any() && ! in_array('error', $flashKeys, true))
            toastr.error(@json($errors->first()), 'Validation');
        @endif
    })();
</script>
