@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Create course outline and OPTLB</h3>
                    <p class="text-muted mb-0">{{ $curriculum->grade_level }} · {{ $year->name }}</p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('curriculum.index') }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">Check the highlighted fields and save again.</div>
        @endif

        @if($subjects->isEmpty())
            <div class="alert alert-light border">Add subjects for {{ $curriculum->grade_level }} before creating a course outline.</div>
        @else
            <form method="POST" action="{{ route('curriculum.demo.store', $curriculum) }}" enctype="multipart/form-data">
                @csrf
                <div class="card mb-3">
                    <div class="card-header"><h5 class="card-title mb-0">Course outline</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="subject_id">Subject</label>
                                <select name="subject_id" id="subject_id" class="form-control" required>
                                    <option value="">Select a subject</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->subject_name }}</option>
                                    @endforeach
                                </select>
                                @error('subject_id')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="title">Course outline title</label>
                                <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
                                @error('title')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="description">Course description</label>
                            <textarea name="description" id="description" rows="3" class="form-control" required>{{ old('description') }}</textarea>
                            @error('description')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="learning_objectives">Learning objectives</label>
                            <textarea name="learning_objectives" id="learning_objectives" rows="3" class="form-control" required>{{ old('learning_objectives') }}</textarea>
                            @error('learning_objectives')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="topic_1">Topic unit 1</label>
                                <input type="text" name="topic_1" id="topic_1" class="form-control" value="{{ old('topic_1') }}" required>
                                @error('topic_1')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="topic_2">Topic unit 2</label>
                                <input type="text" name="topic_2" id="topic_2" class="form-control" value="{{ old('topic_2') }}" required>
                                @error('topic_2')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="topic_3">Topic unit 3</label>
                                <input type="text" name="topic_3" id="topic_3" class="form-control" value="{{ old('topic_3') }}" required>
                                @error('topic_3')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="assessment_methods">Assessment methods</label>
                            <textarea name="assessment_methods" id="assessment_methods" rows="2" class="form-control" required>{{ old('assessment_methods') }}</textarea>
                            @error('assessment_methods')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="outline_file">Course outline file</label>
                            <input type="file" name="outline_file" id="outline_file" class="form-control" accept="application/pdf">
                            <div class="form-text">Optional PDF. A file is attached automatically when this is left empty.</div>
                            @error('outline_file')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><h5 class="card-title mb-0">OPTLB</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="optlb_title">OPTLB title</label>
                            <input type="text" name="optlb_title" id="optlb_title" class="form-control" value="{{ old('optlb_title') }}" required>
                            @error('optlb_title')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="optlb_objectives">Learning objectives</label>
                            <textarea name="optlb_objectives" id="optlb_objectives" rows="3" class="form-control" required>{{ old('optlb_objectives') }}</textarea>
                            @error('optlb_objectives')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="optlb_sequence">Topic or lesson sequence</label>
                            <textarea name="optlb_sequence" id="optlb_sequence" rows="3" class="form-control" required>{{ old('optlb_sequence') }}</textarea>
                            @error('optlb_sequence')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="optlb_activities">Suggested learning activities</label>
                            <textarea name="optlb_activities" id="optlb_activities" rows="3" class="form-control" required>{{ old('optlb_activities') }}</textarea>
                            @error('optlb_activities')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="optlb_assessment">Assessment or evaluation</label>
                            <textarea name="optlb_assessment" id="optlb_assessment" rows="2" class="form-control" required>{{ old('optlb_assessment') }}</textarea>
                            @error('optlb_assessment')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="optlb_references">References or learning resources</label>
                            <textarea name="optlb_references" id="optlb_references" rows="2" class="form-control" required>{{ old('optlb_references') }}</textarea>
                            @error('optlb_references')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="optlb_file">OPTLB file</label>
                            <input type="file" name="optlb_file" id="optlb_file" class="form-control" accept="application/pdf">
                            <div class="form-text">Optional PDF. A file is attached automatically when this is left empty.</div>
                            @error('optlb_file')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save course outline and OPTLB</button>
            </form>
        @endif
    </div>
</div>
@endsection
