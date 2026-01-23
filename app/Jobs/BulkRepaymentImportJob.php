<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkRepaymentImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rows;
    protected $branchId;
    protected $bankAccountId;
    protected $repaymentDate;
    protected $transactionType;
    protected $userId;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct($rows, $branchId, $bankAccountId, $repaymentDate, $transactionType, $userId)
    {
        $this->rows = $rows;
        $this->branchId = $branchId;
        $this->bankAccountId = $bankAccountId;
        $this->repaymentDate = $repaymentDate;
        $this->transactionType = $transactionType;
        $this->userId = $userId;
        
        Log::info('BulkRepaymentImportJob created', [
            'total_rows' => count($rows),
            'branch_id' => $branchId,
            'transaction_type' => $transactionType,
            'user_id' => $userId
        ]);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('BulkRepaymentImportJob started', [
            'total_rows' => count($this->rows),
            'branch_id' => $this->branchId
        ]);

        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        $rowNumber = 1;

        foreach ($this->rows as $row) {
            $rowNumber++;
            
            try {
                $customer = trim($row[0] ?? '');
                $scheduleId = trim($row[1] ?? '');
                $loanId = trim($row[2] ?? '');
                $amount = trim($row[3] ?? '');

                Log::info("Processing row {$rowNumber}", [
                    'customer' => $customer,
                    'schedule_id' => $scheduleId,
                    'loan_id' => $loanId,
                    'amount' => $amount
                ]);

                // Validate required fields
                if (empty($scheduleId)) {
                    throw new \Exception("Schedule ID is required");
                }
                if (empty($loanId)) {
                    throw new \Exception("Loan ID is required");
                }
                if (empty($amount) || !is_numeric($amount) || $amount <= 0) {
                    throw new \Exception("Valid payment amount is required");
                }

                // Process the repayment in a transaction
                DB::transaction(function () use ($scheduleId, $loanId, $amount, $customer, $rowNumber) {
                    $this->processRepayment($scheduleId, $loanId, $amount);
                    Log::info("Row {$rowNumber} processed successfully", [
                        'customer' => $customer,
                        'amount' => $amount
                    ]);
                });

                $successCount++;

            } catch (\Exception $e) {
                $errorCount++;
                $errorMsg = "Row {$rowNumber} ({$customer}): " . $e->getMessage();
                $errors[] = $errorMsg;
                
                Log::error("Error processing row {$rowNumber}", [
                    'customer' => $customer,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        Log::info('BulkRepaymentImportJob completed', [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'errors' => $errors
        ]);

        // Store results in cache or notification
        // You can add notification logic here
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('BulkRepaymentImportJob failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * Process individual repayment
     */
    protected function processRepayment($scheduleId, $loanId, $amount)
    {
        // Find the loan schedule
        $schedule = \App\Models\LoanSchedule::with(['loan.product', 'repayments'])
            ->where('id', $scheduleId)
            ->where('loan_id', $loanId)
            ->first();

        if (!$schedule) {
            throw new \Exception("Schedule not found (ID: {$scheduleId})");
        }

        // Verify loan belongs to the selected branch
        if ($schedule->loan->branch_id != $this->branchId) {
            throw new \Exception("Loan does not belong to selected branch");
        }

        // Check if loan can accept repayments
        if (!in_array($schedule->loan->status, ['disbursed', 'partially_paid'])) {
            throw new \Exception("Loan status does not allow repayments (Status: {$schedule->loan->status})");
        }

        // Check if schedule is already fully paid
        if ($schedule->remaining_amount <= 0) {
            throw new \Exception("This schedule is already fully paid");
        }

        // Get loan product for GL accounts
        $loanProduct = $schedule->loan->product;
        if (!$loanProduct) {
            throw new \Exception("Loan product not found");
        }

        // Calculate principal and interest portions
        $totalDue = ($schedule->principal ?? 0) + ($schedule->interest ?? 0);
        $principalPortion = $totalDue > 0 ? ($amount * ($schedule->principal ?? 0) / $totalDue) : 0;
        $interestPortion = $totalDue > 0 ? ($amount * ($schedule->interest ?? 0) / $totalDue) : 0;

        $reference = 'BULK_' . strtoupper(substr($this->transactionType, 0, 3)) . '_' . now()->format('YmdHis') . '_' . $scheduleId;
        
        // Create GL Transaction
        $glTransaction = \App\Models\GlTransaction::create([
            'branch_id' => $this->branchId,
            'transaction_date' => $this->repaymentDate,
            'reference_number' => $reference,
            'description' => 'Bulk loan repayment - ' . $schedule->loan->customer->name,
            'amount' => $amount,
            'created_by' => $this->userId,
            'status' => 'posted'
        ]);

        if ($this->transactionType === 'Receipt') {
            // Create Receipt
            $receipt = \App\Models\Receipt::create([
                'receipt_number' => $reference,
                'branch_id' => $this->branchId,
                'customer_id' => $schedule->loan->customer_id,
                'receipt_date' => $this->repaymentDate,
                'total_amount' => $amount,
                'payment_method' => 'Bank Transfer',
                'bank_account_id' => $this->bankAccountId,
                'notes' => 'Bulk repayment import',
                'gl_transaction_id' => $glTransaction->id,
                'created_by' => $this->userId,
                'status' => 'completed'
            ]);

            // Create Receipt Items
            if ($principalPortion > 0) {
                \App\Models\ReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'debit_account_id' => $this->bankAccountId,
                    'credit_account_id' => $loanProduct->principal_receivables_account_id,
                    'amount' => $principalPortion,
                    'description' => 'Principal repayment'
                ]);
            }

            if ($interestPortion > 0) {
                \App\Models\ReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'debit_account_id' => $this->bankAccountId,
                    'credit_account_id' => $loanProduct->interest_receivables_account_id,
                    'amount' => $interestPortion,
                    'description' => 'Interest repayment'
                ]);
            }

        } else { // Journal
            // Create Journal
            $journal = \App\Models\Journal::create([
                'journal_number' => $reference,
                'branch_id' => $this->branchId,
                'journal_date' => $this->repaymentDate,
                'total_debit' => $amount,
                'total_credit' => $amount,
                'description' => 'Bulk loan repayment - ' . $schedule->loan->customer->name,
                'gl_transaction_id' => $glTransaction->id,
                'created_by' => $this->userId,
                'status' => 'posted'
            ]);

            // Create Journal Items - Debit (Bank Account)
            \App\Models\JournalItem::create([
                'journal_id' => $journal->id,
                'account_id' => $this->bankAccountId,
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Loan repayment received'
            ]);

            // Create Journal Items - Credit (Principal Receivables)
            if ($principalPortion > 0) {
                \App\Models\JournalItem::create([
                    'journal_id' => $journal->id,
                    'account_id' => $loanProduct->principal_receivables_account_id,
                    'debit' => 0,
                    'credit' => $principalPortion,
                    'description' => 'Principal repayment'
                ]);
            }

            // Create Journal Items - Credit (Interest Receivables)
            if ($interestPortion > 0) {
                \App\Models\JournalItem::create([
                    'journal_id' => $journal->id,
                    'account_id' => $loanProduct->interest_receivables_account_id,
                    'debit' => 0,
                    'credit' => $interestPortion,
                    'description' => 'Interest repayment'
                ]);
            }
        }

        // Create Repayment record
        $repayment = \App\Models\Repayment::create([
            'loan_id' => $schedule->loan_id,
            'loan_schedule_id' => $schedule->id,
            'customer_id' => $schedule->loan->customer_id,
            'branch_id' => $this->branchId,
            'payment_date' => $this->repaymentDate,
            'principal' => $principalPortion,
            'interest' => $interestPortion,
            'fee_amount' => 0,
            'penalt_amount' => 0,
            'total_amount' => $amount,
            'payment_method' => $this->transactionType === 'Receipt' ? 'Bank Transfer' : 'Journal Entry',
            'reference_number' => $reference,
            'notes' => 'Bulk repayment import (' . $this->transactionType . ')',
            'bank_account_id' => $this->bankAccountId,
            'gl_transaction_id' => $glTransaction->id,
            'recorded_by' => $this->userId,
            'status' => 'completed'
        ]);

        // Update loan outstanding balance
        $schedule->loan->decrement('outstanding_balance', $amount);
        
        // Update loan status if fully paid
        $loan = $schedule->loan->fresh();
        if ($loan->outstanding_balance <= 0) {
            $loan->update(['status' => 'paid']);
        } elseif ($loan->status === 'disbursed') {
            $loan->update(['status' => 'partially_paid']);
        }
    }
}
