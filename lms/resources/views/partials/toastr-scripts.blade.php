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
        @if(session()->has('success'))
            toastr.success(@json(session('success')), 'Success');
        @endif
        @if(session()->has('error'))
            toastr.error(@json(session('error')), 'Error');
        @endif
        @if(session()->has('warning'))
            toastr.warning(@json(session('warning')), 'Warning');
        @endif
        @if(session()->has('info'))
            toastr.info(@json(session('info')), 'Info');
        @endif
        @if(session()->has('status') && !session()->has('success'))
            toastr.success(@json(session('status')), 'Success');
        @endif
        @if(isset($errors) && $errors->any() && !session()->has('error'))
            toastr.error(@json($errors->first()), 'Validation');
        @endif
    })();
</script>
