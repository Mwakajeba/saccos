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

class ContributionDepositImportTemplateExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $contributionProductId;

    public function __construct($contributionProductId = null)
    {
        $this->contributionProductId = $contributionProductId;
    }

    public function headings(): array
    {
        return [
            'customer_id',
            'customer_name',
            'amount',
            'date',
            'description',
        ];
    }

    public function collection()
    {
        $user = auth()->user();
        $branchId = $user->branch_id ?? null;
        $companyId = $user->company_id ?? null;

        // Get all customers for the branch/company
        $customersQuery = Customer::where('status', 'active')
            ->orderBy('name');

        if ($branchId) {
            $customersQuery->where('branch_id', $branchId);
        }

        if ($companyId) {
            $customersQuery->where('company_id', $companyId);
        }

        // If product ID is provided, filter customers who have contribution accounts for this product
        if ($this->contributionProductId) {
            $customersQuery->whereHas('contributionAccounts', function ($query) use ($branchId, $companyId) {
                $query->where('contribution_product_id', $this->contributionProductId)
                    ->where('branch_id', $branchId)
                    ->where('company_id', $companyId);
            });
        }

        $customers = $customersQuery->get();

        $rows = collect();

        // Add customers with default values
        foreach ($customers as $customer) {
            $rows->push([
                $customer->id,
                $customer->name ?? 'N/A',
                '', // amount - to be filled by user
                date('Y-m-d'), // date - default to today
                '', // description - optional
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
