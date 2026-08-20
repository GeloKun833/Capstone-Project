{{-- Registrar Dashboard --}}
<div class="row">
    <div class="col-12">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Registrar Dashboard</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item active">Welcome, {{ $user->name }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Statistics Cards --}}
<div class="row">
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-primary">
                        <i class="fas fa-file-alt"></i>
                    </span>
                    <div class="dash-widget-info">
                        <h3>{{ $registrar['totalApplications'] ?? 0 }}</h3>
                        <h6 class="text-muted">Total Applications</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-warning">
                        <i class="fas fa-clock"></i>
                    </span>
                    <div class="dash-widget-info">
                        <h3>{{ $registrar['pendingApplications'] ?? 0 }}</h3>
                        <h6 class="text-muted">Pending Review</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-success">
                        <i class="fas fa-check-circle"></i>
                    </span>
                    <div class="dash-widget-info">
                        <h3>{{ $registrar['approvedApplications'] ?? 0 }}</h3>
                        <h6 class="text-muted">Approved</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-danger">
                        <i class="fas fa-times-circle"></i>
                    </span>
                    <div class="dash-widget-info">
                        <h3>{{ $registrar['rejectedApplications'] ?? 0 }}</h3>
                        <h6 class="text-muted">Rejected</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Additional Status Cards --}}
<div class="row">
    <div class="col-xl-6 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-info">
                        <i class="fas fa-search"></i>
                    </span>
                    <div class="dash-widget-info">
                        <h3>{{ $registrar['underReviewApplications'] ?? 0 }}</h3>
                        <h6 class="text-muted">Under Review</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-6 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-secondary">
                        <i class="fas fa-file-upload"></i>
                    </span>
                    <div class="dash-widget-info">
                        <h3>{{ $registrar['needsDocumentsApplications'] ?? 0 }}</h3>
                        <h6 class="text-muted">Needs Documents</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Actions --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{ route('enrollment.registrar.index') }}" class="btn btn-primary btn-block mb-2">
                            <i class="fas fa-list me-2"></i>View Applications
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('enrollment.registrar.statistics') }}" class="btn btn-info btn-block mb-2">
                            <i class="fas fa-chart-bar me-2"></i>Statistics
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('enrollment.portal.index') }}" target="_blank" class="btn btn-success btn-block mb-2">
                            <i class="fas fa-external-link-alt me-2"></i>Portal View
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('list/users') }}" class="btn btn-warning btn-block mb-2">
                            <i class="fas fa-users me-2"></i>Manage Users
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Applications --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Recent Applications</h5>
                <a href="{{ route('enrollment.registrar.index') }}" class="btn btn-primary btn-sm float-end">View All</a>
            </div>
            <div class="card-body">
                @if(isset($registrar['recentApplications']) && $registrar['recentApplications']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Application #</th>
                                    <th>Student Name</th>
                                    <th>Grade Level</th>
                                    <th>Status</th>
                                    <th>Applied Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($registrar['recentApplications'] as $application)
                                    <tr>
                                        <td>{{ $application->application_number }}</td>
                                        <td>{{ $application->first_name }} {{ $application->last_name }}</td>
                                        <td>{{ $application->grade_level_applying_for }}</td>
                                        <td>
                                            @switch($application->status)
                                                @case('pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                    @break
                                                @case('approved')
                                                    <span class="badge bg-success">Approved</span>
                                                    @break
                                                @case('rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                    @break
                                                @case('under_review')
                                                    <span class="badge bg-info">Under Review</span>
                                                    @break
                                                @case('needs_documents')
                                                    <span class="badge bg-secondary">Needs Documents</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-light text-dark">{{ ucfirst($application->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $application->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('enrollment.registrar.show', $application->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-file-alt text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No Applications Yet</h5>
                        <p class="text-muted">New enrollment applications will appear here.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Monthly Statistics --}}
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">This Month</h5>
            </div>
            <div class="card-body text-center">
                <h2 class="text-primary">{{ $registrar['thisMonthApplications'] ?? 0 }}</h2>
                <p class="text-muted">Applications Received</p>
                @if(isset($registrar['growthPercentage']) && $registrar['growthPercentage'] != 0)
                    <small class="text-{{ $registrar['growthPercentage'] > 0 ? 'success' : 'danger' }}">
                        <i class="fas fa-arrow-{{ $registrar['growthPercentage'] > 0 ? 'up' : 'down' }}"></i>
                        {{ abs($registrar['growthPercentage']) }}% from last month
                    </small>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Applications by Grade Level</h5>
            </div>
            <div class="card-body">
                @if(isset($registrar['applicationsByGrade']) && $registrar['applicationsByGrade']->count() > 0)
                    @foreach($registrar['applicationsByGrade'] as $grade)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>{{ $grade->grade_level_applying_for }}</span>
                            <span class="badge bg-primary">{{ $grade->count }}</span>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center">No grade level data available</p>
                @endif
            </div>
        </div>
    </div>
</div>
