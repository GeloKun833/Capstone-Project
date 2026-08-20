@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col"><h3 class="page-title">Database Backup & Recovery</h3></div>
                <div class="col-auto">
                    <form action="{{ route('admin.backup.create') }}" method="POST">@csrf
                        <button type="submit" class="btn btn-primary"><i class="fas fa-database"></i> Create Backup Now</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-muted">Backups are stored securely on the server. Download and store copies off-site for disaster recovery.</p>
                <table class="table table-hover">
                    <thead><tr><th>Filename</th><th>Size</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($backups as $backup)
                            <tr>
                                <td>{{ $backup['name'] }}</td>
                                <td>{{ $backup['size'] }}</td>
                                <td>{{ $backup['date'] }}</td>
                                <td>
                                    <a href="{{ route('admin.backup.download', $backup['name']) }}" class="btn btn-sm btn-info">Download</a>
                                    <form action="{{ route('admin.backup.destroy', $backup['name']) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this backup?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No backups yet. Click "Create Backup Now" to generate one.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
