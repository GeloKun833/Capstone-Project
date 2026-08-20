@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="col">
                <h3 class="page-title">Edit Activity</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('lessons.activities.update', [$lesson, $activity]) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $activity->title) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Instructions</label>
                        <textarea name="instructions" class="form-control" rows="5" required>{{ old('instructions', $activity->instructions) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $activity->due_date ? \Carbon\Carbon::parse($activity->due_date)->format('Y-m-d') : '') }}" required>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="allows_submission" class="form-check-input" id="allows_submission" {{ $activity->allows_submission ? 'checked' : '' }}>
                        <label class="form-check-label" for="allows_submission">Allow student submissions</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Activity</button>
                    <a href="{{ route('lessons.activities.show', [$lesson, $activity]) }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
