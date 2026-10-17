@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Create OPTLB</h3>
                    <p class="text-muted mb-0">{{ $curriculum->grade_level }} · DEMO — NOT OFFICIAL</p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('curriculum.index') }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </div>
        </div>

        <div class="alert alert-warning">
            This saves a demonstration OPTLB linked to a course outline. It is not an official OPTLB document.
        </div>

        @if($outlines->isEmpty())
            <div class="alert alert-light border">Create a course outline first, or every outline for {{ $curriculum->grade_level }} already has an OPTLB.</div>
        @else
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('curriculum.demo.store-optlb', $curriculum) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="course_outline_id">Course outline</label>
                            <select name="course_outline_id" id="course_outline_id" class="form-control" required>
                                <option value="">Select a course outline</option>
                                @foreach($outlines as $outline)
                                    <option value="{{ $outline->id }}" @selected(old('course_outline_id') == $outline->id)>
                                        {{ $outline->subject->subject_name ?? 'Subject' }} — {{ $outline->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('course_outline_id')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="title">OPTLB title</label>
                            <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" placeholder="DEMO OPTLB — {{ $curriculum->grade_level }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="learning_objectives">Learning objectives</label>
                            <textarea name="learning_objectives" id="learning_objectives" rows="3" class="form-control">{{ old('learning_objectives') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="topic_sequence">Topic or lesson sequence</label>
                            <textarea name="topic_sequence" id="topic_sequence" rows="3" class="form-control">{{ old('topic_sequence') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="suggested_activities">Suggested learning activities</label>
                            <textarea name="suggested_activities" id="suggested_activities" rows="3" class="form-control">{{ old('suggested_activities') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="assessment">Assessment or evaluation</label>
                            <textarea name="assessment" id="assessment" rows="2" class="form-control">{{ old('assessment') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="references">References or learning resources</label>
                            <textarea name="references" id="references" rows="2" class="form-control">{{ old('references') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="file">Sample PDF</label>
                            <input type="file" name="file" id="file" class="form-control" accept="application/pdf">
                            <div class="form-text">Optional. A labeled demo PDF is created when this is left empty.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save OPTLB</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
