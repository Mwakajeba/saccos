<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BulkRepaymentTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $schedules;

    public function __construct($schedules)
    {
        $this->schedules = $schedules;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->schedules->map(function ($schedule) {
            $totalAmount = $schedule->principal + $schedule->interest + 
                          ($schedule->penalty ?? 0) + ($schedule->fees ?? 0);
            
            return [
                'customer' => $schedule->loan->customer->name ?? 'N/A',
                'schedule_id' => $schedule->id,
                'loan_id' => $schedule->loan_id,
                'amount' => number_format($totalAmount, 2, '.', '')
            ];
        });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Customer',
            'Schedule ID',
            'Loan ID',
            'Amount'
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 15,
            'C' => 15,
            'D' => 20,
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                
                // Protect the sheet
                $sheet->getProtection()->setSheet(true);
                $sheet->getProtection()->setPassword('repayment2026');
                
                // Lock columns A, B, C (Customer, Schedule ID, Loan ID)
                foreach (['A', 'B', 'C'] as $column) {
                    for ($row = 1; $row <= $highestRow; $row++) {
                        $sheet->getStyle($column . $row)
                            ->getProtection()
                            ->setLocked(Protection::PROTECTION_PROTECTED);
                    }
                }
                
                // Unlock column D (Amount) - make it editable
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getStyle('D' . $row)
                        ->getProtection()
                        ->setLocked(Protection::PROTECTION_UNPROTECTED);
                    
                    // Add light yellow background to editable cells
                    $sheet->getStyle('D' . $row)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('FFFFCC');
                }
                
                // Add borders to all cells
                $sheet->getStyle('A1:D' . $highestRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                
                // Center align Schedule ID and Loan ID columns
                $sheet->getStyle('B2:C' . $highestRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Right align Amount column
                $sheet->getStyle('D2:D' . $highestRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}
