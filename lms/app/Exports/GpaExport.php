<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\StudentGpa;

class GpaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $academicYearId;
    protected $semesterId;
    protected $sectionId;

    public function __construct($academicYearId = null, $semesterId = null, $sectionId = null)
    {
        $this->academicYearId = $academicYearId;
        $this->semesterId = $semesterId;
        $this->sectionId = $sectionId;
    }

    public function collection()
    {
        $query = StudentGpa::with(['student', 'academicYear', 'semester'])
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId);

        if ($this->sectionId) {
            $query->whereHas('student.sections', function($q) {
                $q->where('section_id', $this->sectionId);
            });
        }

        return $query->orderBy('gpa', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Rank',
            'Student ID',
            'Student Name',
            'GPA',
            'Letter Grade',
            'Grade Description',
            'Total Units',
            'Total Grade Points',
            'Academic Year',
            'Semester',
            'Remarks',
        ];
    }

    public function map($gpaRecord): array
    {
        return [
            $gpaRecord->rank ?? 'N/A',
            $gpaRecord->student->admission_id ?? $gpaRecord->student->id,
            $gpaRecord->student->first_name . ' ' . $gpaRecord->student->last_name,
            $gpaRecord->gpa,
            $gpaRecord->letter_grade,
            $gpaRecord->grade_description,
            $gpaRecord->total_units,
            $gpaRecord->total_grade_points,
            $gpaRecord->academicYear->name ?? 'N/A',
            $gpaRecord->semester->name ?? 'N/A',
            $gpaRecord->remarks ?? 'N/A',
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
} 