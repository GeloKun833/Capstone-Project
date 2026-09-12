<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card — {{ $student->last_name }}, {{ $student->first_name }}</title>
    @include('reports.partials.report-card-styles')
</head>
<body>
    <div class="actions">
        <button type="button" onclick="window.print()">Print / Save as PDF</button>
    </div>

    @include('reports.partials.report-card-body')
</body>
</html>
