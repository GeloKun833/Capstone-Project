<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
            font-size: 10px;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .student-name {
            text-align: left;
            font-weight: bold;
            min-width: 120px;
        }
        .present {
            background-color: #d4edda;
            color: #155724;
        }
        .absent {
            background-color: #f8d7da;
            color: #721c24;
        }
        .late {
            background-color: #fff3cd;
            color: #856404;
        }
        .summary {
            margin-top: 20px;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table th,
        .summary-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .summary-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .page-break {
            page-break-before: always;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Attendance Report</h1>
        <p>Generated on: {{ now()->format('F j, Y g:i A') }}</p>
        <p>Period: {{ \Carbon\Carbon::createFromFormat('Y-m', request('month', now()->format('Y-m')))->format('F Y') }}</p>
    </div>

    @if(count($students) > 0)
        <table>
            <thead>
                <tr>
                    <th class="student-name">Student Name</th>
                    @foreach($days as $day)
                        <th>{{ $day }}</th>
                    @endforeach
                    <th>Present</th>
                    <th>Total</th>
                    <th>%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                    <tr>
                        <td class="student-name">{{ $student->first_name }} {{ $student->last_name }}</td>
                        @foreach($days as $day)
                            @php
                                $status = $attendanceMap[$student->id][$day] ?? null;
                                $statusClass = '';
                                $statusText = '-';
                                
                                if ($status === 'present') {
                                    $statusClass = 'present';
                                    $statusText = 'P';
                                } elseif ($status === 'absent') {
                                    $statusClass = 'absent';
                                    $statusText = 'A';
                                } elseif ($status === 'late') {
                                    $statusClass = 'late';
                                    $statusText = 'L';
                                }
                            @endphp
                            <td class="{{ $statusClass }}">{{ $statusText }}</td>
                        @endforeach
                        <td>{{ $summary[$student->id]['present'] ?? 0 }}</td>
                        <td>{{ $summary[$student->id]['total'] ?? 0 }}</td>
                        <td>{{ $summary[$student->id]['percentage'] ?? 0 }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <h3>Summary</h3>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Late</th>
                        <th>Total Days</th>
                        <th>Attendance %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        @php
                            $studentSummary = $summary[$student->id] ?? [];
                            $present = $studentSummary['present'] ?? 0;
                            $total = $studentSummary['total'] ?? 0;
                            $absent = 0;
                            $late = 0;
                            
                            // Calculate absent and late from attendance map
                            foreach($days as $day) {
                                $status = $attendanceMap[$student->id][$day] ?? null;
                                if ($status === 'absent') {
                                    $absent++;
                                } elseif ($status === 'late') {
                                    $late++;
                                }
                            }
                        @endphp
                        <tr>
                            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                            <td>{{ $present }}</td>
                            <td>{{ $absent }}</td>
                            <td>{{ $late }}</td>
                            <td>{{ $total }}</td>
                            <td>{{ $studentSummary['percentage'] ?? 0 }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="text-align: center; padding: 40px;">
            <h3>No Data Available</h3>
            <p>No students found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by Panorama Montessori School LMS System</p>
        <p>Page 1 of 1</p>
    </div>
</body>
</html> 