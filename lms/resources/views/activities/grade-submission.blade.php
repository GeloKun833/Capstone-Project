@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Grade Submission</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.index') }}">Lesson Planner</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.show', $lesson) }}">{{ $lesson->title }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.activities.index', $lesson) }}">Activities</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.activities.submissions', [$lesson, $activity]) }}">Submissions</a></li>
                            <li class="breadcrumb-item active">Grade: {{ $submission->student->first_name }} {{ $submission->student->last_name }}</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto download-grp">
                        <a href="{{ route('lessons.activities.submissions', [$lesson, $activity]) }}" class="btn btn-outline-primary me-2">
                            <i class="fas fa-arrow-left"></i> Back to Submissions
                        </a>
                        <button type="button" class="btn btn-success" onclick="saveGrade()">
                            <i class="fas fa-save"></i> Save Grade
                        </button>
                    </div>
                </div>
            </div>

            <!-- Student and Activity Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Student Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> {{ $submission->student->first_name }} {{ $submission->student->last_name }}</p>
                                    <p><strong>Email:</strong> {{ $submission->student->email }}</p>
                                    <p><strong>Submitted:</strong> {{ $submission->created_at->format('M d, Y H:i') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> 
                                        @if($submission->status === 'submitted')
                                            <span class="badge bg-warning">Submitted</span>
                                        @elseif($submission->status === 'graded')
                                            <span class="badge bg-success">Graded</span>
                                        @endif
                                    </p>
                                    @if($submission->created_at->gt($activity->due_date))
                                        <p><strong>Late:</strong> <span class="badge bg-danger">Late Submission</span></p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Activity Information</h5>
                            <p><strong>Activity:</strong> {{ $activity->title }}</p>
                            <p><strong>Due Date:</strong> {{ $activity->due_date->format('M d, Y') }}</p>
                            <p><strong>Instructions:</strong> {{ Str::limit($activity->instructions, 100) }}</p>
                            @if($submission->file_path)
                                <a href="{{ asset('storage/' . $submission->file_path) }}" target="_blank" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Download Submission
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rubric Grading Form -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Rubric Grading</h5>
                            
                            @if($rubrics->count() > 0)
                                <form id="gradingForm" action="{{ route('lessons.activities.store-grade', [$lesson, $activity, $submission]) }}" method="POST">
                                    @csrf
                                    
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 25%;">Rubric Category</th>
                                                    <th style="width: 35%;">Description</th>
                                                    <th style="width: 15%;">Max Score</th>
                                                    <th style="width: 15%;">Weight</th>
                                                    <th style="width: 10%;">Score</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($rubrics as $rubric)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $rubric->category_name }}</strong>
                                                        </td>
                                                        <td>
                                                            {{ $rubric->description }}
                                                        </td>
                                                        <td class="text-center">
                                                            <strong>{{ $rubric->max_score }}</strong>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-primary">{{ $rubric->weight }}%</span>
                                                        </td>
                                                        <td>
                                                            <input type="number" 
                                                                   class="form-control score-input" 
                                                                   name="scores[{{ $rubric->id }}]" 
                                                                   min="0" 
                                                                   max="{{ $rubric->max_score }}" 
                                                                   value="{{ old('scores.' . $rubric->id, 0) }}"
                                                                   data-max="{{ $rubric->max_score }}"
                                                                   data-weight="{{ $rubric->weight }}"
                                                                   onchange="calculateTotal()">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <td colspan="3"></td>
                                                    <td class="text-center">
                                                        <strong>Total Weight: <span id="totalWeight">0</span>%</strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <strong>Total Score: <span id="totalScore">0</span></strong>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <!-- Grade Summary -->
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title">Grade Summary</h6>
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <p><strong>Raw Score:</strong> <span id="rawScore">0</span></p>
                                                            <p><strong>Max Possible:</strong> <span id="maxPossible">0</span></p>
                                                        </div>
                                                        <div class="col-6">
                                                            <p><strong>Percentage:</strong> <span id="percentage">0%</span></p>
                                                            <p><strong>Letter Grade:</strong> <span id="letterGrade">-</span></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="feedback">Teacher Feedback</label>
                                                <textarea class="form-control" id="feedback" name="feedback" rows="4" placeholder="Provide constructive feedback to the student...">{{ old('feedback') }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hidden fields for calculated values -->
                                    <input type="hidden" name="total_score" id="hiddenTotalScore" value="0">
                                    <input type="hidden" name="max_possible_score" id="hiddenMaxPossible" value="0">
                                    <input type="hidden" name="percentage" id="hiddenPercentage" value="0">
                                    <input type="hidden" name="letter_grade" id="hiddenLetterGrade" value="">
                                </form>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    No rubric categories have been set up for this activity. 
                                    <a href="{{ route('lessons.activities.rubric', [$lesson, $activity]) }}" class="alert-link">Set up rubrics first</a>.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grade History (if previously graded) -->
            @if($submission->status === 'graded')
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Previous Grade</h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <p><strong>Previous Score:</strong> {{ $submission->total_score }}/{{ $submission->max_possible_score }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <p><strong>Previous Percentage:</strong> {{ number_format(($submission->total_score / $submission->max_possible_score) * 100, 1) }}%</p>
                                    </div>
                                    <div class="col-md-3">
                                        <p><strong>Previous Grade:</strong> <span class="badge bg-{{ $submission->letter_grade_color }}">{{ $submission->letter_grade }}</span></p>
                                    </div>
                                    <div class="col-md-3">
                                        <p><strong>Graded On:</strong> {{ $submission->graded_at->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>
                                @if($submission->feedback)
                                    <div class="mt-3">
                                        <strong>Previous Feedback:</strong>
                                        <p class="text-muted">{{ $submission->feedback }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
<style>
    .table th {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }
    
    .score-input {
        text-align: center;
        font-weight: 600;
    }
    
    .score-input:focus {
        border-color: #3d5ee1;
        box-shadow: 0 0 0 0.2rem rgba(61, 94, 225, 0.25);
    }
    
    .badge.bg-primary {
        background-color: #3d5ee1 !important;
    }
    
    .badge.bg-success {
        background-color: #7bb13c !important;
    }
    
    .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
    }
    
    .badge.bg-danger {
        background-color: #dc3545 !important;
    }
    
    .card.bg-light {
        background-color: #f8f9fa !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    calculateTotal();
    
    // Validate score inputs
    $('.score-input').on('input', function() {
        let value = parseInt($(this).val());
        let max = parseInt($(this).data('max'));
        
        if (value > max) {
            $(this).val(max);
        } else if (value < 0) {
            $(this).val(0);
        }
        
        calculateTotal();
    });
});

function calculateTotal() {
    let totalScore = 0;
    let totalWeight = 0;
    let maxPossible = 0;
    
    $('.score-input').each(function() {
        let score = parseInt($(this).val()) || 0;
        let max = parseInt($(this).data('max'));
        let weight = parseInt($(this).data('weight'));
        
        totalScore += score;
        totalWeight += weight;
        maxPossible += max;
    });
    
    let percentage = maxPossible > 0 ? (totalScore / maxPossible) * 100 : 0;
    let letterGrade = getLetterGrade(percentage);
    
    // Update display
    $('#totalScore').text(totalScore);
    $('#totalWeight').text(totalWeight);
    $('#rawScore').text(totalScore);
    $('#maxPossible').text(maxPossible);
    $('#percentage').text(percentage.toFixed(1) + '%');
    $('#letterGrade').text(letterGrade).removeClass().addClass('badge bg-' + getLetterGradeColor(letterGrade));
    
    // Update hidden fields
    $('#hiddenTotalScore').val(totalScore);
    $('#hiddenMaxPossible').val(maxPossible);
    $('#hiddenPercentage').val(percentage.toFixed(1));
    $('#hiddenLetterGrade').val(letterGrade);
}

function getLetterGrade(percentage) {
    if (percentage >= 90) return 'A';
    if (percentage >= 85) return 'B+';
    if (percentage >= 80) return 'B';
    if (percentage >= 75) return 'C+';
    if (percentage >= 70) return 'C';
    if (percentage >= 65) return 'D+';
    if (percentage >= 60) return 'D';
    return 'F';
}

function getLetterGradeColor(letterGrade) {
    switch(letterGrade) {
        case 'A': return 'success';
        case 'B+': return 'success';
        case 'B': return 'primary';
        case 'C+': return 'primary';
        case 'C': return 'warning';
        case 'D+': return 'warning';
        case 'D': return 'danger';
        case 'F': return 'danger';
        default: return 'secondary';
    }
}

function saveGrade() {
    // Validate form
    let totalWeight = parseInt($('#totalWeight').text());
    if (totalWeight !== 100) {
        alert('Total weight must equal 100%. Current total: ' + totalWeight + '%');
        return;
    }
    
    let feedback = $('#feedback').val().trim();
    if (!feedback) {
        if (!confirm('No feedback provided. Continue anyway?')) {
            return;
        }
    }
    
    // Submit form
    $('#gradingForm').submit();
}
</script>
@endpush 