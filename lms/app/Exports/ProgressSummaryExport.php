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

class ProgressSummaryExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle, WithStyles
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
        return ['Student Name', 'Student ID', 'Year Level', 'Section', 'Academic Year', 'Semester', 'Category', 'Subject / Metric', 'Value', 'Narrative'];
    }

    public function title(): string
    {
        return 'Progress Summary';
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
                'startColor' => ['rgb' => '2563EB'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        for ($row = 2; $row <= $lastRow; $row++) {
            $fillColor = $row % 2 === 0 ? 'EFF6FF' : 'FFFFFF';
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
