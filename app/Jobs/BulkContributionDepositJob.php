<?php

namespace App\Jobs;

use App\Models\ContributionProduct;
use App\Models\ContributionAccount;
use App\Models\BankAccount;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\GlTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BulkContributionDepositJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes timeout

    protected $rows;
    protected $contributionProductId;
    protected $bankAccountId;
    protected $userId;
    protected $jobId;
    protected $branchId;
    protected $companyId;
    protected $chunkSize = 25;

    public function __construct($rows, $contributionProductId, $bankAccountId, $userId, $jobId)
    {
        $this->rows = $rows;
        $this->contributionProductId = $contributionProductId;
        $this->bankAccountId = $bankAccountId;
        $this->userId = $userId;
        $this->jobId = $jobId;

        $user = \App\Models\User::find($userId);
        $this->branchId = $user->branch_id;
        $this->companyId = $user->company_id;
    }

    public function handle()
    {
        $totalRows = count($this->rows);
        $successCount = 0;
        $failedCount = 0;
        $failedRecords = [];
        $processedCount = 0;

        // Initialize progress in cache
        $this->updateProgress($processedCount, $totalRows, $successCount, $failedCount, 'processing', $failedRecords);

        try {
            // Get contribution product
            $product = ContributionProduct::findOrFail($this->contributionProductId);

            if (!$product->liability_account_id) {
                throw new \Exception('Contribution product does not have a liability account configured.');
            }

            // Get bank account
            $bankAccount = BankAccount::findOrFail($this->bankAccountId);

            // Process in chunks to avoid memory issues
            $chunks = array_chunk($this->rows, $this->chunkSize);

            foreach ($chunks as $chunkIndex => $chunk) {
                DB::transaction(function () use ($chunk, $product, $bankAccount, &$successCount, &$failedCount, &$failedRecords, &$processedCount, $totalRows) {
                    foreach ($chunk as $rowIndex => $row) {
                        $processedCount++;
                        $rowNumber = $processedCount;

                        try {
                            // Validate and get values from row
                            $customerId = isset($row['customer_id']) ? trim($row['customer_id']) : null;
                            $customerName = isset($row['customer_name']) ? trim($row['customer_name']) : '';
                            $amount = isset($row['amount']) ? trim($row['amount']) : null;
                            $date = isset($row['date']) ? trim($row['date']) : null;
                            $description = isset($row['description']) ? trim($row['description']) : null;

                            // Validate required fields
                            if (empty($customerId)) {
                                throw new \Exception('Customer ID is required');
                            }

                            if (empty($amount)) {
                                throw new \Exception('Amount is required');
                            }

                            $amount = (float) $amount;
                            if ($amount <= 0) {
                                throw new \Exception('Amount must be greater than 0');
                            }

                            if (empty($date)) {
                                throw new \Exception('Date is required');
                            }

                            // Validate date format
                            try {
                                $date = \Carbon\Carbon::parse($date)->format('Y-m-d');
                            } catch (\Exception $e) {
                                throw new \Exception('Invalid date format. Use YYYY-MM-DD');
                            }

                            // Find customer
                            $customer = \App\Models\Customer::where('id', $customerId)
                                ->where('branch_id', $this->branchId)
                                ->where('company_id', $this->companyId)
                                ->first();

                            if (!$customer) {
                                throw new \Exception("Customer with ID '{$customerId}' not found");
                            }

                            // Get or create contribution account
                            $contributionAccount = ContributionAccount::where('customer_id', $customerId)
                                ->where('contribution_product_id', $this->contributionProductId)
                                ->where('branch_id', $this->branchId)
                                ->where('company_id', $this->companyId)
                                ->first();

                            if (!$contributionAccount) {
                                throw new \Exception("Contribution account not found for customer ID '{$customerId}' and product ID '{$this->contributionProductId}'");
                            }

                            // Create receipt (for deposit - money coming in)
                            $receipt = Receipt::create([
                                'reference' => 'CD-' . strtoupper(uniqid()),
                                'reference_type' => 'Contribution Deposit',
                                'reference_number' => null,
                                'amount' => $amount,
                                'date' => $date,
                                'description' => $description,
                                'user_id' => $this->userId,
                                'bank_account_id' => $this->bankAccountId,
                                'payee_type' => 'customer',
                                'customer_id' => $customerId,
                                'branch_id' => $this->branchId,
                                'approved' => true, // Auto-approve contribution deposits
                                'approved_by' => $this->userId,
                                'approved_at' => now(),
                            ]);

                            // Create receipt item (liability account)
                            ReceiptItem::create([
                                'receipt_id' => $receipt->id,
                                'chart_account_id' => $product->liability_account_id,
                                'amount' => $amount,
                                'description' => $description ?: "Contribution deposit for {$product->product_name}",
                            ]);

                            // Create GL transactions
                            $glDescription = $description ?: "Contribution deposit - {$product->product_name}";

                            // Debit bank account (money coming in)
                            GlTransaction::create([
                                'chart_account_id' => $bankAccount->chart_account_id,
                                'customer_id' => $customerId,
                                'amount' => $amount,
                                'nature' => 'debit',
                                'transaction_id' => $receipt->id,
                                'transaction_type' => 'contribution_deposit',
                                'date' => $date,
                                'description' => $glDescription,
                                'branch_id' => $this->branchId,
                                'user_id' => $this->userId,
                            ]);

                            // Credit liability account (contribution account increases)
                            GlTransaction::create([
                                'chart_account_id' => $product->liability_account_id,
                                'customer_id' => $customerId,
                                'amount' => $amount,
                                'nature' => 'credit',
                                'transaction_id' => $receipt->id,
                                'transaction_type' => 'contribution_deposit',
                                'date' => $date,
                                'description' => $glDescription,
                                'branch_id' => $this->branchId,
                                'user_id' => $this->userId,
                            ]);

                            // Update contribution account balance
                            $contributionAccount->increment('balance', $amount);

                            $successCount++;
                        } catch (\Exception $e) {
                            $failedCount++;
                            $failedRecords[] = [
                                'customer_id' => $row['customer_id'] ?? '',
                                'customer_name' => $row['customer_name'] ?? '',
                                'amount' => $row['amount'] ?? '',
                                'date' => $row['date'] ?? '',
                                'description' => $row['description'] ?? '',
                                'error_reason' => "Row {$rowNumber}: " . $e->getMessage(),
                            ];
                            Log::error('Bulk Contribution Deposit Error: ' . $e->getMessage(), [
                                'row' => $row,
                                'row_number' => $rowNumber,
                                'trace' => $e->getTraceAsString()
                            ]);
                        }

                        // Update progress after each record
                        $this->updateProgress($processedCount, $totalRows, $successCount, $failedCount, 'processing', $failedRecords);
                    }
                });
            }

            // Mark as completed
            $this->updateProgress($processedCount, $totalRows, $successCount, $failedCount, 'completed', $failedRecords);

            Log::info("Bulk Contribution Deposit Import Completed", [
                'job_id' => $this->jobId,
                'total_rows' => $totalRows,
                'success_count' => $successCount,
                'failed_count' => $failedCount,
            ]);
        } catch (\Exception $e) {
            // Mark as failed
            $this->updateProgress($processedCount, $totalRows, $successCount, $failedCount, 'failed', $failedRecords);
            Log::error("Bulk Contribution Deposit Job Failed: " . $e->getMessage(), [
                'job_id' => $this->jobId,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function updateProgress($processed, $total, $success, $failed, $status, $failedRecords)
    {
        $percentage = $total > 0 ? round(($processed / $total) * 100, 2) : 0;

        $progress = [
            'processed' => $processed,
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'percentage' => $percentage,
            'status' => $status,
        ];

        // Store progress in cache (expire after 1 hour)
        Cache::put("bulk_deposit_progress_{$this->jobId}", $progress, 3600);

        // Store failed records in cache (expire after 1 hour)
        if (!empty($failedRecords)) {
            Cache::put("bulk_deposit_failed_{$this->jobId}", $failedRecords, 3600);
        }
    }
}
