@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Add Academic Year</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('academic_years.index') }}">Academic Years</a></li>
                            <li class="breadcrumb-item active">Add Academic Year</li>
                        </ul>
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Academic Year Information</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="{{ route('academic_years.index') }}" class="btn btn-outline-secondary me-2">
                                            <i class="fas fa-arrow-left"></i> Back to List
                                        </a>
                                        <button type="submit" form="academicYearForm" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Create Academic Year
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('academic_years.store') }}" method="POST" id="academicYearForm">
                                @csrf
                                
                                <!-- Academic Year Details -->
                                <div class="student-group-form">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Academic Year Name</label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g., 2024-2025" required>
                                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Academic Year Code</label>
                                                <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" placeholder="e.g., AY2024-25">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Start Date</label>
                                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">End Date</label>
                                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                                                @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Description</label>
                                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Optional description for this academic year">{{ old('description') }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Status</label>
                                                <select class="form-control" id="is_active" name="is_active">
                                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Academic Year Preview -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">
                                                    <i class="fas fa-eye me-2"></i>Academic Year Preview
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Duration:</label>
                                                            <span class="info-value" id="durationPreview">-</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Total Days:</label>
                                                            <span class="info-value" id="totalDaysPreview">-</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Status:</label>
                                                            <span class="info-value" id="statusPreview">-</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="info-item">
                                                            <label class="info-label">Academic Period:</label>
                                                            <span class="info-value" id="periodPreview">-</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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

@push('styles')
<style>
/* Admin-style form controls */
.student-group-form {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 31px 3px rgba(44,50,63,.02);
    margin-bottom: 20px;
}

.student-group-form .form-group {
    margin-bottom: 0;
}

.student-group-form .form-control {
    border: 1px solid #ddd;
    border-radius: 5px;
    height: 45px;
    padding: 10px 15px;
    font-size: 15px;
}

.student-group-form .form-control:focus {
    border-color: #3d5ee1;
    box-shadow: none;
    outline: 0;
}

.student-group-form .form-label {
    font-weight: 600;
    color: #2c323f;
    margin-bottom: 8px;
}

.student-group-form textarea.form-control {
    height: auto;
    min-height: 80px;
}

/* Card styling */
.card-table {
    border: 0;
    border-radius: 10px;
    box-shadow: 0 0 31px 3px rgba(44,50,63,.02);
    margin-bottom: 1.875rem;
}

.card-table .card-body {
    padding: 1.5rem;
}

.card {
    border: 0;
    border-radius: 10px;
    box-shadow: 0 0 31px 3px rgba(44,50,63,.02);
    margin-bottom: 1.875rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
    border-radius: 10px 10px 0 0;
}

.card-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c323f;
    margin-bottom: 0;
}

.card-body {
    padding: 1.5rem;
}

/* Info items */
.info-item {
    margin-bottom: 15px;
}

.info-label {
    display: block;
    font-weight: 600;
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 5px;
}

.info-value {
    display: block;
    font-weight: 500;
    color: #2c323f;
    font-size: 16px;
}

/* Buttons */
.btn {
    border-radius: 5px;
    font-weight: 600;
    transition: all .4s ease;
}

.btn-primary {
    background-color: #3d5ee1;
    border: 1px solid #3d5ee1;
}

.btn-primary:hover {
    background-color: #18aefa;
    border: 1px solid #18aefa;
}

.btn-outline-secondary {
    color: #6c757d;
    border-color: #6c757d;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
    color: #fff;
}

/* Page header */
.page-header {
    margin-bottom: 1.875rem;
}

.page-header .breadcrumb {
    background-color: transparent;
    color: #6c757d;
    font-size: 1rem;
    font-weight: 500;
    margin-bottom: 0;
    padding: 0;
    margin-left: auto;
}

.page-header .breadcrumb a {
    color: #333;
}

.page-title {
    font-size: 22px;
    font-weight: 500;
    color: #2c323f;
    margin-bottom: 5px;
}

/* Download group */
.download-grp {
    display: flex;
    align-items: center;
}

/* Alert styling */
.alert {
    border-radius: 5px;
    border: none;
    margin-bottom: 1.5rem;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}

/* Responsive */
@media (max-width: 768px) {
    .student-group-form {
        padding: 15px;
    }
    
    .card-table .card-body {
        padding: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .info-item {
        margin-bottom: 10px;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-generate code from name
    $('#name').on('input', function() {
        const name = $(this).val();
        if (name && !$('#code').val()) {
            // Extract year pattern and create code
            const yearMatch = name.match(/(\d{4})/g);
            if (yearMatch && yearMatch.length >= 2) {
                const code = `AY${yearMatch[0]}-${yearMatch[1].substring(2)}`;
                $('#code').val(code);
            }
        }
    });
    
    // Update preview on form changes
    function updatePreview() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        const isActive = $('#is_active').val();
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const diffMonths = Math.ceil(diffDays / 30);
            
            $('#durationPreview').text(`${diffMonths} months (${diffDays} days)`);
            $('#totalDaysPreview').text(diffDays);
            $('#periodPreview').text(`${startDate} to ${endDate}`);
        } else {
            $('#durationPreview').text('-');
            $('#totalDaysPreview').text('-');
            $('#periodPreview').text('-');
        }
        
        $('#statusPreview').text(isActive === '1' ? 'Active' : 'Inactive');
    }
    
    // Bind preview updates to form changes
    $('#start_date, #end_date, #is_active').on('change', updatePreview);
    $('#name').on('input', updatePreview);
    
    // Form validation
    $('#academicYearForm').on('submit', function(e) {
        const name = $('#name').val();
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (!name || !startDate || !endDate) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }
        
        if (startDate >= endDate) {
            e.preventDefault();
            alert('End date must be after start date.');
            return false;
        }
        
        // Check for overlapping academic years
        const start = new Date(startDate);
        const end = new Date(endDate);
        
        // This would typically be checked server-side, but we can do basic validation here
        if (end.getFullYear() - start.getFullYear() > 2) {
            if (!confirm('This academic year spans more than 2 years. Are you sure this is correct?')) {
                e.preventDefault();
                return false;
            }
        }
    });
    
    // Auto-set end date to one year after start date
    $('#start_date').on('change', function() {
        const startDate = $(this).val();
        if (startDate && !$('#end_date').val()) {
            const start = new Date(startDate);
            const end = new Date(start);
            end.setFullYear(end.getFullYear() + 1);
            end.setDate(end.getDate() - 1); // Last day of previous year
            
            const endDateStr = end.toISOString().split('T')[0];
            $('#end_date').val(endDateStr);
        }
    });
    
    // Initialize preview
    updatePreview();
});
</script>
@endpush

@endsection 