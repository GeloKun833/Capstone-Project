<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Grade Slip - {{ $student->full_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            font-weight: normal;
        }
        .student-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .student-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .student-info td {
            padding: 5px 10px;
            border: none;
        }
        .student-info td.label {
            font-weight: bold;
            width: 150px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #333;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        .subject-row {
            background-color: #fff;
        }
        .component-row {
            background-color: #f9f9f9;
            font-size: 10px;
        }
        .component-row td {
            padding-left: 30px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border: 2px solid #000;
        }
        .summary table {
            border: none;
        }
        .summary td {
            border: none;
            padding: 5px;
        }
        .summary td.label {
            font-weight: bold;
            width: 200px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        .period-info {
            background-color: #333;
            color: white;
            padding: 10px;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Grade Slip</h1>
        <h2>Official Student Grade Report</h2>
    </div>

    <div class="student-info">
        <table>
            <tr>
                <td class="label">Student Name:</td>
                <td><strong>{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name ?? '' }}</strong></td>
                <td class="label">Student ID:</td>
                <td><strong>{{ $student->id }}</strong></td>
            </tr>
            <tr>
                <td class="label">Year Level:</td>
                <td>{{ $student->year_level ?? 'N/A' }}</td>
                <td class="label">Section:</td>
                <td>{{ $student->sections->first() ? $student->sections->first()->name : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Email:</td>
                <td>{{ $student->email ?? 'N/A' }}</td>
                <td class="label">Generated Date:</td>
                <td>{{ \Carbon\Carbon::now()->format('F d, Y') }}</td>
            </tr>
        </table>
    </div>

    @if($academicYear && $semester)
        <div class="period-info">
            Academic Period: {{ $academicYear->name }} - {{ $semester->name }}
        </div>
    @endif

    @if(count($subjectGrades) > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 30%;">Subject</th>
                    <th style="width: 20%;">Component</th>
                    <th style="width: 10%;">Score</th>
                    <th style="width: 10%;">Max Score</th>
                    <th style="width: 10%;">Percentage</th>
                    <th style="width: 10%;">Grade</th>
                    <th style="width: 5%;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @php $subjectNum = 1; @endphp
                @foreach($subjectGrades as $subjectData)
                    @php
                        $firstComponent = true;
                        $componentCount = 0;
                        foreach($subjectData['components'] as $component) {
                            $componentCount += $component->count();
                        }
                    @endphp
                    
                    @foreach($subjectData['components'] as $componentId => $componentGrades)
                        @foreach($componentGrades as $grade)
                            <tr class="{{ $firstComponent ? 'subject-row' : 'component-row' }}">
                                @if($firstComponent)
                                    <td class="text-center" rowspan="{{ $componentCount }}">{{ $subjectNum }}</td>
                                    <td rowspan="{{ $componentCount }}"><strong>{{ $subjectData['subject']->subject_name }}</strong></td>
                                    @php $firstComponent = false; @endphp
                                @endif
                                <td>{{ $grade->component->name ?? 'N/A' }}</td>
                                <td class="text-center">{{ number_format($grade->score, 2) }}</td>
                                <td class="text-center">{{ number_format($grade->max_score, 2) }}</td>
                                <td class="text-center">{{ number_format($grade->percentage, 2) }}%</td>
                                <td class="text-center">
                                    @php
                                        $percentage = $grade->percentage;
                                        if ($percentage >= 90) echo 'A';
                                        elseif ($percentage >= 80) echo 'B';
                                        elseif ($percentage >= 70) echo 'C';
                                        elseif ($percentage >= 60) echo 'D';
                                        else echo 'F';
                                    @endphp
                                </td>
                                <td class="text-center">{{ $grade->remarks ?? '-' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                    
                    <tr style="background-color: #e8e8e8; font-weight: bold;">
                        <td colspan="5" class="text-right">Average for {{ $subjectData['subject']->subject_name }}:</td>
                        <td class="text-center">{{ number_format($subjectData['average'], 2) }}%</td>
                        <td class="text-center">
                            @php
                                $avg = $subjectData['average'];
                                if ($avg >= 90) echo 'A';
                                elseif ($avg >= 80) echo 'B';
                                elseif ($avg >= 70) echo 'C';
                                elseif ($avg >= 60) echo 'D';
                                else echo 'F';
                            @endphp
                        </td>
                        <td class="text-center">
                            @php
                                $avg = $subjectData['average'];
                                if ($avg >= 75) echo 'PASSED';
                                else echo 'FAILED';
                            @endphp
                        </td>
                    </tr>
                    @php $subjectNum++; @endphp
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 40px; color: #999;">
            <p>No grades found for the selected period.</p>
        </div>
    @endif

    <div class="summary">
        <h3 style="margin-top: 0; text-align: center;">Academic Summary</h3>
        <table>
            @if($gpa)
                <tr>
                    <td class="label">GPA:</td>
                    <td><strong>{{ number_format($gpa->gpa, 2) }}</strong></td>
                    <td class="label">Rank:</td>
                    <td><strong>{{ $gpa->rank ?? 'N/A' }}</strong></td>
                </tr>
            @endif
            <tr>
                <td class="label">Total Attendance:</td>
                <td><strong>{{ $attendanceSummary['total'] }}</strong></td>
                <td class="label">Present:</td>
                <td><strong>{{ $attendanceSummary['present'] }}</strong></td>
            </tr>
            <tr>
                <td class="label">Absent:</td>
                <td><strong>{{ $attendanceSummary['absent'] }}</strong></td>
                <td class="label">Attendance Rate:</td>
                <td><strong>{{ number_format($attendanceSummary['percentage'], 2) }}%</strong></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>This grade slip was generated on {{ \Carbon\Carbon::now()->format('F d, Y \a\t g:i A') }}</p>
        <p>For verification, please contact the school registrar's office.</p>
    </div>
</body>
</html>


