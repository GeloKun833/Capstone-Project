<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceExport implements FromCollection, WithHeadings
{
    protected $data;
    protected $days;

    public function __construct($data, $days)
    {
        $this->data = $data;
        $this->days = $days;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        $headings = ['Student'];
        foreach ($this->days as $day) {
            $headings[] = 'Day ' . $day;
        }
        $headings[] = 'Present';
        $headings[] = 'Total';
        $headings[] = '%';
        return $headings;
    }
} 