<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ClassListExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    protected array $data;
    protected string $title;

    public function __construct(array $data, string $title = 'Class List')
    {
        $this->data = $data;
        $this->title = $title;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return ['#', 'Student ID', 'Last Name', 'First Name', 'Middle Name', 'Gender', 'Email'];
    }

    public function title(): string
    {
        return $this->title;
    }
}
