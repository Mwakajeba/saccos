<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\ContributionProduct;
use App\Models\ContributionAccount;
use App\Models\BankAccount;
use App\Models\ChartAccount;
use App\Models\Journal;
use App\Models\JournalItem;
use App\Models\GlTransaction;
use App\Models\Branch;
use App\Models\User;
use App\Models\OpeningBalanceLog;
use App\Models\SystemSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BulkContributionOpeningBalanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rows;
    protected $contributionProductId;
    protected $bankAccountId;
    protected $openingBalanceDate;
    protected $userId;
    protected $branchId;
    protected $companyId;

    public function __construct($rows, $contributionProductId, $bankAccountId, $openingBalanceDate, $userId)
    {
        $this->rows = $rows;
        $this->contributionProductId = $contributionProductId;
        $this->bankAccountId = $bankAccountId;
        $this->openingBalanceDate = $openingBalanceDate;
        $this->userId = $userId;
        
        $user = \App\Models\User::find($userId);
        $this->branchId = $user->branch_id;
        $this->companyId = $user->company_id;
    }

    public function handle()
    {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        // Get contribution product
        $product = ContributionProduct::findOrFail($this->contributionProductId);
        
        if (!$product->liability_account_id) {
            Log::error('Contribution product does not have a liability account configured');
            return;
        }

        // Get bank account
        $bankAccount = BankAccount::findOrFail($this->bankAccountId);

        // Process in chunks to avoid memory issues
        $chunks = array_chunk($this->rows, 25);

        foreach ($chunks as $chunk) {
            DB::transaction(function () use ($chunk, $product, $bankAccount, &$successCount, &$errorCount, &$errors) {
                foreach ($chunk as $index => $row) {
                    try {
                        // Get values from row
                        $customerNo = trim($row['customer_no'] ?? '');
                        $openingBalanceDate = isset($row['opening_balance_date']) && !empty(trim($row['opening_balance_date'])) 
                            ? trim($row['opening_balance_date']) 
                            : $this->openingBalanceDate;
                        $openingBalanceAmount = trim($row['opening_balance_amount'] ?? '');
                        $openingBalanceDescription = trim($row['opening_balance_description'] ?? '');
                        $transactionReference = trim($row['transaction_reference'] ?? '');
                        $notes = trim($row['notes'] ?? '');

                        // Skip empty rows
                        if (empty($customerNo) || empty($openingBalanceAmount)) {
                            continue;
                        }

                        // Find customer
                        $customer = Customer::where('customerNo', $customerNo)
                            ->where('branch_id', $this->branchId)
                            ->where('company_id', $this->companyId)
                            ->first();

                        if (!$customer) {
                            $errorCount++;
                            $errors[] = "Row " . ($index + 1) . ": Customer with number '{$customerNo}' not found";
                            continue;
                        }

                        // Validate amount
                        $amount = (float) $openingBalanceAmount;
                        if ($amount <= 0) {
                            $errorCount++;
                            $errors[] = "Row " . ($index + 1) . ": Opening balance amount must be greater than 0";
                            continue;
                        }

                        // Check if customer has account for this product, if not create it
                        $contributionAccount = ContributionAccount::where('customer_id', $customer->id)
                            ->where('contribution_product_id', $this->contributionProductId)
                            ->where('branch_id', $this->branchId)
                            ->where('company_id', $this->companyId)
                            ->first();

                        if (!$contributionAccount) {
                            // Generate account number
                            $lastAccount = ContributionAccount::where('contribution_product_id', $this->contributionProductId)
                                ->where('branch_id', $this->branchId)
                                ->orderBy('id', 'desc')
                                ->first();
                            
                            $accountNumber = $product->product_name . '-' . str_pad(($lastAccount ? $lastAccount->id + 1 : 1), 6, '0', STR_PAD_LEFT);

                            // Create contribution account
                            $contributionAccount = ContributionAccount::create([
                                'customer_id' => $customer->id,
                                'contribution_product_id' => $this->contributionProductId,
                                'account_number' => $accountNumber,
                                'balance' => 0,
                                'status' => 'active',
                                'branch_id' => $this->branchId,
                                'company_id' => $this->companyId,
                                'opening_date' => Carbon::parse($openingBalanceDate),
                            ]);
                        }

                        // Generate a unique reference for the journal
                        $nextId = Journal::max('id') + 1;
                        $journalReferenceCode = 'JRN-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

                        // Create Journal
                        $journal = Journal::create([
                            'date' => $openingBalanceDate,
                            'description' => $openingBalanceDescription ?: ($notes ?: "Opening balance for {$product->product_name} - {$customer->name}"),
                            'reference' => $transactionReference ?: $journalReferenceCode,
                            'reference_type' => 'Contribution Opening Balance',
                            'customer_id' => $customer->id,
                            'branch_id' => $this->branchId,
                            'user_id' => $this->userId,
                        ]);

                        // Create Journal Items
                        // Debit: Bank Account
                        JournalItem::create([
                            'journal_id' => $journal->id,
                            'chart_account_id' => $bankAccount->chart_account_id,
                            'amount' => $amount,
                            'nature' => 'debit',
                            'description' => $openingBalanceDescription ?: ($notes ?: "Opening balance deposit - {$customer->name}"),
                        ]);

                        // Credit: Liability Account (Contribution Product)
                        JournalItem::create([
                            'journal_id' => $journal->id,
                            'chart_account_id' => $product->liability_account_id,
                            'amount' => $amount,
                            'nature' => 'credit',
                            'description' => $openingBalanceDescription ?: ($notes ?: "Opening balance - {$product->product_name} - {$customer->name}"),
                        ]);

                        // Create GL Transactions
                        $glDescription = $openingBalanceDescription ?: ($notes ?: "Opening balance - {$product->product_name} - {$customer->name}");

                        // Debit: Bank Account
                        GlTransaction::create([
                            'chart_account_id' => $bankAccount->chart_account_id,
                            'customer_id' => $customer->id,
                            'amount' => $amount,
                            'nature' => 'debit',
                            'transaction_id' => $journal->id,
                            'transaction_type' => 'journal',
                            'date' => $openingBalanceDate,
                            'description' => $glDescription,
                            'branch_id' => $this->branchId,
                            'user_id' => $this->userId,
                        ]);

                        // Credit: Liability Account
                        GlTransaction::create([
                            'chart_account_id' => $product->liability_account_id,
                            'customer_id' => $customer->id,
                            'amount' => $amount,
                            'nature' => 'credit',
                            'transaction_id' => $journal->id,
                            'transaction_type' => 'journal',
                            'date' => $openingBalanceDate,
                            'description' => $glDescription,
                            'branch_id' => $this->branchId,
                            'user_id' => $this->userId,
                        ]);

                        // Update contribution account balance
                        $contributionAccount->increment('balance', $amount);

                        // Create opening balance log
                        OpeningBalanceLog::create([
                            'customer_id' => $customer->id,
                            'contribution_account_id' => $contributionAccount->id,
                            'contribution_product_id' => $this->contributionProductId,
                            'amount' => $amount,
                            'date' => $openingBalanceDate,
                            'description' => $openingBalanceDescription ?: ($notes ?: "Opening balance for {$product->product_name}"),
                            'transaction_reference' => $transactionReference ?: $journal->reference,
                            'receipt_id' => null,
                            'journal_id' => $journal->id,
                            'branch_id' => $this->branchId,
                            'user_id' => $this->userId,
                        ]);

                        $successCount++;
                    } catch (\Exception $e) {
                        $errorCount++;
                        $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                        Log::error('Contribution Opening Balance Import Error: ' . $e->getMessage(), [
                            'row' => $row,
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
            });
        }

        Log::info("Contribution Opening Balance Import Completed", [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'errors' => $errors
        ]);
    }
}

