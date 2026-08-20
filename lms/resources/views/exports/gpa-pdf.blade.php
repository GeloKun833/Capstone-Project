<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>GPA Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 6px; }
        th { background: #f0f0f0; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>GPA Ranking Report</h2>
    <p>Generated: {{ now()->format('F d, Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Student</th>
                <th>GPA</th>
                <th>Academic Year</th>
                <th>Semester</th>
            </tr>
        </thead>
        <tbody>
            @forelse($gpaRecords as $record)
                <tr>
                    <td>{{ $record->rank ?? '-' }}</td>
                    <td>{{ $record->student->first_name ?? '' }} {{ $record->student->last_name ?? '' }}</td>
                    <td>{{ number_format($record->gpa, 2) }}</td>
                    <td>{{ $record->academicYear->name ?? 'N/A' }}</td>
                    <td>{{ $record->semester->name ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;">No GPA records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
