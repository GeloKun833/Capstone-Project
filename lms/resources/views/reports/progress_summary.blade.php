<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Progress Summary - {{ $student->full_name }}</title>
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
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #333;
            color: white;
            padding: 10px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .summary-box {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border: 2px solid #000;
        }
        .summary-box table {
            border: none;
        }
        .summary-box td {
            border: none;
            padding: 5px;
        }
        .summary-box td.label {
            font-weight: bold;
            width: 200px;
        }
        .alert-box {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            padding: 10px;
            margin: 10px 0;
        }
        .alert-box h4 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        .chart-placeholder {
            background-color: #f9f9f9;
            border: 1px dashed #ccc;
            padding: 20px;
            text-align: center;
            color: #999;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Student Progress Summary</h1>
        <h2>Comprehensive Academic Report</h2>
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
            @if($academicYear && $semester)
            <tr>
                <td class="label">Academic Period:</td>
                <td><strong>{{ $academicYear->name }} - {{ $semester->name }}</strong></td>
                <td class="label">Generated Date:</td>
                <td>{{ \Carbon\Carbon::now()->format('F d, Y') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Subject Performance Section -->
    <div class="section">
        <div class="section-title">Subject Performance</div>
        @if(count($subjectPerformance) > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th style="width: 35%;">Subject</th>
                        <th style="width: 15%;">Average</th>
                        <th style="width: 15%;">Highest</th>
                        <th style="width: 15%;">Lowest</th>
                        <th style="width: 15%;">Grade Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subjectPerformance as $index => $perf)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td><strong>{{ $perf['subject']->subject_name }}</strong></td>
                            <td class="text-center">{{ number_format($perf['average'], 2) }}%</td>
                            <td class="text-center">{{ number_format($perf['max'], 2) }}%</td>
                            <td class="text-center">{{ number_format($perf['min'], 2) }}%</td>
                            <td class="text-center">{{ $perf['count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="text-align: center; color: #999; padding: 20px;">No subject performance data available.</p>
        @endif
    </div>

    @if(!empty($narratives))
    <div class="section">
        <div class="section-title">Narrative Feedback</div>
        <div class="summary-box">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($narratives as $narrative)
                    <li style="margin-bottom: 8px;">{{ $narrative }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- GPA Records Section -->
    @if($gpaRecords->count() > 0)
        <div class="section">
            <div class="section-title">GPA History</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 30%;">Academic Year</th>
                        <th style="width: 20%;">Semester</th>
                        <th style="width: 15%;">GPA</th>
                        <th style="width: 15%;">Rank</th>
                        <th style="width: 20%;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gpaRecords as $gpa)
                        <tr>
                            <td>{{ $gpa->academicYear->name ?? 'N/A' }}</td>
                            <td>{{ $gpa->semester->name ?? 'N/A' }}</td>
                            <td class="text-center"><strong>{{ number_format($gpa->gpa, 2) }}</strong></td>
                            <td class="text-center">{{ $gpa->rank ?? 'N/A' }}</td>
                            <td class="text-center">{{ $gpa->created_at->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Attendance Summary Section -->
    <div class="section">
        <div class="section-title">Attendance Summary</div>
        <div class="summary-box">
            <table>
                <tr>
                    <td class="label">Total Attendance Records:</td>
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
    </div>

    <!-- Recent Activities Section -->
    @if($recentActivities->count() > 0)
        <div class="section">
            <div class="section-title">Recent Activity Submissions</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 30%;">Activity</th>
                        <th style="width: 25%;">Subject</th>
                        <th style="width: 15%;">Submitted Date</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 15%;">Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentActivities as $activity)
                        <tr>
                            <td>{{ $activity->activity->title ?? 'N/A' }}</td>
                            <td>{{ $activity->activity->lesson->subject->subject_name ?? 'N/A' }}</td>
                            <td class="text-center">{{ $activity->created_at->format('M d, Y') }}</td>
                            <td class="text-center">{{ ucfirst($activity->status ?? 'N/A') }}</td>
                            <td class="text-center">{{ $activity->total_score ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Grade Alerts Section -->
    @if($gradeAlerts->count() > 0)
        <div class="section">
            <div class="section-title">Active Grade Alerts</div>
            @foreach($gradeAlerts as $alert)
                <div class="alert-box">
                    <h4>⚠️ Alert: {{ $alert->subject->subject_name ?? 'N/A' }}</h4>
                    <p><strong>Message:</strong> {{ $alert->message }}</p>
                    <p><strong>Current Value:</strong> {{ $alert->current_value }}% | <strong>Threshold:</strong> {{ $alert->threshold_value }}%</p>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Overall Summary -->
    <div class="summary-box">
        <h3 style="margin-top: 0; text-align: center;">Overall Summary</h3>
        <table>
            <tr>
                <td class="label">Total Subjects:</td>
                <td><strong>{{ count($subjectPerformance) }}</strong></td>
                <td class="label">Average Performance:</td>
                <td><strong>
                    @if(count($subjectPerformance) > 0)
                        {{ number_format(collect($subjectPerformance)->avg('average'), 2) }}%
                    @else
                        N/A
                    @endif
                </strong></td>
            </tr>
            <tr>
                <td class="label">Current GPA:</td>
                <td><strong>{{ $gpaRecords->first() ? number_format($gpaRecords->first()->gpa, 2) : 'N/A' }}</strong></td>
                <td class="label">Current Rank:</td>
                <td><strong>{{ $gpaRecords->first() ? ($gpaRecords->first()->rank ?? 'N/A') : 'N/A' }}</strong></td>
            </tr>
            <tr>
                <td class="label">Active Alerts:</td>
                <td><strong>{{ $gradeAlerts->count() }}</strong></td>
                <td class="label">Recent Submissions:</td>
                <td><strong>{{ $recentActivities->count() }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>This progress summary was generated on {{ \Carbon\Carbon::now()->format('F d, Y \a\t g:i A') }}</p>
        <p>For verification, please contact the school registrar's office.</p>
    </div>
</body>
</html>


