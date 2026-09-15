@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Edit User</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('list/users') }}">Users</a></li>
                            <li class="breadcrumb-item active">Edit User</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('user/update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="form-title"><span>Edit User</span></h5>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Name <span class="login-danger">*</span></label>
                                            <input type="text" class="form-control" name="name" value="{{ old('name', $users->name) }}" pattern="[\p{L}\p{M}\s'\-\.]+" title="Letters only — no emojis">
                                            <input type="hidden" class="form-control" name="user_id" value="{{ $users->user_id }}">
                                            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Email <span class="login-danger">*</span></label>
                                            <input type="email" class="form-control" name="email" value="{{ old('email', $users->email) }}">
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Phone Number <span class="login-danger">*</span></label>
                                            <input type="tel" class="form-control" name="phone_number" value="{{ old('phone_number', $users->phone_number) }}" inputmode="numeric" pattern="[0-9+\-\s()]+" title="Numbers only">
                                            @error('phone_number')<div class="text-danger small">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Date Of Birth</label>
                                            <input type="date" class="form-control js-dob" name="date_of_birth" max="{{ date('Y-m-d') }}" min="1950-01-01" value="{{ old('date_of_birth', $users->date_of_birth ? \Illuminate\Support\Str::of($users->date_of_birth)->substr(0, 10) : '') }}">
                                            @error('date_of_birth')<div class="text-danger small">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Status <span class="login-danger">*</span></label>
                                            @php $currentStatus = old('status', $users->status); @endphp
                                            <select class="form-control" name="status" @if(!empty($isSoleAdmin)) data-sole-admin="1" @endif>
                                                <option value="Active" {{ \App\Models\User::isActiveStatus($currentStatus) ? 'selected' : '' }}>Active</option>
                                                <option value="Inactive" {{ strtolower((string) $currentStatus) === 'inactive' ? 'selected' : '' }} @if(!empty($isSoleAdmin)) disabled @endif>Inactive</option>
                                                <option value="Disable" {{ in_array(strtolower((string) $currentStatus), ['disable', 'disabled'], true) ? 'selected' : '' }} @if(!empty($isSoleAdmin)) disabled @endif>Disable</option>
                                            </select>
                                            @if(!empty($isSoleAdmin))
                                                <small class="text-warning">This is the only active Admin — status/role are locked.</small>
                                                <input type="hidden" name="status" value="{{ $users->status }}">
                                            @endif
                                            @error('status')<div class="text-danger small">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Role Name <span class="login-danger">*</span></label>
                                            @php $currentRole = old('role_name', $users->role_name); @endphp
                                            <select class="form-control" name="role_name" id="role_name" required @if(!empty($isSoleAdmin)) disabled @endif>
                                                @foreach ($role as $name)
                                                    <option value="{{ $name->role_type }}" {{ (string) $currentRole === (string) $name->role_type ? 'selected' : '' }}>{{ $name->role_type }}</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="role_name_current" value="{{ $users->role_name }}">
                                            @if(!empty($isSoleAdmin))
                                                <input type="hidden" name="role_name" value="Admin">
                                            @endif
                                            @error('role_name')<div class="text-danger small">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Profile Image</label>
                                            <input type="file" class="form-control @error('avatar') is-invalid @enderror"
                                                   name="avatar" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                            <small class="form-text text-muted">Optional. JPG, PNG, GIF, or WEBP. Max 2MB.</small>
                                            @error('avatar')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            @if(!empty($users->avatar))
                                                <div class="user-img mt-2">
                                                    <img class="rounded-circle" src="{{ \App\Support\AvatarUploader::url($users->avatar) }}"
                                                         alt="Current profile" style="width:64px;height:64px;object-fit:cover;">
                                                </div>
                                            @endif
                                        </div>
                                        <input type="hidden" name="hidden_avatar" value="{{ $users->avatar }}">
                                    </div>

                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Position <span class="login-danger">*</span></label>
                                            <input type="text" class="form-control" name="position" value="{{ old('position', $users->position) }}">
                                            @error('position')<div class="text-danger small">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Department <span class="login-danger">*</span></label>
                                            <input type="text" class="form-control" name="department" value="{{ old('department', $users->department) }}">
                                            @error('department')<div class="text-danger small">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Updated Date</label>
                                            <input type="text" class="form-control" name="updated_at" value="{{ $users->updated_at }}" readonly>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <h5 class="form-title"><span>Reset Password (Admin)</span></h5>
                                        <p class="text-muted small">Optional. Use this if the user forgot their password and cannot use email reset. Leave blank to keep the current password.</p>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group local-forms">
                                            <label>New Password</label>
                                            <input type="password" class="form-control @error('new_password') is-invalid @enderror"
                                                   name="new_password" autocomplete="new-password" placeholder="Leave blank to keep current">
                                            @error('new_password')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group local-forms">
                                            <label>Confirm New Password</label>
                                            <input type="password" class="form-control" name="new_password_confirmation" autocomplete="new-password" placeholder="Confirm new password">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="student-submit">
                                            <button type="submit" class="btn btn-primary">Update</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
