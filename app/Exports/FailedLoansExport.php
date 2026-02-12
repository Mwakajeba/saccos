<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FailedLoansExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $failedLoans;

    public function __construct(array $failedLoans)
    {
        $this->failedLoans = $failedLoans;
    }

    public function headings(): array
    {
        return [
            'Row Number',
            'Customer No',
            'Customer Name',
            'Amount',
            'Interest',
            'Period',
            'Interest Cycle',
            'Date Applied',
            'Sector',
            'Loan Officer',
            'Amount Paid',
            'Failure Reason',
        ];
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->failedLoans as $failed) {
            $rows->push([
                $failed['row'] ?? '',
                $failed['customer_no'] ?? '',
                $failed['customer_name'] ?? '',
                $failed['amount'] ?? '',
                $failed['interest'] ?? '',
                $failed['period'] ?? '',
                $failed['interest_cycle'] ?? '',
                $failed['date_applied'] ?? '',
                $failed['sector'] ?? '',
                $failed['loan_officer_id'] ?? '',
                $failed['amount_paid'] ?? '',
                $failed['error'] ?? 'Unknown error',
            ]);
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DC3545'], // Red for failed
            ],
        ]);

        // Add borders to all cells
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:L' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Highlight failure reason column
        $sheet->getStyle('L2:L' . $highestRow)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFCCCC'], // Light red
            ],
        ]);

        return [];
    }
}
