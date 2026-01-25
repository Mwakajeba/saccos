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

class ContributionMemberLedgerExport implements FromView, ShouldAutoSize, WithStyles, WithEvents
{
    protected $account;
    protected $transactions;
    protected $company;
    protected $startDate;
    protected $endDate;

    public function __construct($account, $transactions, $company, $startDate = null, $endDate = null)
    {
        $this->account = $account;
        $this->transactions = $transactions;
        $this->company = $company;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        return view('reports.contributions.member-ledger-excel', [
            'account' => $this->account,
            'transactions' => $this->transactions,
            'company' => $this->company,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'totalDeposits' => $this->transactions->where('transaction_type', 'deposit')->sum('amount'),
            'totalWithdrawals' => $this->transactions->where('transaction_type', 'withdrawal')->sum('amount'),
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
            // Account info header
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
                
                // Dashboard header
                $sheet->mergeCells('A5:' . $highestColumn . '5');
                
                // Account info section styling (6-12)
                for ($row = 6; $row <= 12; $row++) {
                    // Label columns
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
                    
                    // Value columns
                    $sheet->getStyle('C' . $row . ':D' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFFFFF'],
                        ],
                        'font' => ['bold' => true, 'size' => 11],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THICK,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                    
                    // Additional label columns
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
                    
                    // Additional value columns
                    $sheet->getStyle('G' . $row . ':H' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFFFFF'],
                        ],
                        'font' => ['bold' => true, 'size' => 11],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THICK,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                }
                
                // Find transaction header row (should be around row 14)
                $headerRow = 14;
                
                // Style transaction table headers
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
                
                // Apply conditional formatting for deposits and withdrawals with black borders
                for ($row = $headerRow + 1; $row < $highestRow - 4; $row++) {
                    $typeCell = $sheet->getCell('C' . $row)->getValue();
                    $fillColor = 'FFFFFF';
                    
                    if (strpos(strtolower($typeCell), 'deposit') !== false) {
                        $fillColor = 'E8F5E9'; // Light green for deposits
                    } elseif (strpos(strtolower($typeCell), 'withdrawal') !== false) {
                        $fillColor = 'FFEBEE'; // Light red for withdrawals
                    } elseif (strpos(strtolower($typeCell), 'transfer') !== false) {
                        $fillColor = 'E3F2FD'; // Light blue for transfers
                    }
                    
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
                
                // Style summary section (last 5 rows)
                $summaryStartRow = $highestRow - 5;
                $sheet->mergeCells('A' . $summaryStartRow . ':' . $highestColumn . $summaryStartRow);
                
                for ($row = $summaryStartRow; $row <= $highestRow; $row++) {
                    $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFF9C4'],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THICK,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                }
                
                // Set row heights
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(5)->setRowHeight(20);
                $sheet->getRowDimension($summaryStartRow)->setRowHeight(20);
                
                // Freeze panes at header row
                $sheet->freezePane('A' . ($headerRow + 1));
            },
        ];
    }
}
