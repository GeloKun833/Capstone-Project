<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Grade;

class GradesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $subjectId;
    protected $sectionId;
    protected $academicYearId;
    protected $semesterId;

    public function __construct($subjectId = null, $sectionId = null, $academicYearId = null, $semesterId = null)
    {
        $this->subjectId = $subjectId;
        $this->sectionId = $sectionId;
        $this->academicYearId = $academicYearId;
        $this->semesterId = $semesterId;
    }

    public function collection()
    {
        $query = Grade::with(['student', 'subject', 'component', 'teacher', 'academicYear', 'semester'])
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId);

        if ($this->subjectId) {
            $query->where('subject_id', $this->subjectId);
        }

        if ($this->sectionId) {
            $query->whereHas('student.sections', function($q) {
                $q->where('section_id', $this->sectionId);
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Student ID',
            'Student Name',
            'Subject',
            'Component',
            'Score',
            'Max Score',
            'Percentage',
            'Grade Points',
            'Letter Grade',
            'Teacher',
            'Academic Year',
            'Semester',
            'Remarks',
            'Date',
        ];
    }

    public function map($grade): array
    {
        return [
            $grade->student->admission_id ?? $grade->student->id,
            $grade->student->first_name . ' ' . $grade->student->last_name,
            $grade->subject->subject_name,
            $grade->component->name,
            $grade->score,
            $grade->max_score,
            $grade->percentage . '%',
            $this->percentageToGradePoints($grade->percentage),
            $this->percentageToLetterGrade($grade->percentage),
            $grade->teacher->first_name . ' ' . $grade->teacher->last_name ?? 'N/A',
            $grade->academicYear->name ?? 'N/A',
            $grade->semester->name ?? 'N/A',
            $grade->remarks ?? 'N/A',
            $grade->created_at->format('M d, Y'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA']
                ]
            ],
        ];
    }

    private function percentageToGradePoints($percentage)
    {
        if ($percentage === null) return 'N/A';
        
        if ($percentage >= 90) return '4.0';
        if ($percentage >= 85) return '3.7';
        if ($percentage >= 80) return '3.3';
        if ($percentage >= 75) return '3.0';
        if ($percentage >= 70) return '2.7';
        if ($percentage >= 65) return '2.3';
        if ($percentage >= 60) return '2.0';
        if ($percentage >= 55) return '1.7';
        if ($percentage >= 50) return '1.3';
        if ($percentage >= 45) return '1.0';
        return '0.0';
    }

    private function percentageToLetterGrade($percentage)
    {
        if ($percentage === null) return 'N/A';
        
        if ($percentage >= 90) return 'A';
        if ($percentage >= 85) return 'B+';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 75) return 'C+';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 65) return 'D+';
        if ($percentage >= 60) return 'D';
        if ($percentage >= 55) return 'E+';
        if ($percentage >= 50) return 'E';
        return 'F';
    }
} 