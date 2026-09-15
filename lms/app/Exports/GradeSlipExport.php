<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class GradeSlipExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return ['Subject', 'Q1', 'Q2', 'Q3', 'Q4', 'Final', 'Remarks'];
    }

    public function title(): string
    {
        return 'Grade Slip';
    }
}
