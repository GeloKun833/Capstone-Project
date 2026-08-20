<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class List - {{ $section->name }}</title>
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
        .section-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .section-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .section-info td {
            padding: 5px 10px;
            border: none;
        }
        .section-info td.label {
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
        .student-row:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
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
        .subjects-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Class List / Section Roster</h1>
        <h2>{{ $section->name }}</h2>
    </div>

    <div class="section-info">
        <table>
            <tr>
                <td class="label">Section Name:</td>
                <td><strong>{{ $section->name }}</strong></td>
                <td class="label">Grade Level:</td>
                <td><strong>{{ $section->grade_level }}</strong></td>
            </tr>
            <tr>
                <td class="label">Adviser:</td>
                <td>{{ $section->adviser ? $section->adviser->first_name . ' ' . $section->adviser->last_name : 'N/A' }}</td>
                <td class="label">Capacity:</td>
                <td>{{ $section->capacity ?? 'N/A' }}</td>
            </tr>
            @if($academicYear)
            <tr>
                <td class="label">Academic Year:</td>
                <td><strong>{{ $academicYear->name }}</strong></td>
                <td class="label">Semester:</td>
                <td><strong>{{ $semester ? $semester->name : 'N/A' }}</strong></td>
            </tr>
            @endif
            <tr>
                <td class="label">Generated Date:</td>
                <td>{{ \Carbon\Carbon::now()->format('F d, Y') }}</td>
                <td class="label">Total Students:</td>
                <td><strong>{{ $students->count() }}</strong></td>
            </tr>
        </table>
    </div>

    @if($students->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 10%;">Student ID</th>
                    <th style="width: 25%;">Last Name</th>
                    <th style="width: 25%;">First Name</th>
                    <th style="width: 15%;">Middle Name</th>
                    <th style="width: 10%;">Gender</th>
                    <th style="width: 10%;">Year Level</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $index => $student)
                    <tr class="student-row">
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $student->id }}</td>
                        <td>{{ $student->last_name }}</td>
                        <td>{{ $student->first_name }}</td>
                        <td>{{ $student->middle_name ?? 'N/A' }}</td>
                        <td class="text-center">{{ ucfirst($student->gender ?? 'N/A') }}</td>
                        <td class="text-center">{{ $student->year_level ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 40px; color: #999;">
            <p>No students found in this section for the selected period.</p>
        </div>
    @endif

    @if($subjects->count() > 0)
        <div class="subjects-section">
            <h3 style="margin-top: 30px; border-bottom: 2px solid #000; padding-bottom: 5px;">Subjects Offered</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%;">Code</th>
                        <th style="width: 40%;">Subject Name</th>
                        <th style="width: 30%;">Description</th>
                        <th style="width: 10%;">Units</th>
                        <th style="width: 10%;">Type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subjects as $subject)
                        <tr>
                            <td class="text-center">{{ $subject->subject_code ?? 'N/A' }}</td>
                            <td>{{ $subject->subject_name }}</td>
                            <td>{{ $subject->description ?? 'N/A' }}</td>
                            <td class="text-center">{{ $subject->units ?? 'N/A' }}</td>
                            <td class="text-center">{{ $subject->type ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="summary">
        <table>
            <tr>
                <td class="label">Total Number of Students:</td>
                <td><strong>{{ $students->count() }}</strong></td>
                <td class="label">Section Capacity:</td>
                <td><strong>{{ $section->capacity ?? 'N/A' }}</strong></td>
            </tr>
            <tr>
                <td class="label">Available Slots:</td>
                <td><strong>{{ ($section->capacity ?? 0) - $students->count() }}</strong></td>
                <td class="label">Total Subjects:</td>
                <td><strong>{{ $subjects->count() }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>This class list was generated on {{ \Carbon\Carbon::now()->format('F d, Y \a\t g:i A') }}</p>
        <p>For verification, please contact the school registrar's office.</p>
    </div>
</body>
</html>


