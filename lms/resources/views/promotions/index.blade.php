@extends('layouts.master')
@section('content')
{{-- message --}}
{!! Toastr::message() !!}

<div class="page-wrapper">
    <div class="content container-fluid">
        
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Student Promotions</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Promotions</li>
                    </ul>
                </div>
                <div class="col-auto text-end float-end ms-auto download-grp">
                    <a href="{{ route('promotions.history') }}" class="btn btn-outline-primary">
                        <i class="fas fa-history"></i> Promotion History
                    </a>
                </div>
            </div>
        </div>

        {{-- Info Card --}}
        <div class="card bg-light-info mb-4">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle fa-3x text-info"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-2">About Student Promotions</h5>
                        <p class="mb-2">Use this feature to promote students from one grade level to the next at the end of the academic year.</p>
                        <ul class="mb-0">
                            <li><strong>Promote:</strong> Advance students to the next grade level</li>
                            <li><strong>Retain:</strong> Keep students in the same grade for another year</li>
                            <li><strong>Graduate:</strong> Mark Grade 10 students as graduated</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Students by Grade Level --}}
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Students by Grade Level</h5>
                    </div>
                    <div class="card-body">
                        @if(count($studentsByGrade) > 0)
                            <div class="row">
                                @foreach($studentsByGrade as $grade => $count)
                                    <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                        <div class="card border-primary">
                                            <div class="card-body text-center">
                                                <h3 class="text-primary mb-2">{{ $count }}</h3>
                                                <p class="mb-0 text-muted">{{ $grade }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-center text-muted">No active students found.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Promotion Form --}}
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0 text-white">
                            <i class="fas fa-graduation-cap"></i> Promote Students
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('promotions.create') }}" method="GET">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">From Grade Level <span class="text-danger">*</span></label>
                                        <select name="from_grade" class="form-control" required>
                                            <option value="">Select Grade Level</option>
                                            @foreach($gradeLevels as $grade)
                                                @if(isset($studentsByGrade[$grade]))
                                                    <option value="{{ $grade }}">{{ $grade }} ({{ $studentsByGrade[$grade] }} students)</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">To Grade Level <span class="text-danger">*</span></label>
                                        <select name="to_grade" class="form-control" required>
                                            <option value="">Select Grade Level</option>
                                            <option value="Kindergarten">Kindergarten</option>
                                            @for($i = 1; $i <= 10; $i++)
                                                <option value="Grade {{ $i }}">Grade {{ $i }}</option>
                                            @endfor
                                            <option value="Graduated">Graduated</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block w-100">
                                            <i class="fas fa-arrow-right"></i> Continue to Student Selection
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Common Promotions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a href="{{ route('promotions.create', ['from_grade' => 'Nursery', 'to_grade' => 'Kindergarten']) }}" class="btn btn-outline-primary btn-block w-100">
                                    <i class="fas fa-arrow-up"></i> Promote Nursery → Kindergarten
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="{{ route('promotions.create', ['from_grade' => 'Kindergarten', 'to_grade' => 'Grade 1']) }}" class="btn btn-outline-primary btn-block w-100">
                                    <i class="fas fa-arrow-up"></i> Promote Kindergarten → Grade 1
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="{{ route('promotions.create', ['from_grade' => 'Grade 10', 'to_grade' => 'Graduated']) }}" class="btn btn-outline-success btn-block w-100">
                                    <i class="fas fa-user-graduate"></i> Graduate Grade 10 Students
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@section('script')
<script>
$(document).ready(function() {
    // Auto-suggest next grade level
    $('select[name="from_grade"]').on('change', function() {
        var fromGrade = $(this).val();
        var toSelect = $('select[name="to_grade"]');
        
        var gradeMap = {
            'Nursery': 'Kindergarten',
            'Kindergarten': 'Grade 1',
            'Grade 1': 'Grade 2',
            'Grade 2': 'Grade 3',
            'Grade 3': 'Grade 4',
            'Grade 4': 'Grade 5',
            'Grade 5': 'Grade 6',
            'Grade 6': 'Grade 7',
            'Grade 7': 'Grade 8',
            'Grade 8': 'Grade 9',
            'Grade 9': 'Grade 10',
            'Grade 10': 'Graduated'
        };
        
        var nextGrade = gradeMap[fromGrade];
        if (nextGrade) {
            toSelect.val(nextGrade);
        }
    });
});
</script>
@endsection
@endsection

