@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header"><h3 class="page-title">System Activity Log (All Users)</h3></div>
        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead><tr><th>Date</th><th>User</th><th>Action</th><th>Details</th></tr></thead>
                    <tbody>
                        @forelse($activities as $activity)
                            <tr>
                                <td>{{ $activity->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $activity->causer->name ?? 'System' }}</td>
                                <td>{{ $activity->description }}</td>
                                <td>{{ $activity->subject_type ? class_basename($activity->subject_type) . ' #' . $activity->subject_id : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No activity recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $activities->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
