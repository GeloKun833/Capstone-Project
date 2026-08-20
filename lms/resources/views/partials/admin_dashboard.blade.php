<div class="page-header">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-sub-header">
                <h3 class="page-title">Welcome {{ Session::get('name') }}!</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">{{ Session::get('name') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->role_name === 'Admin')
<div class="row mb-4">
    <div class="col-12">
        <h3 class="page-title text-primary fw-bold">Administrative Reports</h3>
    </div>
</div>
@endif





@if(auth()->user()->role_name === 'Admin')
<div class="row g-4">
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card card-report shadow-hover transition-all">
            <div class="card-body d-flex align-items-center">
                <div class="report-icon bg-primary-soft rounded-circle me-3">
                    <i class="fas fa-users text-primary fs-4"></i>
                </div>
                <div class="report-content">
                    <h6 class="text-muted mb-1">Total Students</h6>
                    <h3 class="text-dark fw-bold mb-0">{{ number_format($admin['totalStudents']) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card card-report shadow-hover transition-all">
            <div class="card-body d-flex align-items-center">
                <div class="report-icon bg-success-soft rounded-circle me-3">
                    <i class="fas fa-chalkboard-teacher text-success fs-4"></i>
                </div>
                <div class="report-content">
                    <h6 class="text-muted mb-1">Total Teachers</h6>
                    <h3 class="text-dark fw-bold mb-0">{{ number_format($admin['totalTeachers']) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card card-report shadow-hover transition-all">
            <div class="card-body d-flex align-items-center">
                <div class="report-icon bg-warning-soft rounded-circle me-3">
                    <i class="fas fa-book text-warning fs-4"></i>
                </div>
                <div class="report-content">
                    <h6 class="text-muted mb-1">Total Subjects</h6>
                    <h3 class="text-dark fw-bold mb-0">{{ number_format($admin['totalSubjects']) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card card-report shadow-hover transition-all">
            <div class="card-body d-flex align-items-center">
                <div class="report-icon bg-info-soft rounded-circle me-3">
                    <i class="fas fa-calendar-check text-info fs-4"></i>
                </div>
                <div class="report-content">
                    <h6 class="text-muted mb-1">Attendance Rate</h6>
                    <h3 class="text-dark fw-bold mb-0">{{ $admin['attendancePercentage'] }}%</h3>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(auth()->user()->role_name === 'Admin')
<div class="row">
    <div class="col-md-12 col-lg-6">

        <div class="card card-chart">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-6">
                        <h5 class="card-title">Students Performance</h5>
                    </div>
                    <div class="col-6">
                        <ul class="chart-list-out">
                            <li><span class="circle-blue"></span>Teacher</li>
                            <li><span class="circle-green"></span>Student</li>
                            <li class="star-menus"><a href="javascript:;"><i
                                        class="fas fa-ellipsis-v"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="apexcharts-area"></div>
            </div>
        </div>

    </div>
    <div class="col-md-12 col-lg-6">

        <div class="card card-chart">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-6">
                        <h5 class="card-title">Number of Students</h5>
                    </div>
                    <div class="col-6">
                        <ul class="chart-list-out">
                            <li><span class="circle-blue"></span>Girls</li>
                            <li><span class="circle-green"></span>Boys</li>
                            <li class="star-menus"><a href="javascript:;"><i
                                        class="fas fa-ellipsis-v"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="bar"></div>
            </div>
        </div>

    </div>
</div>
@endif
@if(auth()->user()->role_name === 'Admin')
<div class="row">
    <div class="col-xl-6 d-flex">

        <div class="card flex-fill student-space comman-shadow">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title">Star Students</h5>
                <ul class="chart-list-out student-ellips">
                    <li class="star-menus"><a href="javascript:;"><i class="fas fa-ellipsis-v"></i></a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table
                        class="table star-student table-hover table-center table-borderless table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th class="text-center">Marks</th>
                                <th class="text-center">Percentage</th>
                                <th class="text-end">Year</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($admin['topStudents'] as $gpaRecord)
                            <tr>
                                <td class="text-nowrap">
                                    <div>{{ $gpaRecord->student->admission_id ?? 'STU' . $gpaRecord->student->id }}</div>
                                </td>   
                                <td class="text-nowrap">
                                    <a href="#">
                                        <img class="rounded-circle" src="{{ URL::to('assets/img/profiles/avatar-01.jpg') }}" width="25" alt="Star Students"> 
                                        {{ $gpaRecord->student->first_name ?? 'Student' }} {{ $gpaRecord->student->last_name ?? '' }}
                                    </a>
                                </td>
                                <td class="text-center">{{ $gpaRecord->total_grade_points ?? 'N/A' }}</td>
                                <td class="text-center">{{ $gpaRecord->gpa ?? 'N/A' }}</td>
                                <td class="text-end">
                                    <div>{{ $gpaRecord->academicYear->name ?? 'N/A' }}</div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    <i class="fas fa-info-circle me-2"></i>No GPA records available
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    <div class="col-xl-6 d-flex">

        <div class="card flex-fill comman-shadow">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title ">Student Activity </h5>
                <ul class="chart-list-out student-ellips">
                    <li class="star-menus"><a href="javascript:;"><i class="fas fa-ellipsis-v"></i></a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="activity-groups">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-info-circle fs-1 mb-3"></i>
                        <h6>No student activities available</h6>
                        <p class="small">Student activities will appear here when available</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endif

@if(auth()->user()->role_name === 'Admin')
<div class="row">
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card flex-fill fb sm-box">
            <div class="social-likes">
                <p>Total Enrollments</p>
                <h6>{{ number_format($admin['totalEnrollments']) }}</h6>
            </div>
            <div class="social-boxs">
                <i class="fas fa-user-graduate text-primary fs-2"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card flex-fill twitter sm-box">
            <div class="social-likes">
                <p>Total Grades</p>
                <h6>{{ number_format($admin['totalGrades']) }}</h6>
            </div>
            <div class="social-boxs">
                <i class="fas fa-clipboard-list text-info fs-2"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card flex-fill insta sm-box">
            <div class="social-likes">
                <p>Total Sections</p>
                <h6>{{ number_format($admin['totalSections']) }}</h6>
            </div>
            <div class="social-boxs">
                <i class="fas fa-layer-group text-warning fs-2"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card flex-fill linkedin sm-box">
            <div class="social-likes">
                <p>Total Announcements</p>
                <h6>{{ number_format($admin['totalAnnouncements']) }}</h6>
            </div>
            <div class="social-boxs">
                <i class="fas fa-bullhorn text-success fs-2"></i>
            </div>
        </div>
    </div>
</div>
@endif
