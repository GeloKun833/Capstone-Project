<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Grades Export</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>Grades Report</h2>
    <p>Generated: {{ now()->format('F d, Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Subject</th>
                <th>Component</th>
                <th>Score</th>
                <th>%</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($grades as $grade)
                <tr>
                    <td>{{ $grade->student->first_name ?? '' }} {{ $grade->student->last_name ?? '' }}</td>
                    <td>{{ $grade->subject->subject_name ?? 'N/A' }}</td>
                    <td>{{ $grade->component->name ?? 'N/A' }}</td>
                    <td>{{ $grade->score }}/{{ $grade->max_score }}</td>
                    <td>{{ number_format($grade->percentage, 1) }}%</td>
                    <td>{{ $grade->remarks ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;">No grades found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
