@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">Deactivate Teacher</h3>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <p class="mb-1"><strong>Teacher:</strong> {{ $user->name }}</p>
                    <p class="mb-3"><strong>Academic Year:</strong> {{ $year->displayName() }}</p>
                    <p class="fw-semibold">Assignments to be transferred:</p>
                    <ul>
                        @foreach($summary as $item)
                            <li>{{ $item['label'] }} → {{ $item['teacher'] }}</li>
                        @endforeach
                    </ul>
                    <div class="alert alert-info">
                        After confirmation, these assignments will be transferred to the selected teachers and {{ $user->name }} will be marked Inactive. Historical Academic Year records will not be changed.
                    </div>
                    <form method="POST" action="{{ route('teacher.deactivate.confirm', $user->user_id) }}">
                        @csrf
                        @foreach($replacements as $key => $teacherId)
                            <input type="hidden" name="replacements[{{ $key }}]" value="{{ $teacherId }}">
                        @endforeach
                        <a href="{{ route('teacher.deactivate.transfer', $user->user_id) }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-danger">Confirm Transfer &amp; Deactivate</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
