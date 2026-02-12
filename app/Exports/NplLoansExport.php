<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class NplLoansExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    protected $loans;
    protected $nplClassification;

    public function __construct($loans, $nplClassification)
    {
        $this->loans = $loans;
        $this->nplClassification = $nplClassification;
    }

    public function collection(): Collection
    {
        $data = [];
        $index = 1;

        foreach ($this->loans as $loan) {
            // Calculate days in arrears - find oldest unpaid schedule
            $oldestUnpaid = $loan->schedule
                ->filter(function ($s) {
                    // Calculate paid amount from repayments relationship
                    $paidAmount = $s->repayments->sum(fn($r) => $r->principal + $r->interest);
                    $totalDue = $s->principal + ($s->accrued_interest ?? $s->interest);
                    return $paidAmount < $totalDue;
                })
                ->filter(function ($s) {
                    return Carbon::parse($s->due_date)->lt(now());
                })
                ->sortBy('due_date')
                ->first();

            $daysInArrears = $oldestUnpaid 
                ? Carbon::parse($oldestUnpaid->due_date)->diffInDays(now()) 
                : 0;

            // Calculate outstanding balance
            $totalPaid = $loan->repayments?->sum(fn($r) => $r->principal + $r->interest) ?? 0;
            $outstandingBalance = $loan->amount_total - $totalPaid;

            // Calculate provision
            $provisionPercentage = (float) $this->nplClassification->provision_percentage;
            $provisionAmount = $outstandingBalance * ($provisionPercentage / 100);

            $data[] = [
                'no' => $index++,
                'loan_number' => $loan->loan_number ?? 'N/A',
                'customer_name' => $loan->customer->name ?? 'N/A',
                'customer_phone' => $loan->customer->phone ?? 'N/A',
                'product' => $loan->product->name ?? 'N/A',
                'loan_amount' => $loan->amount,
                'total_due' => $loan->amount_total,
                'total_paid' => $totalPaid,
                'outstanding_balance' => $outstandingBalance,
                'days_in_arrears' => $daysInArrears,
                'provision_percentage' => $provisionPercentage,
                'provision_amount' => $provisionAmount,
                'status' => ucfirst($loan->status),
                'disbursement_date' => $loan->date_released ? Carbon::parse($loan->date_released)->format('d-m-Y') : 'N/A',
            ];
        }

        // Add totals row
        $totalLoanAmount = collect($data)->sum('loan_amount');
        $totalOutstanding = collect($data)->sum('outstanding_balance');
        $totalProvision = collect($data)->sum('provision_amount');

        $data[] = [
            'no' => '',
            'loan_number' => 'TOTALS',
            'customer_name' => '',
            'customer_phone' => '',
            'product' => '',
            'loan_amount' => $totalLoanAmount,
            'total_due' => collect($data)->sum('total_due'),
            'total_paid' => collect($data)->sum('total_paid'),
            'outstanding_balance' => $totalOutstanding,
            'days_in_arrears' => '',
            'provision_percentage' => '',
            'provision_amount' => $totalProvision,
            'status' => '',
            'disbursement_date' => '',
        ];

        return collect($data);
    }

    public function headings(): array
    {
        return [
            '#',
            'Loan Number',
            'Customer Name',
            'Phone',
            'Product',
            'Loan Amount',
            'Total Due',
            'Total Paid',
            'Outstanding Balance',
            'Days in Arrears',
            'Provision %',
            'Provision Amount',
            'Status',
            'Disbursement Date',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        // Header styling
        $sheet->getStyle('A1:N1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '343A40'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Data rows styling
        $sheet->getStyle('A2:N' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Totals row styling
        $sheet->getStyle('A' . $lastRow . ':N' . $lastRow)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F0F0F0'],
            ],
        ]);

        // Number formatting for currency columns
        $sheet->getStyle('F2:I' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('L2:L' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('K2:K' . $lastRow)->getNumberFormat()->setFormatCode('0.00"%"');

        return [];
    }

    public function title(): string
    {
        return 'NPL Report';
    }
}
