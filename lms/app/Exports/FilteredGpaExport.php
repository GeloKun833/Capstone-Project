<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FilteredGpaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private Collection $rows)
    {
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Rank',
            'Student ID',
            'Student Name',
            'GPA',
            'Letter Grade',
            'Description',
            'Subjects',
            'Remarks',
        ];
    }

    public function map($row): array
    {
        return [
            $row->rank,
            $row->student->admission_id ?? $row->student_id,
            trim(($row->student->last_name ?? '').', '.($row->student->first_name ?? '')),
            $row->gpa,
            $row->letter_grade,
            $row->grade_description,
            $row->total_units,
            $row->remarks,
        ];
    }
}
