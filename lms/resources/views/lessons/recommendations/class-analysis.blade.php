@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header"><h3 class="page-title">Class Performance Analysis</h3></div>
        <form method="GET" class="row mb-4">
            <div class="col-md-3">
                <select name="subject_id" class="form-control">
                    <option value="">Select Subject</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ $subjectId == $s->id ? 'selected' : '' }}>{{ $s->subject_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" name="section_id" class="form-control" placeholder="Section ID" value="{{ $sectionId }}">
            </div>
            <div class="col-md-2"><button class="btn btn-primary">Analyze</button></div>
        </form>
        @if($classAnalysis)
            <div class="row mb-4">
                <div class="col-md-3"><div class="card"><div class="card-body text-center"><h4>{{ $classAnalysis['average_score'] }}%</h4><p>Average</p></div></div></div>
                <div class="col-md-3"><div class="card"><div class="card-body text-center"><h4>{{ $classAnalysis['total_students'] }}</h4><p>Students</p></div></div></div>
                <div class="col-md-3"><div class="card"><div class="card-body text-center"><h4>{{ $classAnalysis['highest_score'] }}%</h4><p>Highest</p></div></div></div>
                <div class="col-md-3"><div class="card"><div class="card-body text-center"><h4>{{ $classAnalysis['lowest_score'] }}%</h4><p>Lowest</p></div></div></div>
            </div>
        @endif
        @if($weakStudents && $weakStudents->count())
            <div class="card mb-4">
                <div class="card-header"><h5>Students Needing Support</h5></div>
                <div class="card-body">
                    <table class="table"><thead><tr><th>Student</th><th>Average</th></tr></thead>
                    <tbody>@foreach($weakStudents as $ws)<tr><td>{{ $ws['student']->first_name }} {{ $ws['student']->last_name }}</td><td>{{ $ws['average_score'] }}%</td></tr>@endforeach</tbody></table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
