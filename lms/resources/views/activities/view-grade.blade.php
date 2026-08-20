@extends('layouts.master')
@section('content')
{!! Toastr::message() !!}
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header"><h3 class="page-title">Grade Details</h3></div>
        <div class="card">
            <div class="card-body">
                <p><strong>Student:</strong> {{ $submission->student->first_name }} {{ $submission->student->last_name }}</p>
                <p><strong>Score:</strong> {{ $submission->total_score ?? '-' }} / {{ $submission->max_possible_score ?? '-' }}</p>
                <p><strong>Percentage:</strong> {{ $submission->percentage ? $submission->percentage . '%' : '-' }}</p>
                <p><strong>Letter Grade:</strong> {{ $submission->letter_grade ?? '-' }}</p>
                @if($submission->feedback)<p><strong>Feedback:</strong> {{ $submission->feedback }}</p>@endif
                @if($submission->grades->count())
                    <h5 class="mt-3">Rubric Scores</h5>
                    <table class="table table-bordered">
                        <thead><tr><th>Rubric</th><th>Score</th></tr></thead>
                        <tbody>
                            @foreach($submission->grades as $grade)
                                <tr><td>{{ $grade->rubric->name ?? 'Rubric' }}</td><td>{{ $grade->score }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
