<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $assignment->title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }
        h1 { font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 6px; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>{{ $assignment->title }}</h1>
    <p><strong>Subject:</strong> {{ $assignment->subject->subject_name ?? 'N/A' }}</p>
    <p><strong>Section:</strong> {{ $assignment->section->name ?? 'N/A' }}</p>
    <p><strong>Due:</strong> {{ $assignment->due_date ? \Carbon\Carbon::parse($assignment->due_date)->format('M d, Y') : 'N/A' }}</p>
    <p>{{ $assignment->description }}</p>
    <h3>Submissions ({{ $assignment->submissions->count() }})</h3>
    <table>
        <thead><tr><th>Student</th><th>Status</th><th>Score</th><th>Submitted</th></tr></thead>
        <tbody>
            @foreach($assignment->submissions as $sub)
                <tr>
                    <td>{{ $sub->student->first_name ?? '' }} {{ $sub->student->last_name ?? '' }}</td>
                    <td>{{ ucfirst($sub->status ?? 'pending') }}</td>
                    <td>{{ $sub->score ?? '-' }}</td>
                    <td>{{ $sub->submitted_at ? $sub->submitted_at->format('M d, Y') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
