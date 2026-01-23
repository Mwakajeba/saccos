<?php

namespace App\Jobs;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class BulkCustomerImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $csvData;
    protected $validated;
    protected $userId;
    protected $branchId;
    protected $companyId;
    protected $chunkSize = 25;
    protected $customerNoCounter;

    /**
     * Create a new job instance.
     */
    public function __construct($csvData, $validated, $userId, $branchId, $companyId)
    {
        $this->csvData = $csvData;
        $this->validated = $validated;
        $this->userId = $userId;
        $this->branchId = $branchId;
        $this->companyId = $companyId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting bulk customer import job', [
            'total_rows' => count($this->csvData),
            'user_id' => $this->userId
        ]);

        // Initialize customer number counter
        $this->customerNoCounter = 100000 + (Customer::max('id') ?? 0);

        $createdCustomers = [];
        $failedCustomers = [];

        // Process customers in chunks
        $chunks = array_chunk($this->csvData, $this->chunkSize);

        foreach ($chunks as $chunkIndex => $chunk) {
            Log::info("Processing chunk {$chunkIndex}", ['chunk_size' => count($chunk)]);

            foreach ($chunk as $rowIndex => $row) {
                try {
                    $customerData = $this->processCustomerRow($row);

                    if ($customerData) {
                        $customer = $this->createCustomer($customerData);
                        if ($customer) {
                            $createdCustomers[] = $customer;

                            // Create share account if selected
                            if (isset($this->validated['has_shares']) && isset($this->validated['share_product_id'])) {
                                $this->createShareAccount($customer);
                            }

                            // Create contribution account if selected
                            if (isset($this->validated['has_contributions']) && isset($this->validated['contribution_product_id'])) {
                                $this->createContributionAccount($customer);
                            }

                            // Assign to individual group
                            $this->assignToGroup($customer);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to create customer in row " . ($rowIndex + 2), [
                        'error' => $e->getMessage(),
                        'row_data' => $row
                    ]);
                    $failedCustomers[] = [
                        'row' => $rowIndex + 2,
                        'error' => $e->getMessage()
                    ];
                }
            }
        }

        Log::info('Bulk customer import job completed', [
            'created' => count($createdCustomers),
            'failed' => count($failedCustomers)
        ]);
    }

    protected function processCustomerRow($row)
    {
        // Validate required fields
        if (empty($row['name']) || empty($row['phone1']) || empty($row['dob']) || empty($row['sex'])) {
            throw new \Exception("Missing required fields: name, phone1, dob, or sex");
        }

        // Validate sex
        if (!in_array(strtoupper($row['sex']), ['M', 'F'])) {
            throw new \Exception("Sex must be M or F");
        }

        // Check for duplicate phone
        $phone1 = $this->formatPhoneNumber(trim($row['phone1']));
        if (Customer::where('phone1', $phone1)->exists()) {
            throw new \Exception("Phone number already exists: {$phone1}");
        }

        return [
            'name' => trim($row['name']),
            'phone1' => $phone1,
            'phone2' => !empty($row['phone2']) ? $this->formatPhoneNumber(trim($row['phone2'])) : null,
            'sex' => strtoupper($row['sex']),
            'dob' => $row['dob'],
            'region_id' => $row['region_id'] ?? null,
            'district_id' => $row['district_id'] ?? null,
            'work' => trim($row['work'] ?? ''),
            'workAddress' => trim($row['workaddress'] ?? ''),
            'idType' => trim($row['idtype'] ?? ''),
            'idNumber' => trim($row['idnumber'] ?? ''),
            'relation' => trim($row['relation'] ?? ''),
            'description' => trim($row['description'] ?? ''),
            'reference' => trim($row['reference'] ?? ''),
            'customerNo' => ++$this->customerNoCounter,
            'password' => Hash::make('1234567890'),
            'branch_id' => $this->branchId,
            'company_id' => $this->companyId,
            'registrar' => $this->userId,
            'dateRegistered' => now()->toDateString(),
            'category' => 'Member',
        ];
    }

    protected function createCustomer($customerData)
    {
        return DB::transaction(function () use ($customerData) {
            return Customer::create($customerData);
        });
    }

    protected function createShareAccount($customer)
    {
        $shareProduct = \App\Models\ShareProduct::find($this->validated['share_product_id']);
        if (!$shareProduct) {
            return;
        }

        $accountNumber = $this->generateShareAccountNumber();
        
        \App\Models\ShareAccount::create([
            'customer_id' => $customer->id,
            'share_product_id' => $this->validated['share_product_id'],
            'account_number' => $accountNumber,
            'share_balance' => 0,
            'nominal_value' => $shareProduct->nominal_price ?? 0,
            'opening_date' => now()->toDateString(),
            'status' => 'active',
            'branch_id' => $this->branchId,
            'company_id' => $this->companyId,
            'created_by' => $this->userId,
            'updated_by' => $this->userId,
        ]);
    }

    protected function createContributionAccount($customer)
    {
        $accountNumber = $this->generateContributionAccountNumber();
        
        \App\Models\ContributionAccount::create([
            'customer_id' => $customer->id,
            'contribution_product_id' => $this->validated['contribution_product_id'],
            'account_number' => $accountNumber,
            'balance' => 0,
            'opening_date' => now()->toDateString(),
            'branch_id' => $this->branchId,
            'company_id' => $this->companyId,
            'created_by' => $this->userId,
            'updated_by' => $this->userId,
        ]);
    }

    protected function assignToGroup($customer)
    {
        $existingMembership = DB::table('group_members')->where('customer_id', $customer->id)->first();
        if (!$existingMembership) {
            DB::table('group_members')->insert([
                'group_id' => 1,
                'customer_id' => $customer->id,
                'status' => 'active',
                'joined_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    protected function formatPhoneNumber($phone)
    {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // If it starts with +255, remove + to get 255
        if (strpos($phone, '+255') === 0) {
            return substr($phone, 1);
        }
        
        // If it starts with 255, return as is
        if (strpos($phone, '255') === 0 && strlen($phone) === 12) {
            return $phone;
        }
        
        // If it starts with 0, replace with 255
        if (strpos($phone, '0') === 0 && strlen($phone) === 10) {
            return '255' . substr($phone, 1);
        }
        
        // If 9 digits, add 255 prefix
        if (strlen($phone) === 9) {
            return '255' . $phone;
        }
        
        // Otherwise, add 255 prefix
        return '255' . $phone;
    }

    protected function generateShareAccountNumber()
    {
        $lastAccount = \App\Models\ShareAccount::orderBy('id', 'desc')->first();
        $lastNumber = $lastAccount ? (int) substr($lastAccount->account_number, -6) : 0;
        return 'SA' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }

    protected function generateContributionAccountNumber()
    {
        $lastAccount = \App\Models\ContributionAccount::orderBy('id', 'desc')->first();
        $lastNumber = $lastAccount ? (int) substr($lastAccount->account_number, -6) : 0;
        return 'CA' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }
}
