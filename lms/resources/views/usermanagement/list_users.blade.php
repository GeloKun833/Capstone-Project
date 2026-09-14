@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid dir-page">

        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">All Users</h3>
                    <p class="dir-subtitle">Admin, teachers, students, parents, and staff in one directory.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">All Users</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="dir-card dir-filters">
            <div class="row g-2 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">User ID</label>
                    <input type="text" class="form-control" id="search_id" placeholder="Search by ID">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" id="search_name" placeholder="Search by name">
                </div>
                <div class="col-lg-4 col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" id="search_phone" placeholder="Search by phone">
                </div>
                <div class="col-lg-2 col-md-6 pb-3">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary dir-btn flex-fill" id="search_btn">Search</button>
                        <button type="button" class="btn btn-outline-secondary dir-btn" id="clear_btn">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="dir-card">
            <div class="dir-toolbar">
                <div>
                    <h5 class="dir-toolbar-title">User accounts</h5>
                    <span class="dir-count mt-1">Live directory</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table dir-table mb-0" id="UsersList">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Date joined</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade dir-modal" id="delete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Delete user?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted mb-0">This will remove the user account. This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <form action="{{ route('user/delete') }}" method="POST" class="d-flex gap-2 w-100 justify-content-end">
                    @csrf
                    <input type="hidden" name="user_id" class="e_user_id" value="">
                    <input type="hidden" name="avatar" class="e_avatar" value="">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@section('script')
<script>
    $(document).on('click', '.delete', function () {
        $('.e_user_id').val($(this).data('user_id'));
        $('.e_avatar').val($(this).data('avatar'));
    });

    $(document).ready(function() {
        var table = $('#UsersList').DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            searching: false,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            deferRender: true,
            autoWidth: false,
            language: {
                lengthMenu: 'Show _MENU_ users',
                info: 'Showing _START_–_END_ of _TOTAL_ users',
                infoEmpty: 'No users found',
                emptyTable: 'No users found',
                processing: 'Loading users…'
            },
            dom: '<"px-3 pt-2"l>rt<"d-flex justify-content-between align-items-center flex-wrap px-3 pb-3"ip>',
            ajax: {
                url: "{{ route('get-users-data') }}",
                data: function(d) {
                    d.search_id = $('#search_id').val();
                    d.search_name = $('#search_name').val();
                    d.search_phone = $('#search_phone').val();
                },
                error: function(xhr) {
                    console.error('Users DataTable error', xhr.responseText);
                }
            },
            columns: [
                { data: 'user_id', name: 'user_id' },
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'phone_number', name: 'phone_number' },
                { data: 'join_date', name: 'join_date' },
                { data: 'position', name: 'position' },
                { data: 'status', name: 'status' },
                { data: 'modify', name: 'modify', orderable: false, searchable: false, className: 'text-end' }
            ]
        });

        $('#search_btn').click(function() {
            table.draw();
        });

        $('#clear_btn').click(function() {
            $('#search_id').val('');
            $('#search_name').val('');
            $('#search_phone').val('');
            table.draw();
        });

        $('#search_id, #search_name, #search_phone').keypress(function(e) {
            if (e.which == 13) {
                table.draw();
            }
        });
    });
</script>
@endsection

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914b">
@endpush
