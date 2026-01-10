<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ContributionOpeningBalanceImportTemplateExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $contributionProductId;
    protected $openingDate;

    public function __construct($contributionProductId, $openingDate = null)
    {
        $this->contributionProductId = $contributionProductId;
        $this->openingDate = $openingDate ?? date('Y-m-d');
    }

    public function headings(): array
    {
        return [
            'customer_no',
            'customer_name',
            'opening_balance_date',
            'opening_balance_amount',
            'opening_balance_description',
            'transaction_reference',
            'notes',
        ];
    }

    public function collection()
    {
        $user = auth()->user();
        $branchId = $user->branch_id ?? null;
        $companyId = $user->company_id ?? null;
        
        // Get all customers for the branch/company
        $customersQuery = Customer::where('status', 'active')
            ->orderBy('customerNo');
        
        if ($branchId) {
            $customersQuery->where('branch_id', $branchId);
        }
        
        if ($companyId) {
            $customersQuery->where('company_id', $companyId);
        }
        
        $customers = $customersQuery->get();

        $rows = collect();

        // Add customers with default values
        foreach ($customers as $customer) {
            $rows->push([
                $customer->customerNo,
                $customer->name ?? 'N/A',
                $this->openingDate,
                '',
                '',
                '',
                '',
            ]);
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
        ]);

        // Add borders to all cells
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        return [];
    }
}

