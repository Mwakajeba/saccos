<?php

namespace App\Exports;

use App\Models\Customer;
use App\Models\LoanProduct;
use App\Models\Sector;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class LoanOpeningBalanceTemplateExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $productId;
    protected $product;
    protected $sectors;
    protected $interestCycles;
    protected $loanOfficers;
    protected $customerCount;

    public function __construct($productId = null)
    {
        $this->productId = $productId;
        $this->product = $productId ? LoanProduct::find($productId) : null;
        
        // Get sectors
        $this->sectors = Sector::where('status', 'active')->orderBy('name')->pluck('name')->toArray();
        
        // Get interest cycles from product or default list
        $this->interestCycles = [
            'daily',
            'weekly',
            'bi_weekly',
            'semi_monthly',
            'monthly',
            'bi_monthly',
            'quarterly',
            'semi_annually',
            'annually',
            'one_payment_off'
        ];
        
        // Get loan officers (users from current branch)
        $branchId = auth()->user()->branch_id ?? null;
        $this->loanOfficers = User::when($branchId, function($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })->get(['id', 'name'])->map(function($user) {
            return $user->id . ' - ' . $user->name;
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'customer_no',
            'customer_name',
            'group_id',
            'group_name',
            'amount',
            'interest',
            'period',
            'interest_cycle',
            'date_applied',
            'sector',
            'loan_officer_id',
            'amount_paid',
        ];
    }

    public function collection()
    {
        $branchId = auth()->user()->branch_id ?? null;
        
        $query = Customer::with(['groups'])
            ->where('category', 'Borrower')
            ->where('status', 'active');
            
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        
        $customers = $query->orderBy('name')->get();
        $this->customerCount = $customers->count();

        $rows = collect();
        
        // Add instruction row
        $rows->push([
            'DELETE THIS ROW BEFORE UPLOAD',
            'Instructions: Fill amount, interest, period. Select from dropdowns for sector, interest_cycle, loan_officer',
            '', '', '', '', '', '', '', '', '', ''
        ]);

        // Default interest cycle from product
        $defaultCycle = $this->product ? $this->product->interest_cycle : 'monthly';
        $defaultInterest = $this->product ? $this->product->minimum_interest_rate : '';

        foreach ($customers as $customer) {
            $group = $customer->groups->first();
            $rows->push([
                $customer->customerNo,
                $customer->name,
                $group ? $group->id : '',
                $group ? $group->name : '',
                '', // amount - to be filled
                $defaultInterest, // interest - default from product
                '', // period - to be filled
                $defaultCycle, // interest_cycle - default from product
                date('Y-m-d'), // date_applied
                $this->sectors[0] ?? 'Agriculture', // sector - default first
                '', // loan_officer_id - to be filled
                '', // amount_paid - to be filled
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
                'startColor' => ['rgb' => '4472C4'],
            ],
        ]);

        // Style instruction row (row 2)
        $sheet->getStyle('A2:L2')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FF0000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF99'],
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

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                
                // Skip if no data rows
                if ($highestRow < 3) return;
                
                // Create dropdown for Sector (column J, starting from row 3)
                if (!empty($this->sectors)) {
                    $sectorList = '"' . implode(',', $this->sectors) . '"';
                    for ($row = 3; $row <= $highestRow; $row++) {
                        $validation = $sheet->getCell("J{$row}")->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(false);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setShowDropDown(true);
                        $validation->setErrorTitle('Invalid Sector');
                        $validation->setError('Please select a sector from the list.');
                        $validation->setPromptTitle('Select Sector');
                        $validation->setPrompt('Choose a business sector from the dropdown.');
                        $validation->setFormula1($sectorList);
                    }
                }
                
                // Create dropdown for Interest Cycle (column H, starting from row 3)
                if (!empty($this->interestCycles)) {
                    $cycleList = '"' . implode(',', $this->interestCycles) . '"';
                    for ($row = 3; $row <= $highestRow; $row++) {
                        $validation = $sheet->getCell("H{$row}")->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(false);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setShowDropDown(true);
                        $validation->setErrorTitle('Invalid Interest Cycle');
                        $validation->setError('Please select an interest cycle from the list.');
                        $validation->setPromptTitle('Select Interest Cycle');
                        $validation->setPrompt('Choose an interest cycle from the dropdown.');
                        $validation->setFormula1($cycleList);
                    }
                }
                
                // Create dropdown for Loan Officers (column K, starting from row 3)
                if (!empty($this->loanOfficers)) {
                    $officerList = '"' . implode(',', array_slice($this->loanOfficers, 0, 50)) . '"'; // Limit to 50 for Excel compatibility
                    for ($row = 3; $row <= $highestRow; $row++) {
                        $validation = $sheet->getCell("K{$row}")->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(true);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setShowDropDown(true);
                        $validation->setErrorTitle('Invalid Loan Officer');
                        $validation->setError('Please select a loan officer from the list.');
                        $validation->setPromptTitle('Select Loan Officer');
                        $validation->setPrompt('Choose a loan officer from the dropdown (format: ID - Name).');
                        $validation->setFormula1($officerList);
                    }
                }
                
                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(10);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(10);
                $sheet->getColumnDimension('G')->setWidth(10);
                $sheet->getColumnDimension('H')->setWidth(15);
                $sheet->getColumnDimension('I')->setWidth(15);
                $sheet->getColumnDimension('J')->setWidth(20);
                $sheet->getColumnDimension('K')->setWidth(25);
                $sheet->getColumnDimension('L')->setWidth(15);
            },
        ];
    }
}
