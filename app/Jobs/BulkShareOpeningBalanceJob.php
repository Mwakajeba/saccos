<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\ShareProduct;
use App\Models\ShareAccount;
use App\Models\ShareDeposit;
use App\Models\BankAccount;
use App\Models\ChartAccount;
use App\Models\Journal;
use App\Models\JournalItem;
use App\Models\GlTransaction;
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

class BulkShareOpeningBalanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rows;
    protected $shareProductId;
    protected $bankAccountId;
    protected $openingBalanceDate;
    protected $userId;
    protected $branchId;
    protected $companyId;

    public function __construct($rows, $shareProductId, $bankAccountId, $openingBalanceDate, $userId)
    {
        $this->rows = $rows;
        $this->shareProductId = $shareProductId;
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

        // Get share product
        $shareProduct = ShareProduct::findOrFail($this->shareProductId);
        
        if (!$shareProduct->liability_account_id) {
            Log::error('Share product does not have a liability account configured');
            return;
        }

        // Get bank account
        $bankAccount = BankAccount::findOrFail($this->bankAccountId);

        // Get SHARES opening balance account from settings
        $sharesOpeningBalanceAccountId = SystemSetting::getValue('shares_opening_balance_account_id', null);
        
        if (!$sharesOpeningBalanceAccountId) {
            Log::error('SHARES opening balance account is not configured in settings');
            return;
        }

        // Process in chunks to avoid memory issues
        $chunks = array_chunk($this->rows, 25);

        foreach ($chunks as $chunk) {
            DB::transaction(function () use ($chunk, $shareProduct, $bankAccount, $sharesOpeningBalanceAccountId, &$successCount, &$errorCount, &$errors) {
                foreach ($chunk as $index => $row) {
                    try {
                        // Get values from row
                        $accountNumber = trim($row['account_number'] ?? '');
                        $openingBalanceDate = isset($row['opening_balance_date']) && !empty(trim($row['opening_balance_date'])) 
                            ? trim($row['opening_balance_date']) 
                            : $this->openingBalanceDate;
                        $openingBalanceAmount = trim($row['opening_balance_amount'] ?? '');
                        $openingBalanceDescription = trim($row['opening_balance_description'] ?? '');
                        $transactionReference = trim($row['transaction_reference'] ?? '');
                        $notes = trim($row['notes'] ?? '');

                        // Skip empty rows
                        if (empty($accountNumber) || empty($openingBalanceAmount)) {
                            continue;
                        }

                        // Find share account by account number
                        $shareAccount = ShareAccount::where('account_number', $accountNumber)
                            ->where('branch_id', $this->branchId)
                            ->where('company_id', $this->companyId)
                            ->with('shareProduct')
                            ->first();

                        if (!$shareAccount) {
                            $errorCount++;
                            $errors[] = "Row " . ($index + 1) . ": Share account with number '{$accountNumber}' not found";
                            continue;
                        }

                        // Validate amount
                        $amount = (float) $openingBalanceAmount;
                        if ($amount <= 0) {
                            $errorCount++;
                            $errors[] = "Row " . ($index + 1) . ": Opening balance amount must be greater than 0";
                            continue;
                        }

                        // Validate opening balance amount against product constraints
                        if ($shareProduct->minimum_purchase_amount && $amount < $shareProduct->minimum_purchase_amount) {
                            $errorCount++;
                            $errors[] = "Row " . ($index + 1) . ": Opening balance amount must be at least " . number_format($shareProduct->minimum_purchase_amount, 2);
                            continue;
                        }

                        if ($shareProduct->maximum_purchase_amount && $amount > $shareProduct->maximum_purchase_amount) {
                            $errorCount++;
                            $errors[] = "Row " . ($index + 1) . ": Opening balance amount must not exceed " . number_format($shareProduct->maximum_purchase_amount, 2);
                            continue;
                        }

                        // Calculate number of shares
                        $nominalPrice = $shareProduct->nominal_price ?? 1;
                        if ($nominalPrice <= 0) {
                            $errorCount++;
                            $errors[] = "Row " . ($index + 1) . ": Share product nominal price is not set or is zero";
                            continue;
                        }
                        $numberOfShares = $amount / $nominalPrice;

                        // Calculate charge amount if product has charges
                        $chargeAmount = 0;
                        if ($shareProduct->has_charges && $shareProduct->charge_amount) {
                            if ($shareProduct->charge_type === 'fixed') {
                                $chargeAmount = $shareProduct->charge_amount;
                            } elseif ($shareProduct->charge_type === 'percentage') {
                                $chargeAmount = ($amount * $shareProduct->charge_amount) / 100;
                            }
                        }

                        $totalAmount = $amount + $chargeAmount;

                        // Get liability account (use share_capital_account_id if available, otherwise liability_account_id)
                        $liabilityAccountId = $shareProduct->share_capital_account_id ?? $shareProduct->liability_account_id;
                        
                        if (!$liabilityAccountId) {
                            $errorCount++;
                            $errors[] = "Row " . ($index + 1) . ": Share product does not have a liability account configured";
                            continue;
                        }

                        // Generate a unique reference for the journal
                        $nextId = Journal::max('id') + 1;
                        $journalReferenceCode = 'JRN-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

                        // Create Journal
                        $journal = Journal::create([
                            'date' => $openingBalanceDate,
                            'description' => $openingBalanceDescription ?: ($notes ?: "Opening balance for {$shareProduct->share_name} - {$shareAccount->customer->name}"),
                            'reference' => $transactionReference ?: $journalReferenceCode,
                            'reference_type' => 'Share Opening Balance',
                            'customer_id' => $shareAccount->customer_id,
                            'branch_id' => $this->branchId,
                            'user_id' => $this->userId,
                        ]);

                        // Create Journal Items
                        // Debit: Bank Account
                        JournalItem::create([
                            'journal_id' => $journal->id,
                            'chart_account_id' => $bankAccount->chart_account_id,
                            'amount' => $totalAmount,
                            'nature' => 'debit',
                            'description' => $openingBalanceDescription ?: ($notes ?: "Opening balance deposit - {$shareAccount->customer->name}"),
                        ]);

                        // Credit: Liability Account (Share Product)
                        JournalItem::create([
                            'journal_id' => $journal->id,
                            'chart_account_id' => $liabilityAccountId,
                            'amount' => $totalAmount,
                            'nature' => 'credit',
                            'description' => $openingBalanceDescription ?: ($notes ?: "Opening balance - {$shareProduct->share_name} - {$shareAccount->customer->name}"),
                        ]);

                        // Create GL Transactions
                        $glDescription = $openingBalanceDescription ?: ($notes ?: "Opening balance - {$shareProduct->share_name} - {$shareAccount->customer->name}");

                        // Debit: Bank Account
                        GlTransaction::create([
                            'chart_account_id' => $bankAccount->chart_account_id,
                            'customer_id' => $shareAccount->customer_id,
                            'amount' => $totalAmount,
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
                            'chart_account_id' => $liabilityAccountId,
                            'customer_id' => $shareAccount->customer_id,
                            'amount' => $totalAmount,
                            'nature' => 'credit',
                            'transaction_id' => $journal->id,
                            'transaction_type' => 'journal',
                            'date' => $openingBalanceDate,
                            'description' => $glDescription,
                            'branch_id' => $this->branchId,
                            'user_id' => $this->userId,
                        ]);

                        // Create share deposit record
                        $deposit = ShareDeposit::create([
                            'share_account_id' => $shareAccount->id,
                            'deposit_date' => $openingBalanceDate,
                            'deposit_amount' => $amount,
                            'number_of_shares' => $numberOfShares,
                            'charge_amount' => $chargeAmount,
                            'total_amount' => $totalAmount,
                            'transaction_reference' => $transactionReference ?: 'Opening Balance',
                            'bank_account_id' => $this->bankAccountId,
                            'liability_account_id' => $liabilityAccountId,
                            'share_capital_account_id' => $shareProduct->share_capital_account_id,
                            'cheque_number' => null,
                            'notes' => $openingBalanceDescription ?: ($notes ?: 'Opening Balance Import'),
                            'status' => 'approved',
                            'branch_id' => $this->branchId,
                            'company_id' => $this->companyId,
                            'created_by' => $this->userId,
                            'updated_by' => $this->userId,
                        ]);

                        // Update share account balance
                        $shareAccount->share_balance += $numberOfShares;
                        $shareAccount->last_transaction_date = Carbon::parse($openingBalanceDate);
                        $shareAccount->updated_by = $this->userId;
                        $shareAccount->save();

                        // Create opening balance log
                        OpeningBalanceLog::create([
                            'type' => 'share',
                            'customer_id' => $shareAccount->customer_id,
                            'contribution_account_id' => null,
                            'contribution_product_id' => null, // Not needed for share type
                            'share_account_id' => $shareAccount->id,
                            'share_product_id' => $this->shareProductId,
                            'amount' => $totalAmount,
                            'date' => $openingBalanceDate,
                            'description' => $openingBalanceDescription ?: ($notes ?: "Opening balance for {$shareProduct->share_name}"),
                            'transaction_reference' => $transactionReference ?: $journal->reference,
                            'receipt_id' => null,
                            'journal_id' => $journal->id,
                            'share_deposit_id' => $deposit->id,
                            'branch_id' => $this->branchId,
                            'user_id' => $this->userId,
                        ]);

                        $successCount++;
                    } catch (\Exception $e) {
                        $errorCount++;
                        $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                        Log::error('Share Opening Balance Import Error: ' . $e->getMessage(), [
                            'row' => $row,
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
            });
        }

        Log::info("Share Opening Balance Import Completed", [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'errors' => $errors
        ]);
    }
}

