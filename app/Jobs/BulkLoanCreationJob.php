<?php

namespace App\Jobs;

use App\Models\CashCollateral;
use App\Models\Customer;
use App\Models\GlTransaction;
use App\Models\Journal;
use App\Models\JournalItem;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\LoanSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkLoanCreationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $loanData;
    protected $validated;
    protected $userId;
    protected $batchId;
    protected $chunkSize = 25;

    /**
     * Create a new job instance.
     */
    public function __construct($loanData, $validated, $userId, $batchId = null)
    {
        $this->loanData = $loanData;
        $this->validated = $validated;
        $this->userId = $userId;
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting bulk loan creation job', [
            'total_loans' => count($this->loanData),
            'user_id' => $this->userId,
            'batch_id' => $this->batchId
        ]);

        $product = LoanProduct::with('principalReceivableAccount')->findOrFail($this->validated['product_id']);
        $chartAccountId = $this->validated['chart_account_id'];

        $createdLoans = [];
        $failedLoans = [];
        $repaymentData = [];
        $processed = 0;
        $total = count($this->loanData);

        // Process loans in chunks
        $chunks = array_chunk($this->loanData, $this->chunkSize);

        foreach ($chunks as $chunkIndex => $chunk) {
            Log::info("Processing chunk {$chunkIndex}", ['chunk_size' => count($chunk)]);

            foreach ($chunk as $rowIndex => $row) {
                $absoluteRowIndex = ($chunkIndex * $this->chunkSize) + $rowIndex + 1;
                
                try {
                    $loanData = $this->processLoanRow($row, $product, $chartAccountId, $absoluteRowIndex);

                    if ($loanData) {
                        $loan = $this->createLoan($loanData, $product, $chartAccountId);
                        if ($loan) {
                            $createdLoans[] = $loan;

                            // Collect repayment data if amount_paid > 0
                            if ($loanData['amount_paid'] > 0) {
                                $repaymentData[] = [
                                    'loan_id' => $loan->id,
                                    'amount' => $loanData['amount_paid'],
                                    'payment_date' => $loanData['date_applied']
                                ];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $failedLoan = [
                        'row' => $absoluteRowIndex + 1, // +1 for header row
                        'customer_no' => $row['customer_no'] ?? 'Unknown',
                        'customer_name' => $row['customer_name'] ?? 'Unknown',
                        'amount' => $row['amount'] ?? '',
                        'interest' => $row['interest'] ?? '',
                        'period' => $row['period'] ?? '',
                        'interest_cycle' => $row['interest_cycle'] ?? '',
                        'date_applied' => $row['date_applied'] ?? '',
                        'sector' => $row['sector'] ?? '',
                        'loan_officer_id' => $row['loan_officer_id'] ?? '',
                        'amount_paid' => $row['amount_paid'] ?? '',
                        'error' => $e->getMessage()
                    ];
                    $failedLoans[] = $failedLoan;
                    
                    Log::error('Failed to create loan', [
                        'row' => $absoluteRowIndex,
                        'customer_no' => $row['customer_no'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ]);
                }
                
                $processed++;
                
                // Update progress in cache
                $this->updateProgress($processed, $total, count($createdLoans), count($failedLoans), $failedLoans);
            }
        }

        Log::info('Bulk loan creation completed', [
            'created_loans' => count($createdLoans),
            'failed_loans' => count($failedLoans),
            'batch_id' => $this->batchId
        ]);

        // Update final status
        $this->updateProgress($processed, $total, count($createdLoans), count($failedLoans), $failedLoans, 'completed');

        // Dispatch repayment job if there are repayments to process
        if (!empty($repaymentData)) {
            Log::info('Dispatching bulk repayment job', ['repayment_count' => count($repaymentData)]);
            BulkRepaymentJob::dispatch($repaymentData, $this->userId, $chartAccountId);
        }
    }

    /**
     * Update progress in cache
     */
    private function updateProgress($processed, $total, $created, $failed, $failedLoans, $status = 'processing')
    {
        if (!$this->batchId) return;
        
        Cache::put($this->batchId, [
            'total' => $total,
            'processed' => $processed,
            'created' => $created,
            'failed' => $failed,
            'percentage' => $total > 0 ? round(($processed / $total) * 100, 1) : 0,
            'status' => $status,
            'started_at' => Cache::get($this->batchId)['started_at'] ?? now()->toDateTimeString(),
            'completed_at' => $status === 'completed' ? now()->toDateTimeString() : null,
            'failed_loans' => $failedLoans,
        ], 3600);
    }

    /**
     * Process a single loan row (now handles associative array from Excel)
     */
    private function processLoanRow($row, $product, $chartAccountId, $rowIndex)
    {
        // Handle both associative array (from Excel) and indexed array (legacy CSV)
        if (isset($row['customer_no'])) {
            // Associative array from Excel
            $data = [
                'customer_no' => $row['customer_no'] ?? '',
                'customer_name' => $row['customer_name'] ?? '',
                'group_id' => $row['group_id'] ?? '',
                'group_name' => $row['group_name'] ?? '',
                'amount' => floatval($row['amount'] ?? 0),
                'interest' => floatval($row['interest'] ?? 0),
                'period' => intval($row['period'] ?? 0),
                'interest_cycle' => $row['interest_cycle'] ?? $product->interest_cycle ?? 'monthly',
                'date_applied' => $row['date_applied'] ?? date('Y-m-d'),
                'sector' => $row['sector'] ?? 'Agriculture',
                'loan_officer_id' => $this->extractLoanOfficerId($row['loan_officer_id'] ?? ''),
                'amount_paid' => floatval($row['amount_paid'] ?? 0)
            ];
        } else {
            // Indexed array from legacy CSV
            $data = [
                'customer_no' => $row[0] ?? '',
                'customer_name' => $row[1] ?? '',
                'group_id' => $row[2] ?? '',
                'group_name' => $row[3] ?? '',
                'amount' => floatval($row[4] ?? 0),
                'interest' => floatval($row[5] ?? 0),
                'period' => intval($row[6] ?? 0),
                'interest_cycle' => $row[7] ?? $product->interest_cycle ?? 'monthly',
                'date_applied' => $row[8] ?? date('Y-m-d'),
                'sector' => $row[9] ?? 'Agriculture',
                'loan_officer_id' => $this->extractLoanOfficerId($row[10] ?? ''),
                'amount_paid' => floatval($row[11] ?? 0)
            ];
        }

        // Validate required fields
        if (empty($data['customer_no'])) {
            throw new \Exception('Customer number is required');
        }
        
        if ($data['amount'] <= 0) {
            throw new \Exception('Loan amount must be greater than 0');
        }
        
        if ($data['interest'] <= 0) {
            throw new \Exception('Interest rate must be greater than 0');
        }
        
        if ($data['period'] <= 0) {
            throw new \Exception('Loan period must be greater than 0');
        }

        // Find customer by customer number
        $customer = Customer::where('customerNo', $data['customer_no'])->first();
        if (!$customer) {
            throw new \Exception("Customer not found: {$data['customer_no']} - {$data['customer_name']}");
        }

        // Validate product limits
        if (!$product->isAmountWithinLimits($data['amount'])) {
            $min = number_format($product->minimum_principal, 2);
            $max = number_format($product->maximum_principal, 2);
            throw new \Exception("Loan amount {$data['amount']} is outside product limits ({$min} - {$max})");
        }

        // Check if customer has reached maximum number of loans for this product
        if ($product->hasReachedMaxLoans($customer->id)) {
            $remainingLoans = $product->getRemainingLoans($customer->id);
            $maxLoans = $product->maximum_number_of_loans;
            throw new \Exception("Customer has reached the maximum number of loans ({$maxLoans}) for this product. Remaining loans allowed: {$remainingLoans}");
        }

        // Check collateral if required
        if ($product->requiresCollateral()) {
            $requiredCollateral = $product->calculateRequiredCollateral($data['amount']);
            $availableCollateral = CashCollateral::getCashCollateralBalance($customer->id);

            if ($availableCollateral < $requiredCollateral) {
                throw new \Exception("Insufficient collateral. Required: " . number_format($requiredCollateral, 2) . ", Available: " . number_format($availableCollateral, 2));
            }
        }

        // Use provided loan officer or default to current user
        $loanOfficerId = $data['loan_officer_id'] ?: $this->userId;
        
        // Validate loan officer exists
        if ($loanOfficerId && !User::find($loanOfficerId)) {
            $loanOfficerId = $this->userId;
        }

        return array_merge($data, [
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'branch_id' => $this->validated['branch_id'],
            'chart_account_id' => $chartAccountId,
            'loan_officer_id' => $loanOfficerId
        ]);
    }

    /**
     * Extract loan officer ID from dropdown value (format: "ID - Name")
     */
    private function extractLoanOfficerId($value)
    {
        if (empty($value)) return null;
        
        // If it's just a number, return it
        if (is_numeric($value)) {
            return intval($value);
        }
        
        // Extract ID from format "ID - Name"
        if (preg_match('/^(\d+)\s*-/', $value, $matches)) {
            return intval($matches[1]);
        }
        
        return null;
    }

    /**
     * Create a single loan with accrued interest calculation for backdated loans
     */
    private function createLoan($loanData, $product, $chartAccountId)
    {
        return DB::transaction(function () use ($loanData, $product, $chartAccountId) {
            // Create loan
            $loan = Loan::create([
                'product_id' => $loanData['product_id'],
                'period' => $loanData['period'],
                'interest' => $loanData['interest'],
                'amount' => $loanData['amount'],
                'customer_id' => $loanData['customer_id'],
                'group_id' => $loanData['group_id'] ?: null,
                'bank_account_id' => null,
                'date_applied' => $loanData['date_applied'],
                'disbursed_on' => $loanData['date_applied'],
                'sector' => $loanData['sector'],
                'branch_id' => $loanData['branch_id'],
                'status' => 'active',
                'interest_cycle' => $loanData['interest_cycle'],
                'loan_officer_id' => $loanData['loan_officer_id'],
            ]);

            // Calculate interest and repayment dates
            $interestAmount = $loan->calculateInterestAmount($loanData['interest']);
            $repaymentDates = $loan->getRepaymentDates();

            // Update loan with totals and schedule
            $loan->update([
                'interest_amount' => $interestAmount,
                'amount_total' => $loan->amount + $interestAmount,
                'first_repayment_date' => $repaymentDates['first_repayment_date'],
                'last_repayment_date' => $repaymentDates['last_repayment_date'],
            ]);

            // Generate repayment schedule
            $loan->generateRepaymentSchedule($loanData['interest']);

            // Refresh loan to ensure all relationships are loaded
            $loan->refresh();
            $loan->load(['product', 'customer', 'branch', 'repayments']);

            // Calculate accrued interest for backdated loans
            $accruedInterest = $this->calculateAccruedInterest($loan, $loanData['date_applied']);
            if ($accruedInterest > 0) {
                $loan->update(['accrued_interest' => $accruedInterest]);
                Log::info("Accrued interest calculated for backdated loan", [
                    'loan_id' => $loan->id,
                    'accrued_interest' => $accruedInterest,
                    'disbursed_on' => $loanData['date_applied']
                ]);
            }

            // Calculate daily interest accruals for backdated loans
            // This calculates interest for each day from disbursement_date + 1 until yesterday
            $accrualsCreated = $loan->calculateBackdatedDailyInterest();
            Log::info("Backdated daily interest calculation completed", [
                'loan_id' => $loan->id,
                'loan_no' => $loan->loanNo,
                'disbursed_on' => $loanData['date_applied'],
                'accruals_created' => $accrualsCreated
            ]);

            // Post matured interest for past loans
            $loan->postMaturedInterestForPastLoan();

            // Create journal entry for loan disbursement
            $notes = "Being disbursement for loan of {$product->name}, paid to {$loan->customer->name}, TSHS.{$loanData['amount']}";
            $principalReceivable = $product->principal_receivable_account_id;

            if (!$principalReceivable) {
                throw new \Exception('Principal receivable account not set for this loan product.');
            }

            $disbursementAmount = $loanData['amount'];

            // Generate a unique reference for the journal
            $nextId = Journal::max('id') + 1;
            $reference = 'JRN-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

            $journal = Journal::create([
                'date' => $loanData['date_applied'],
                'description' => $notes,
                'branch_id' => $loanData['branch_id'],
                'user_id' => $this->userId,
                'reference_type' => 'Loan Disbursement',
                'reference' => $loan->id,
            ]);

            // Create journal items
            JournalItem::create([
                'journal_id' => $journal->id,
                'chart_account_id' => $chartAccountId,
                'amount' => $disbursementAmount,
                'nature' => 'credit',
                'description' => $notes,
            ]);

            JournalItem::create([
                'journal_id' => $journal->id,
                'chart_account_id' => $principalReceivable,
                'amount' => $loanData['amount'],
                'nature' => 'debit',
                'description' => $notes,
            ]);

            // Create GL transactions
            GlTransaction::insert([
                [
                    'chart_account_id' => $chartAccountId,
                    'customer_id' => $loan->customer_id,
                    'amount' => $disbursementAmount,
                    'nature' => 'credit',
                    'transaction_id' => $loan->id,
                    'transaction_type' => 'Loan Disbursement',
                    'date' => $loanData['date_applied'],
                    'description' => $notes,
                    'branch_id' => $loanData['branch_id'],
                    'user_id' => $this->userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'chart_account_id' => $principalReceivable,
                    'customer_id' => $loan->customer_id,
                    'amount' => $loanData['amount'],
                    'nature' => 'debit',
                    'transaction_id' => $loan->id,
                    'transaction_type' => 'Loan Disbursement',
                    'date' => $loanData['date_applied'],
                    'description' => $notes,
                    'branch_id' => $loanData['branch_id'],
                    'user_id' => $this->userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);

            // Post accrued interest to GL if backdated
            if ($accruedInterest > 0) {
                $this->postAccruedInterestToGL($loan, $accruedInterest, $product, $loanData);
            }

            return $loan;
        });
    }

    /**
     * Calculate accrued interest for backdated loans
     */
    private function calculateAccruedInterest(Loan $loan, $disbursedDate)
    {
        $disbursedOn = Carbon::parse($disbursedDate);
        $today = Carbon::now();
        
        // If loan is not backdated, no accrued interest
        if ($disbursedOn->greaterThanOrEqualTo($today)) {
            return 0;
        }
        
        $daysElapsed = $disbursedOn->diffInDays($today);
        
        if ($daysElapsed <= 0) {
            return 0;
        }
        
        $principal = $loan->amount;
        $annualRate = $loan->interest;
        
        // Calculate daily interest rate
        $dailyRate = $annualRate / 365 / 100;
        
        // Calculate accrued interest
        $accruedInterest = $principal * $dailyRate * $daysElapsed;
        
        Log::info("Calculating accrued interest", [
            'loan_id' => $loan->id,
            'disbursed_on' => $disbursedDate,
            'days_elapsed' => $daysElapsed,
            'daily_rate' => $dailyRate,
            'accrued_interest' => $accruedInterest
        ]);
        
        return round($accruedInterest, 2);
    }

    /**
     * Post accrued interest to GL
     */
    private function postAccruedInterestToGL(Loan $loan, $accruedInterest, $product, $loanData)
    {
        $interestReceivableAccount = $product->interest_receivable_account_id;
        $interestIncomeAccount = $product->interest_income_account_id;
        
        if (!$interestReceivableAccount || !$interestIncomeAccount) {
            Log::warning("Interest accounts not configured for product, skipping accrued interest GL posting", [
                'product_id' => $product->id,
                'loan_id' => $loan->id
            ]);
            return;
        }
        
        $notes = "Accrued interest for loan #{$loan->id} - {$loan->customer->name}";
        
        // Create journal entry
        $journal = Journal::create([
            'date' => now()->toDateString(),
            'description' => $notes,
            'branch_id' => $loanData['branch_id'],
            'user_id' => $this->userId,
            'reference_type' => 'Accrued Interest',
            'reference' => $loan->id,
        ]);
        
        // Debit Interest Receivable
        JournalItem::create([
            'journal_id' => $journal->id,
            'chart_account_id' => $interestReceivableAccount,
            'amount' => $accruedInterest,
            'nature' => 'debit',
            'description' => $notes,
        ]);
        
        // Credit Interest Income
        JournalItem::create([
            'journal_id' => $journal->id,
            'chart_account_id' => $interestIncomeAccount,
            'amount' => $accruedInterest,
            'nature' => 'credit',
            'description' => $notes,
        ]);
        
        // GL Transactions
        GlTransaction::insert([
            [
                'chart_account_id' => $interestReceivableAccount,
                'customer_id' => $loan->customer_id,
                'amount' => $accruedInterest,
                'nature' => 'debit',
                'transaction_id' => $loan->id,
                'transaction_type' => 'Accrued Interest',
                'date' => now()->toDateString(),
                'description' => $notes,
                'branch_id' => $loanData['branch_id'],
                'user_id' => $this->userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'chart_account_id' => $interestIncomeAccount,
                'customer_id' => $loan->customer_id,
                'amount' => $accruedInterest,
                'nature' => 'credit',
                'transaction_id' => $loan->id,
                'transaction_type' => 'Accrued Interest',
                'date' => now()->toDateString(),
                'description' => $notes,
                'branch_id' => $loanData['branch_id'],
                'user_id' => $this->userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
        
        Log::info("Posted accrued interest to GL", [
            'loan_id' => $loan->id,
            'accrued_interest' => $accruedInterest
        ]);
    }
}
