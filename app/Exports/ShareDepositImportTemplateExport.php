<?php

namespace App\Exports;

use App\Models\ShareAccount;
use App\Models\BankAccount;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class ShareDepositImportTemplateExport implements FromCollection, WithHeadings, WithStyles, WithEvents, ShouldAutoSize
{
    protected $shareProductId;
    protected $bankAccountNames;

    public function __construct($shareProductId = null)
    {
        $this->shareProductId = $shareProductId;
        // Get bank account names for dropdown
        $this->bankAccountNames = BankAccount::orderBy('name')->pluck('name')->toArray();
    }

    public function headings(): array
    {
        return [
            'account_number',
            'customer_name',
            'deposit_date',
            'deposit_amount',
            'bank_account_name',
            'transaction_reference',
            'cheque_number',
            'notes',
        ];
    }

    public function collection()
    {
        $branchId = auth()->user()->branch_id ?? null;
        
        // Get active share accounts
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
        
        // Get bank accounts
        $bankAccounts = BankAccount::orderBy('name')->get();
        
        // Get the first bank account name for template
        $defaultBankAccount = $bankAccounts->first() ? $bankAccounts->first()->name : '';
        $defaultDate = date('Y-m-d');

        $rows = collect();

        // Add share accounts with default values
        foreach ($shareAccounts as $account) {
            $rows->push([
                $account->account_number,
                $account->customer->name ?? '',
                $defaultDate,
                '',
                $defaultBankAccount,
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
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
        ]);

        // Add borders to all cells
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Bank Account Name column is E (5th column)
                // Create dropdown for bank_account_name column (column E)
                if (!empty($this->bankAccountNames)) {
                    $bankAccountList = '"' . implode(',', $this->bankAccountNames) . '"';
                    
                    // Apply validation to all rows (skip header row)
                    for ($row = 2; $row <= $highestRow; $row++) {
                        $cell = 'E' . $row;
                        $validation = $sheet->getCell($cell)->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(DataValidation::STYLE_STOP);
                        $validation->setAllowBlank(true);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setShowDropDown(true);
                        $validation->setFormula1($bankAccountList);
                        $validation->setErrorTitle('Invalid Bank Account');
                        $validation->setError('Please select a valid bank account from the dropdown list.');
                        $validation->setPromptTitle('Select Bank Account');
                        $validation->setPrompt('Please select a bank account from the dropdown list.');
                    }
                }
            },
        ];
    }
}

