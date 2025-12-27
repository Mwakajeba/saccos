<?php

namespace App\Exports;

use App\Models\ShareAccount;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ShareOpeningBalanceImportTemplateExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $shareProductId;
    protected $openingDate;

    public function __construct($shareProductId, $openingDate = null)
    {
        $this->shareProductId = $shareProductId;
        $this->openingDate = $openingDate ?? date('Y-m-d');
    }

    public function headings(): array
    {
        return [
            'account_number',
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
        $branchId = auth()->user()->branch_id ?? null;
        
        // Get active share accounts for the selected share product
        $shareAccountsQuery = ShareAccount::with(['customer', 'shareProduct'])
            ->where('status', 'active')
            ->orderBy('account_number');
        
        // Filter by share product if provided
        if ($this->shareProductId) {
            $shareAccountsQuery->where('share_product_id', $this->shareProductId);
        }
            
        if ($branchId) {
            $shareAccountsQuery->where('branch_id', $branchId);
        }
        
        $shareAccounts = $shareAccountsQuery->get();

        $rows = collect();

        // Add share accounts with default values
        foreach ($shareAccounts as $account) {
            $rows->push([
                $account->account_number,
                $account->customer->name ?? 'N/A',
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

