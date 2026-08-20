@extends('layouts.master')
@section('content')


<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Create New User</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('enrollments.index') }}">Enrollments</a></li>
                        <li class="breadcrumb-item active">Create User</li>
            </ul>
        </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">User Information</h5>
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Note:</strong> Student and Parent accounts are automatically created through the enrollment portal. 
                            Use this form only for creating Teacher and Registrar accounts.
                        </p>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('enrollments.store') }}" method="POST">
        @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               name="email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Role <span class="text-danger">*</span></label>
                                        <select class="form-control @error('role_name') is-invalid @enderror" 
                                                name="role_name" id="role_name" required>
                                            <option value="">Select Role</option>
                                            <option value="Teacher" {{ old('role_name') == 'Teacher' ? 'selected' : '' }}>Teacher</option>
                                            <option value="Registrar" {{ old('role_name') == 'Registrar' ? 'selected' : '' }}>Registrar</option>
                                        </select>
                                        @error('role_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        <input type="text" class="form-control @error('phone_number') is-invalid @enderror" 
                                               name="phone_number" value="{{ old('phone_number') }}">
                                        @error('phone_number')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                               name="password" required>
                                        @error('password')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                               name="password_confirmation" required>
                                        @error('password_confirmation')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Department</label>
                                        <input type="text" class="form-control @error('department') is-invalid @enderror" 
                                               name="department" value="{{ old('department') }}">
                                        @error('department')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Position</label>
                                        <input type="text" class="form-control @error('position') is-invalid @enderror" 
                                               name="position" value="{{ old('position') }}">
                                        @error('position')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date of Birth</label>
                                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                               name="date_of_birth" value="{{ old('date_of_birth') }}">
                                        @error('date_of_birth')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>


                            {{-- Teacher-specific fields --}}
                            <div id="teacher-fields" style="display: none;">
                                <hr>
                                <h6 class="text-primary">Teacher Information</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Teacher ID</label>
                                            <input type="text" class="form-control @error('teacher_id') is-invalid @enderror" 
                                                   name="teacher_id" value="{{ old('teacher_id') }}">
                                            @error('teacher_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
            </div>
            <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Specialization</label>
                                            <input type="text" class="form-control @error('specialization') is-invalid @enderror" 
                                                   name="specialization" value="{{ old('specialization') }}">
                                            @error('specialization')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Create User</button>
            <a href="{{ route('enrollments.index') }}" class="btn btn-secondary">Cancel</a>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role_name');
    const teacherFields = document.getElementById('teacher-fields');

    function toggleFields() {
        const selectedRole = roleSelect.value;
        
        // Hide all role-specific fields
        teacherFields.style.display = 'none';
        
        // Show fields based on selected role
        if (selectedRole === 'Teacher') {
            teacherFields.style.display = 'block';
        }
    }
    
    roleSelect.addEventListener('change', toggleFields);
    
    // Initialize on page load
    toggleFields();
});
</script>
@endpush 