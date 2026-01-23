<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ContributionDepositFailedRecordsExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $failedRecords;

    public function __construct($failedRecords)
    {
        $this->failedRecords = $failedRecords;
    }

    public function headings(): array
    {
        return [
            'customer_id',
            'customer_name',
            'amount',
            'date',
            'description',
            'error_reason',
        ];
    }

    public function array(): array
    {
        $data = [];
        foreach ($this->failedRecords as $record) {
            $data[] = [
                $record['customer_id'] ?? '',
                $record['customer_name'] ?? '',
                $record['amount'] ?? '',
                $record['date'] ?? '',
                $record['description'] ?? '',
                $record['error_reason'] ?? 'Unknown error',
            ];
        }
        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row with red background for error indication
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DC3545'],
            ],
        ]);

        // Add borders to all cells
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        if ($highestRow > 0) {
            $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
        }

        return [];
    }
}
