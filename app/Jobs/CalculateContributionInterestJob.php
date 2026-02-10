<?php

namespace App\Jobs;

use App\Models\ContributionAccount;
use App\Models\ContributionProduct;
use App\Models\InterestOnSaving;
use App\Models\InterestSavingSummary;
use App\Models\GlTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CalculateContributionInterestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 7200; // 2 hours
    public $maxExceptions = 1;
    public $backoff = [30, 60, 120]; // Retry delays in seconds

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Early exit if job was manually deleted
        if ($this->job && $this->job->isDeleted()) {
            Log::warning('CalculateContributionInterestJob was manually deleted, stopping execution');
            return;
        }

        Log::info('CalculateContributionInterestJob started');

        $today = Carbon::today();
        $calculationDate = $today->toDateString();

        // Get all contribution accounts where product has interest on saving enabled
        $accounts = ContributionAccount::whereHas('contributionProduct', function ($query) {
            $query->where('has_interest_on_saving', true)
                ->where('interest', '>', 0)
                ->where('is_active', true);
        })
            ->where('status', 'active')
            ->with(['contributionProduct', 'customer', 'branch', 'company'])
            ->get();

        Log::info('Found contribution accounts with interest on saving', ['count' => $accounts->count()]);

        $processedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        // Process accounts in chunks
        $accounts->chunk(25)->each(function ($accountChunk) use ($calculationDate, &$processedCount, &$skippedCount, &$errorCount) {
            foreach ($accountChunk as $account) {
                try {
                    $result = $this->calculateAccountInterest($account, $calculationDate);

                    if ($result === 'skipped') {
                        $skippedCount++;
                    } elseif ($result === 'processed') {
                        $processedCount++;
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error('Failed to calculate interest for contribution account', [
                        'account_id' => $account->id,
                        'customer_id' => $account->customer_id,
                        'product_id' => $account->contribution_product_id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        });

        // Create or update summary for the day
        $this->createOrUpdateSummary($calculationDate, $processedCount, $skippedCount, $errorCount);

        Log::info('CalculateContributionInterestJob completed', [
            'processed' => $processedCount,
            'skipped' => $skippedCount,
            'errors' => $errorCount
        ]);
    }

    /**
     * Create or update daily summary
     */
    protected function createOrUpdateSummary(string $calculationDate, int $processedCount, int $skippedCount, int $errorCount): void
    {
        // Get all interest records for this date to calculate totals
        $interestRecords = InterestOnSaving::where('calculation_date', $calculationDate)->get();

        if ($interestRecords->isEmpty()) {
            Log::info('No interest records found for summary', ['date' => $calculationDate]);
            return;
        }

        // Get branch and company from first record (assuming all are from same branch/company for a job run)
        $firstRecord = $interestRecords->first();
        $branchId = $firstRecord->branch_id;
        $companyId = $firstRecord->company_id;

        // Calculate summary totals
        $totalAccounts = $interestRecords->count();
        $totalCustomers = $interestRecords->pluck('customer_id')->unique()->count();
        $totalInterestAmount = $interestRecords->sum('interest_amount_gained');
        $totalWithholdingAmount = $interestRecords->sum('withholding_amount');
        $totalNetAmount = $interestRecords->sum('net_amount');
        $totalBalance = $interestRecords->sum('account_balance_at_interest_calculation');

        $dayOfCalculation = Carbon::parse($calculationDate)->format('l'); // Day name (Monday, Tuesday, etc.)

        // Create or update summary
        InterestSavingSummary::updateOrCreate(
            [
                'calculation_date' => $calculationDate,
                'branch_id' => $branchId,
                'company_id' => $companyId,
            ],
            [
                'day_of_calculation' => $dayOfCalculation,
                'total_accounts' => $totalAccounts,
                'total_customers' => $totalCustomers,
                'total_interest_amount' => $totalInterestAmount,
                'total_withholding_amount' => $totalWithholdingAmount,
                'total_net_amount' => $totalNetAmount,
                'total_balance' => $totalBalance,
                'processed_count' => $processedCount,
                'skipped_count' => $skippedCount,
                'error_count' => $errorCount,
            ]
        );

        Log::info('Interest saving summary created/updated', [
            'date' => $calculationDate,
            'total_accounts' => $totalAccounts,
            'total_interest' => $totalInterestAmount
        ]);
    }

    /**
     * Calculate interest for a specific contribution account
     *
     * @return string 'processed', 'skipped', or null
     */
    protected function calculateAccountInterest(ContributionAccount $account, string $calculationDate)
    {
        $product = $account->contributionProduct;
        $customer = $account->customer;

        if (!$product || !$customer) {
            Log::warning('Product or customer not found for account', ['account_id' => $account->id]);
            return null;
        }

        // Check if interest already calculated for today (prevent duplicates)
        $existingEntry = InterestOnSaving::where('contribution_account_id', $account->id)
            ->where('calculation_date', $calculationDate)
            ->first();

        if ($existingEntry) {
            Log::info('Interest already calculated for this date, skipping', [
                'account_id' => $account->id,
                'calculation_date' => $calculationDate,
                'existing_entry_id' => $existingEntry->id
            ]);
            return 'skipped';
        }

        // Calculate balance from GL transactions
        $balance = $this->calculateBalanceFromGlTransactions($account, $product);

        Log::info('Account balance check', [
            'account_id' => $account->id,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'product_id' => $product->id,
            'balance' => $balance,
            'min_balance_required' => $product->minimum_balance_for_interest_calculations
        ]);

        // Check if balance meets minimum requirement for interest calculation
        if ($balance < $product->minimum_balance_for_interest_calculations) {
            Log::info('Balance below minimum, skipping', [
                'account_id' => $account->id,
                'balance' => $balance,
                'required' => $product->minimum_balance_for_interest_calculations
            ]);
            return 'skipped';
        }

        // Calculate daily interest based on the interest rate and calculation type
        $interestAmount = $this->calculateInterestAmount($balance, $product);

        if ($interestAmount <= 0) {
            Log::info('No interest to calculate', ['account_id' => $account->id]);
            return 'skipped';
        }

        // Calculate withholding tax
        $withholdingPercentage = $product->withholding_percentage ?? 0;
        $withholdingAmount = 0;

        if ($withholdingPercentage > 0) {
            $withholdingAmount = $interestAmount * ($withholdingPercentage / 100);
        }

        // Calculate net amount (after deducting withholding)
        $netAmount = $interestAmount - $withholdingAmount;

        Log::info('Interest calculated', [
            'account_id' => $account->id,
            'interest_amount' => $interestAmount,
            'balance' => $balance,
            'rate' => $product->interest,
            'withholding_percentage' => $withholdingPercentage,
            'withholding_amount' => $withholdingAmount,
            'net_amount' => $netAmount
        ]);

        // Save to interest_on_saving table
        InterestOnSaving::create([
            'contribution_account_id' => $account->id,
            'contribution_product_id' => $product->id,
            'customer_id' => $customer->id,
            'calculation_date' => $calculationDate,
            'date_of_calculation' => $calculationDate,
            'interest_rate' => $product->interest,
            'interest_amount_gained' => $interestAmount,
            'account_balance_at_interest_calculation' => $balance,
            'withholding_percentage' => $withholdingPercentage,
            'withholding_amount' => $withholdingAmount,
            'net_amount' => $netAmount,
            'description' => "Interest on saving for {$customer->name} - {$product->product_name} - " . Carbon::parse($calculationDate)->format('F d, Y'),
            'posted' => false,
            'reason' => 'waiting for approval',
            'branch_id' => $account->branch_id,
            'company_id' => $account->company_id,
            'created_by' => null, // System job
        ]);

        return 'processed';
    }

    /**
     * Calculate balance from GL transactions
     */
    protected function calculateBalanceFromGlTransactions(ContributionAccount $account, ContributionProduct $product): float
    {
        if (!$product->liability_account_id) {
            Log::warning('Product does not have liability account configured', [
                'product_id' => $product->id,
                'account_id' => $account->id
            ]);
            return 0;
        }

        $branchId = $account->branch_id;

        // Get deposits (credits to liability account for this customer)
        $deposits = GlTransaction::whereIn('transaction_type', ['contribution_deposit', 'journal'])
            ->where('nature', 'credit')
            ->where('chart_account_id', $product->liability_account_id)
            ->where('customer_id', $account->customer_id)
            ->where('branch_id', $branchId)
            ->sum('amount');

        // Get withdrawals (debits to liability account for this customer)
        $withdrawals = GlTransaction::whereIn('transaction_type', ['contribution_withdrawal', 'contribution_transfer', 'journal'])
            ->where('nature', 'debit')
            ->where('chart_account_id', $product->liability_account_id)
            ->where('customer_id', $account->customer_id)
            ->where('branch_id', $branchId)
            ->sum('amount');

        $balance = $deposits - $withdrawals;

        return (float) $balance;
    }

    /**
     * Calculate interest amount based on balance and product settings
     * Converts monthly/annual rates to daily
     */
    protected function calculateInterestAmount(float $balance, ContributionProduct $product): float
    {
        $interestRate = $product->interest; // Annual interest rate percentage
        $calculationType = $product->interest_calculation_type ?? 'Daily';

        // Calculate based on calculation type, converting to daily
        switch ($calculationType) {
            case 'Daily':
                // Daily rate: (annual_rate / 100) / 365
                return ($balance * $interestRate / 100) / 365;

            case 'Monthly':
                // Monthly rate converted to daily: (annual_rate / 12) / 30
                // Assuming the interest field is annual, we convert monthly to daily
                $monthlyRate = $interestRate / 12; // Convert annual to monthly
                return ($balance * $monthlyRate / 100) / 30; // Convert monthly to daily

            case 'Annually':
            default:
                // Annual rate converted to daily: (annual_rate / 100) / 365
                return ($balance * $interestRate / 100) / 365;
        }
    }
}
