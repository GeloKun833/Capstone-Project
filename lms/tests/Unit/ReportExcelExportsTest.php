<?php

namespace Tests\Unit;

use App\Exports\ClassListExport;
use App\Exports\GradeSlipExport;
use App\Exports\ProgressSummaryExport;
use PHPUnit\Framework\TestCase;

class ReportExcelExportsTest extends TestCase
{
    public function test_class_list_export_includes_full_student_metadata(): void
    {
        $export = new ClassListExport([
            [1, 'Grade 10', 'Ruby', 'Mrs. Santos', 'Juan', 'S-001', 'Dela Cruz', 'Juan', '', 'Male', 'juan@example.com', 'Grade 10'],
        ], 'Ruby');

        $this->assertSame([
            '#',
            'Grade Level',
            'Section',
            'Adviser',
            'Student Name',
            'Student ID',
            'Last Name',
            'First Name',
            'Middle Name',
            'Gender',
            'Email',
            'Year Level',
        ], $export->headings());
    }

    public function test_grade_slip_export_includes_student_context(): void
    {
        $export = new GradeSlipExport([
            ['Juan Dela Cruz', 'S-001', 'Grade 10', 'Ruby', '2025-2026', '1st Semester', 'Math', 90, 88, 92, 95, 91, 'Excellent'],
        ]);

        $this->assertSame([
            'Student Name',
            'Student ID',
            'Year Level',
            'Section',
            'Academic Year',
            'Semester',
            'Subject',
            'Q1',
            'Q2',
            'Q3',
            'Q4',
            'Final Average',
            'Remarks',
        ], $export->headings());
    }

    public function test_progress_summary_export_includes_student_context(): void
    {
        $export = new ProgressSummaryExport([
            ['Juan Dela Cruz', 'S-001', 'Grade 10', 'Ruby', '2025-2026', '1st Semester', 'Subject Performance', 'Math', '91.00%', 'Strong performance'],
        ]);

        $this->assertSame([
            'Student Name',
            'Student ID',
            'Year Level',
            'Section',
            'Academic Year',
            'Semester',
            'Category',
            'Subject / Metric',
            'Value',
            'Narrative',
        ], $export->headings());
    }
}
