<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Contracts\View\View;

class ContributionRegisterExport implements FromView, ShouldAutoSize, WithStyles, WithEvents
{
    protected $accounts;
    protected $company;
    protected $contributionProduct;
    protected $status;
    protected $asOfDate;

    public function __construct($accounts, $company, $contributionProduct = null, $status = null, $asOfDate = null)
    {
        $this->accounts = $accounts;
        $this->company = $company;
        $this->contributionProduct = $contributionProduct;
        $this->status = $status;
        $this->asOfDate = $asOfDate;
    }

    public function view(): View
    {
        return view('reports.contributions.contribution-register-excel', [
            'accounts' => $this->accounts,
            'company' => $this->company,
            'contributionProduct' => $this->contributionProduct,
            'status' => $this->status,
            'asOfDate' => $this->asOfDate,
            'totalBalance' => $this->accounts->sum('balance'),
            'totalDeposits' => $this->accounts->sum('total_deposits'),
            'totalWithdrawals' => $this->accounts->sum('total_withdrawals'),
            'totalTransfers' => $this->accounts->sum('total_transfers'),
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [
            // Title row
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '28a745'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Company name row
            2 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // Generated date row
            3 => [
                'font' => [
                    'italic' => true,
                    'size' => 10,
                    'color' => ['rgb' => '666666'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // Dashboard header
            5 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '28a745'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];

        return $styles;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                // Merge cells for title
                $sheet->mergeCells('A1:' . $highestColumn . '1');
                $sheet->mergeCells('A2:' . $highestColumn . '2');
                $sheet->mergeCells('A3:' . $highestColumn . '3');
                
                // Dashboard section styling (rows 5-11)
                $dashboardStartRow = 5;
                $dashboardEndRow = 11;
                
                // Dashboard header
                $sheet->mergeCells('A5:' . $highestColumn . '5');
                
                // Apply black borders to all dashboard cells
                for ($row = 6; $row <= $dashboardEndRow; $row++) {
                    // Label columns (A-B)
                    $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'E8F5E9'],
                        ],
                        'font' => ['bold' => true, 'size' => 11],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THICK,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                    
                    // Value columns (C-D)
                    $sheet->getStyle('C' . $row . ':D' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFFFFF'],
                        ],
                        'font' => ['bold' => true, 'size' => 12],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THICK,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                    
                    // Label columns (E-F)
                    $sheet->getStyle('E' . $row . ':F' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'E8F5E9'],
                        ],
                        'font' => ['bold' => true, 'size' => 11],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THICK,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                    
                    // Value columns (G-H)
                    $sheet->getStyle('G' . $row . ':H' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFFFFF'],
                        ],
                        'font' => ['bold' => true, 'size' => 12],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THICK,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                }
                
                // Find data table header row (should be around row 13)
                $headerRow = 13;
                
                // Style data table headers
                $sheet->getStyle('A' . $headerRow . ':' . $highestColumn . $headerRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '28a745'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                // Apply alternating row colors for data with black borders
                for ($row = $headerRow + 1; $row < $highestRow; $row++) {
                    $fillColor = ($row % 2 == 0) ? 'F8F9FA' : 'FFFFFF';
                    $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $fillColor],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                }
                
                // Style total row (last row)
                $sheet->getStyle('A' . $highestRow . ':' . $highestColumn . $highestRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFC107'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                // Set row heights
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(5)->setRowHeight(20);
                
                // Freeze panes at header row
                $sheet->freezePane('A' . ($headerRow + 1));
            },
        ];
    }
}
