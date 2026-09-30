<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ClassListExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle, WithStyles
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
        return ['#', 'Grade Level', 'Section', 'Adviser', 'Student Name', 'Student ID', 'Last Name', 'First Name', 'Middle Name', 'Gender', 'Email', 'Year Level'];
    }

    public function title(): string
    {
        return $this->title;
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        $sheet->freezePane('A2');
        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
            'font' => ['name' => 'Calibri', 'size' => 10, 'color' => ['rgb' => '1F2937']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D7DFEA'],
                ],
            ],
        ]);

        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F3A5F'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        for ($row = 2; $row <= $lastRow; $row++) {
            $fillColor = $row % 2 === 0 ? 'F8FAFC' : 'FFFFFF';
            $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $fillColor],
                ],
            ]);
        }

        $sheet->getRowDimension(1)->setRowHeight(26);
    }
}
