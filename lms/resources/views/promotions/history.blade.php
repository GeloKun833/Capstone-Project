@extends('layouts.master')
@section('content')
{{-- message --}}
{!! Toastr::message() !!}

<div class="page-wrapper">
    <div class="content container-fluid">
        
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Promotion History</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('promotions.index') }}">Promotions</a></li>
                        <li class="breadcrumb-item active">History</li>
                    </ul>
                </div>
                <div class="col-auto text-end float-end ms-auto download-grp">
                    <a href="{{ route('promotions.index') }}" class="btn btn-primary">
                        <i class="fas fa-graduation-cap"></i> New Promotion
                    </a>
                </div>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Promotions</h6>
                                <h3 class="mb-0">{{ number_format($stats['total']) }}</h3>
                            </div>
                            <div>
                                <i class="fas fa-chart-line fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Promoted</h6>
                                <h3 class="mb-0 text-success">{{ number_format($stats['promoted']) }}</h3>
                            </div>
                            <div>
                                <i class="fas fa-arrow-up fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Retained</h6>
                                <h3 class="mb-0 text-warning">{{ number_format($stats['retained']) }}</h3>
                            </div>
                            <div>
                                <i class="fas fa-redo fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Graduated</h6>
                                <h3 class="mb-0 text-info">{{ number_format($stats['graduated']) }}</h3>
                            </div>
                            <div>
                                <i class="fas fa-user-graduate fa-2x text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('promotions.history') }}">
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" placeholder="Search student name..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <div class="form-group">
                                <select name="academic_year_id" class="form-control">
                                    <option value="">All Academic Years</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                            {{ $year->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <div class="form-group">
                                <select name="from_grade" class="form-control">
                                    <option value="">All Grade Levels</option>
                                    @foreach($gradeLevels as $grade)
                                        <option value="{{ $grade }}" {{ request('from_grade') == $grade ? 'selected' : '' }}>
                                            {{ $grade }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <div class="form-group">
                                <select name="promotion_status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="promoted" {{ request('promotion_status') == 'promoted' ? 'selected' : '' }}>Promoted</option>
                                    <option value="retained" {{ request('promotion_status') == 'retained' ? 'selected' : '' }}>Retained</option>
                                    <option value="graduated" {{ request('promotion_status') == 'graduated' ? 'selected' : '' }}>Graduated</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-1 col-md-3">
                            <button type="submit" class="btn btn-primary btn-block w-100">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                        <div class="col-lg-1 col-md-3">
                            <a href="{{ route('promotions.history') }}" class="btn btn-secondary btn-block w-100">
                                <i class="fas fa-redo"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Promotion Records --}}
        <div class="card">
            <div class="card-body">
                @if($promotions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Student</th>
                                    <th>From Grade</th>
                                    <th>To Grade</th>
                                    <th>Academic Year</th>
                                    <th>GPA</th>
                                    <th>Status</th>
                                    <th>Promoted By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($promotions as $promotion)
                                    <tr>
                                        <td>{{ $promotion->promotion_date->format('M d, Y') }}</td>
                                        <td>
                                            <strong>{{ $promotion->student->first_name }} {{ $promotion->student->last_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $promotion->student->admission_id ?? 'STU-' . $promotion->student->id }}</small>
                                        </td>
                                        <td>{{ $promotion->from_year_level }}</td>
                                        <td>{{ $promotion->to_year_level }}</td>
                                        <td>
                                            {{ $promotion->fromAcademicYear->name ?? 'N/A' }} 
                                            <i class="fas fa-arrow-right"></i> 
                                            {{ $promotion->toAcademicYear->name ?? 'N/A' }}
                                        </td>
                                        <td>
                                            @if($promotion->final_gpa)
                                                <span class="badge bg-{{ $promotion->final_gpa >= 3.0 ? 'success' : ($promotion->final_gpa >= 2.0 ? 'warning' : 'danger') }}">
                                                    {{ number_format($promotion->final_gpa, 2) }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $promotion->statusBadge }}">
                                                {{ ucfirst($promotion->promotion_status) }}
                                            </span>
                                        </td>
                                        <td>{{ $promotion->promoter->name ?? 'Unknown' }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @if($promotion->remarks)
                                                        <li>
                                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#remarksModal{{ $promotion->id }}">
                                                                <i class="fas fa-comment"></i> View Remarks
                                                            </a>
                                                        </li>
                                                    @endif
                                                    <li>
                                                        <form action="{{ route('promotions.destroy', $promotion->id) }}" method="POST" 
                                                              onsubmit="return confirm('Are you sure? This will rollback the student to their previous grade level.')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="fas fa-undo"></i> Rollback
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>

                                            {{-- Remarks Modal --}}
                                            @if($promotion->remarks)
                                                <div class="modal fade" id="remarksModal{{ $promotion->id }}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Promotion Remarks</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><strong>Student:</strong> {{ $promotion->student->first_name }} {{ $promotion->student->last_name }}</p>
                                                                <p><strong>Promotion:</strong> {{ $promotion->from_year_level }} → {{ $promotion->to_year_level }}</p>
                                                                <hr>
                                                                <p>{{ $promotion->remarks }}</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($promotions->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $promotions->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5>No Promotion Records Found</h5>
                        <p class="text-muted">No promotion history available with the current filters.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

