<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Section Report Cards</title>
    @include('reports.partials.report-card-styles')
</head>
<body>
    <div class="actions">
        <button type="button" onclick="window.print()">Print All / Save as PDF</button>
    </div>

    @foreach($cards as $card)
        @php
            $student = $card['student'];
            $academicYear = $card['academicYear'];
            $observedGrouped = $card['observedGrouped'];
            $observedRatings = $card['observedRatings'];
            $learningRows = $card['learningRows'];
            $generalAverages = $card['generalAverages'];
            $attendanceMonths = $card['attendanceMonths'] ?? \App\Services\ReportCardService::ATTENDANCE_MONTHS;
            $attendanceRows = $card['attendanceRows'] ?? null;
            $focusQuarter = $card['focusQuarter'] ?? ($quarter ?? null);
        @endphp
        <div class="page-break">
            @include('reports.partials.report-card-body')
        </div>
    @endforeach
</body>
</html>
