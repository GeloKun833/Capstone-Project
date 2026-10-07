@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Transfer Teacher Assignments</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('view/user/edit/'.$user->user_id) }}">Edit Teacher</a></li>
                            <li class="breadcrumb-item active">Transfer Assignments</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning">
                This teacher cannot be deactivated yet. The teacher still has active assignments for Academic Year <strong>{{ $year->displayName() }}</strong>. Please transfer all assignments to another available teacher before deactivating the account.
            </div>

            @if($blocked)
                <div class="alert alert-danger">
                    Teacher cannot be deactivated. There is no available teacher who can take over the current assignment. Please assign or activate another eligible teacher first.
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <p class="mb-1"><strong>Teacher:</strong> {{ $teacher->full_name ?: $user->name }}</p>
                    <p class="mb-3"><strong>Academic Year:</strong> {{ $year->displayName() }}</p>

                    <form method="POST" action="{{ route('teacher.deactivate.review', $user->user_id) }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Section</th>
                                        <th>Grade Level</th>
                                        <th>Subject</th>
                                        <th>Schedule</th>
                                        <th>Transfer To</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $row)
                                        <tr>
                                            <td>{{ $row['section'] }}</td>
                                            <td>{{ $row['grade'] }}</td>
                                            <td>{{ $row['subject'] }}</td>
                                            <td>{{ $row['schedule'] }}</td>
                                            <td>
                                                <select class="form-control" name="replacements[{{ $row['key'] }}]" @disabled($blocked || ! $row['has_eligible']) required>
                                                    <option value="">Select Teacher</option>
                                                    @foreach($row['choices'] as $choice)
                                                        <option value="{{ $choice['id'] }}" @disabled(! $choice['eligible']) @selected((string) old('replacements.'.$row['key']) === (string) $choice['id'])>
                                                            {{ $choice['name'] }}{{ $choice['reason'] ? ' — schedule conflict' : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @foreach($row['choices'] as $choice)
                                                    @if($choice['reason'])
                                                        <small class="text-danger d-block">{{ $choice['reason'] }}</small>
                                                    @endif
                                                @endforeach
                                                @error('replacements.'.$row['key'])
                                                    <small class="text-danger d-block">{{ $message }}</small>
                                                @enderror
                                                @error($row['key'])
                                                    <small class="text-danger d-block">{{ $message }}</small>
                                                @enderror
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ url('view/user/edit/'.$user->user_id) }}" class="btn btn-outline-secondary">Cancel</a>
                            @unless($blocked)
                                <button type="submit" class="btn btn-primary">Review Transfer</button>
                            @endunless
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
