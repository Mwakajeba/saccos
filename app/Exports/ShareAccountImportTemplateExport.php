<?php

namespace App\Exports;

use App\Models\Customer;
use App\Models\ShareAccount;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ShareAccountImportTemplateExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
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
            'customer_name',
            'customer_no',
            'opening_date',
            'notes',
        ];
    }

    public function collection()
    {
        $branchId = auth()->user()->branch_id ?? null;
        
        // Get all customers
        $customersQuery = Customer::orderBy('name');
        if ($branchId) {
            $customersQuery->where('branch_id', $branchId);
        }
        $allCustomers = $customersQuery->get(['id', 'name', 'customerNo']);

        // Get customer IDs that already have an account for this share product
        $existingAccountCustomerIds = ShareAccount::where('share_product_id', $this->shareProductId)
            ->pluck('customer_id')
            ->toArray();

        // Filter customers who don't have an account for this product
        $customersWithoutAccount = $allCustomers->reject(function ($customer) use ($existingAccountCustomerIds) {
            return in_array($customer->id, $existingAccountCustomerIds);
        });

        $rows = collect();

        // Add customers
        foreach ($customersWithoutAccount as $customer) {
            $rows->push([
                $customer->name,
                $customer->customerNo,
                $this->openingDate,
                '',
            ]);
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }
}

