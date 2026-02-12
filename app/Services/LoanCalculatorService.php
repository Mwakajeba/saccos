<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\Fee;
use App\Models\Penalty;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LoanCalculatorService
{
    /**
     * Calculate loan details based on parameters
     */
    public function calculateLoan(array $params): array
    {
        try {
            // Validate inputs
            $this->validateInputs($params);
            
            // Get loan product
            $product = $this->getLoanProduct($params['product_id']);
            
            // Calculate interest
            $interestCalculation = $this->calculateInterest($params, $product);
            
            // Calculate fees
            $fees = $this->calculateFees($params, $product);
            
            // Generate repayment schedule
            $schedule = $this->generateSchedule($params, $product, $interestCalculation, $fees);
            
            // Calculate totals
            $totals = $this->calculateTotals($params, $interestCalculation, $fees, $schedule);
            
            // Calculate penalties (if applicable)
            $penalties = $this->calculatePenalties($params, $product);
            
            return [
                'success' => true,
                'product' => $this->formatProduct($product),
                'interest_calculation' => $interestCalculation,
                'fees' => $fees,
                'penalties' => $penalties,
                'schedule' => $schedule,
                'totals' => $totals,
                'summary' => $this->generateSummary($params, $totals)
            ];
            
        } catch (\Exception $e) {
            Log::error('Loan Calculator Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Compare multiple loan scenarios
     */
    public function compareLoans(array $scenarios): array
    {
        $results = [];
        
        foreach ($scenarios as $index => $scenario) {
            $result = $this->calculateLoan($scenario);
            $results[] = [
                'scenario' => $index + 1,
                'name' => $scenario['name'] ?? "Scenario " . ($index + 1),
                'result' => $result
            ];
        }
        
        return [
            'success' => true,
            'comparisons' => $results,
            'summary' => $this->generateComparisonSummary($results)
        ];
    }
    
    /**
     * Get available loan products for calculator
     */
    public function getAvailableProducts(): array
    {
        $products = LoanProduct::where('is_active', true)->get();
            
        return $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'product_type' => $product->product_type,
                'min_interest_rate' => $product->minimum_interest_rate,
                'max_interest_rate' => $product->maximum_interest_rate,
                'default_interest_rate' => $product->minimum_interest_rate,
                'min_principal' => $product->minimum_principal,
                'max_principal' => $product->maximum_principal,
                'min_period' => $product->minimum_period,
                'max_period' => $product->maximum_period,
                'interest_method' => $product->interest_method,
                'interest_cycle' => $product->interest_cycle,
                'grace_period' => $product->grace_period ?? 0,
                'has_cash_collateral' => $product->has_cash_collateral,
                'cash_collateral_value' => $product->cash_collateral_value,
                'fees_count' => $product->getFeesAttribute()->count(),
                'penalties_count' => $product->penalties()->count()
            ];
        })->toArray();
    }
    
    /**
     * Validate input parameters
     */
    private function validateInputs(array $params): void
    {
        $required = ['product_id', 'amount', 'period', 'interest_rate', 'start_date'];
        
        foreach ($required as $field) {
            if (!isset($params[$field]) || empty($params[$field])) {
                throw new \InvalidArgumentException("Missing required parameter: {$field}");
            }
        }
        
        if ($params['amount'] <= 0) {
            throw new \InvalidArgumentException("Loan amount must be greater than 0");
        }
        
        if ($params['period'] <= 0) {
            throw new \InvalidArgumentException("Loan period must be greater than 0");
        }
        
        if ($params['interest_rate'] < 0) {
            throw new \InvalidArgumentException("Interest rate cannot be negative");
        }
    }
    
    /**
     * Get loan product with validation
     */
    private function getLoanProduct(int $productId): LoanProduct
    {
        $product = LoanProduct::find($productId);
        
        if (!$product) {
            throw new \InvalidArgumentException("Loan product not found");
        }
        
        if (!$product->is_active) {
            throw new \InvalidArgumentException("Loan product is not active");
        }
        
        return $product;
    }
    
    /**
     * Calculate interest using the same core logic as direct loans.
     *
     * This now delegates to the `Loan` model's `calculateInterestAmount`
     * so the calculator shares the exact same function as direct loans
     * for periods, frequency handling and principal/interest breakdown.
     */
    private function calculateInterest(array $params, LoanProduct $product): array
    {
        // Validate against product limits first
        $this->validateProductLimits($params, $product);

        $principal = (float) $params['amount'];
        $annualRate = (float) $params['interest_rate']; // Annual interest rate from product
        $period = (int) $params['period'];
        $method = $product->interest_method;
        $cycle = $params['interest_cycle'] ?? $product->interest_cycle;
        $bulletMonths = ($cycle === 'one_payment_off') ? ($params['bullet_payment_months'] ?? null) : null;

        // Convert annual interest rate to period rate based on interest cycle
        $rate = $this->convertAnnualRateToPeriodRate($annualRate, $cycle, $bulletMonths);

        // Build an in-memory Loan instance to reuse the core calculation logic
        $loan = new Loan();
        $loan->amount = $principal;
        $loan->period = $period;
        $loan->interest_cycle = $cycle;
        // Attach the already-loaded product so the relation is available
        $loan->setRelation('product', $product);

        // Use the same function as direct loans to get schedule rows
        // Pass true for rateAlreadyConverted since we already converted the rate above
        $scheduleRows = $loan->calculateInterestAmount($rate, true, true);

        if (!is_array($scheduleRows) || empty($scheduleRows)) {
            throw new \InvalidArgumentException('Unable to calculate interest schedule for the given parameters.');
        }

        $totalInterest = array_sum(array_column($scheduleRows, 'interest'));
        $totalPayment = array_sum(array_column($scheduleRows, 'total'));
        $installmentCount = count($scheduleRows);

        $monthlyPayment = $installmentCount > 0 ? $totalPayment / $installmentCount : 0;
        $firstRow = $scheduleRows[0];
        $monthlyPrincipal = $firstRow['principal'] ?? 0;
        $monthlyInterest = $firstRow['interest'] ?? 0;

        $base = [
            'method' => $method,
            'total_interest' => round($totalInterest, 2),
            'annual_rate' => $annualRate, // Store original annual rate
            'period_rate' => $rate, // Store converted period rate
            'rate_per_period' => $rate / 100, // Decimal rate per period
            'interest_cycle' => $cycle, // Store the cycle used
            // raw schedule rows returned by Loan::calculateInterestAmount
            'schedule_rows' => $scheduleRows,
        ];

        // Add method-specific summary fields expected by other parts of the service
        switch ($method) {
            case 'flat_rate':
                $base['monthly_interest'] = round($monthlyInterest, 2);
                $base['monthly_principal'] = round($monthlyPrincipal, 2);
                $base['monthly_payment'] = round($monthlyPayment, 2);
                break;

            case 'reducing_balance_with_equal_installment':
                $base['monthly_payment'] = round($monthlyPayment, 2);
                $base['total_payment'] = round($totalPayment, 2);
                break;

            case 'reducing_balance_with_equal_principal':
                $base['monthly_principal'] = round($monthlyPrincipal, 2);
                break;

            default:
                // leave base as-is for any other/custom methods
                break;
        }

        return $base;
    }
    
    /**
     * Calculate fees
     */
    private function calculateFees(array $params, LoanProduct $product): array
    {
        $fees = [];
        $productFees = $product->getFeesAttribute();
        $principal = $params['amount'];
        $period = $params['period'];
        
        foreach ($productFees as $fee) {
            if ($fee->status !== 'active') continue;
            
            $feeAmount = $this->calculateFeeAmount($fee, $principal);
            $feeApplication = $this->determineFeeApplication($fee, $period, $feeAmount);
            
            $fees[] = [
                'fee_id' => $fee->id,
                'name' => $fee->name,
                'type' => $fee->fee_type,
                'amount' => $feeAmount,
                'application' => $feeApplication,
                'criteria' => $fee->deduction_criteria,
                'include_in_schedule' => $fee->include_in_schedule
            ];
        }
        
        return $fees;
    }
    
    /**
     * Calculate fee amount
     */
    private function calculateFeeAmount(Fee $fee, float $principal): float
    {
        if ($fee->fee_type === 'percentage') {
            return round(($principal * $fee->amount) / 100, 2);
        }
        return round($fee->amount, 2);
    }
    
    /**
     * Determine how fee is applied
     */
    private function determineFeeApplication(Fee $fee, int $period, float $computedFeeAmount): array
    {
        $criteria = $fee->deduction_criteria;
        // Use computed monetary amount regardless of type (percentage already applied)
        $totalFee = $computedFeeAmount;
        
        switch ($criteria) {
            case 'distribute_fee_evenly_to_all_repayments':
                return [
                    'type' => 'distributed',
                    'per_installment' => round($totalFee / $period, 2),
                    'total' => $totalFee
                ];
                
            case 'charge_same_fee_to_all_repayments':
                return [
                    'type' => 'per_installment',
                    'per_installment' => $totalFee,
                    'total' => $totalFee * $period
                ];
                
            case 'charge_fee_on_first_repayment':
                return [
                    'type' => 'first_only',
                    'per_installment' => $totalFee,
                    'total' => $totalFee
                ];
                
            case 'charge_fee_on_last_repayment':
                return [
                    'type' => 'last_only',
                    'per_installment' => $totalFee,
                    'total' => $totalFee
                ];
                
            case 'charge_fee_on_release_date':
                return [
                    'type' => 'release_date',
                    'per_installment' => 0,
                    'total' => $totalFee
                ];
                
            default:
                return [
                    'type' => 'not_included',
                    'per_installment' => 0,
                    'total' => 0
                ];
        }
    }
    
    /**
     * Calculate penalties
     */
    private function calculatePenalties(array $params, LoanProduct $product): array
    {
        $penalties = [];
        $productPenalties = $product->penalties();
        
        foreach ($productPenalties as $penalty) {
            if ($penalty->status !== 'active') continue;
            
            $penalties[] = [
                'penalty_id' => $penalty->id,
                'name' => $penalty->name,
                'type' => $penalty->penalty_type,
                'amount' => $penalty->amount,
                'deduction_type' => $penalty->deduction_type,
                'charge_frequency' => $penalty->charge_frequency,
                'description' => $penalty->description
            ];
        }
        
        return $penalties;
    }
    
    /**
     * Generate repayment schedule
     */
    private function generateSchedule(array $params, LoanProduct $product, array $interestCalculation, array $fees): array
    {
        $schedule = [];
        $startDate = Carbon::parse($params['start_date']);
        $period = $params['period'];
        $gracePeriod = $product->grace_period ?? 0;
        $method = $product->interest_method;
        $selectedCycle = $params['interest_cycle'] ?? $product->interest_cycle;
        $bulletMonths = ($selectedCycle === 'one_payment_off')
            ? ($params['bullet_payment_months'] ?? null)
            : null;
        
        // Base schedule rows from direct-loan logic (principal/interest/total per installment)
        $baseRows = $interestCalculation['schedule_rows'] ?? [];

        $remainingBalance = $params['amount'];

        foreach ($baseRows as $index => $row) {
            $installmentNumber = $index + 1;

            $dueDate = $this->calculateDueDate($startDate, $installmentNumber, $selectedCycle, $bulletMonths);
            $endDate = $dueDate->copy()->addDays(5);
            $endGraceDate = $dueDate->copy()->addDays($gracePeriod);

            $principal = round($row['principal'] ?? 0, 2);
            $interest = round($row['interest'] ?? 0, 2);

            // On final installment, adjust principal to clear remaining (protect against rounding drift)
            if ($installmentNumber === $period) {
                $principal = round($remainingBalance, 2);
            }

            // Calculate fees for this installment
            $installmentFees = $this->calculateInstallmentFees($index, $period, $fees, $params['amount']);

            // Update remaining balance
            $newRemaining = round($remainingBalance - $principal, 2);
            if (abs($newRemaining) < 0.05) {
                $newRemaining = 0.0;
            }

            $schedule[] = [
                'installment_number' => $installmentNumber,
                'due_date' => $dueDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'end_grace_date' => $endGraceDate->format('Y-m-d'),
                'principal' => $principal,
                'interest' => $interest,
                'fee_amount' => round($installmentFees, 2),
                'penalty_amount' => 0, // Calculated when overdue
                'total_amount' => round($principal + $interest + $installmentFees, 2),
                'remaining_balance' => max(0, $newRemaining)
            ];

            $remainingBalance = $newRemaining;
        }

        return $schedule;
    }
    
    /**
     * Calculate fees for specific installment
     */
    private function calculateInstallmentFees(int $installmentIndex, int $totalPeriods, array $fees, float $principal): float
    {
        $totalFees = 0;
        
        foreach ($fees as $fee) {
            if (!$fee['include_in_schedule']) continue;
            
            $application = $fee['application'];
            $feeAmount = 0;
            
            switch ($application['type']) {
                case 'distributed':
                    $feeAmount = $application['per_installment'];
                    break;
                    
                case 'per_installment':
                    $feeAmount = $application['per_installment'];
                    break;
                    
                case 'first_only':
                    $feeAmount = $installmentIndex === 0 ? $application['per_installment'] : 0;
                    break;
                    
                case 'last_only':
                    $feeAmount = $installmentIndex === ($totalPeriods - 1) ? $application['per_installment'] : 0;
                    break;
                    
                case 'release_date':
                case 'not_included':
                default:
                    $feeAmount = 0;
                    break;
            }
            
            $totalFees += $feeAmount;
        }
        
        return $totalFees;
    }
    
    /**
     * Convert annual interest rate to per-period rate based on cycle
     * 
     * If the product has an annual interest rate, this converts it to the period rate
     * based on the interest cycle:
     * - Annually: interest = interest (no change)
     * - Semi-annually: interest = interest/2
     * - Quarterly: interest = interest/4
     * - Monthly: interest = interest/12
     * - Bi-monthly: interest = interest/6
     * - Semi-monthly: interest = interest/24
     * - Bi-weekly: interest = interest/26
     * - Weekly: interest = interest/52
     */
    private function convertAnnualRateToPeriodRate(float $annualRate, string $cycle, ?int $bulletMonths = null): float
    {
        $cycle = strtolower(trim($cycle));
        
        switch ($cycle) {
            case 'annually':
            case 'yearly':
                // Annual rate as-is (1 period per year)
                return $annualRate;
                
            case 'semi_annually':
            case 'semi annually':
            case 'semi-annually':
                // Annual rate divided by 2 semi-annual periods
                return $annualRate / 2;
                
            case 'quarterly':
                // Annual rate divided by 4 quarters
                return $annualRate / 4;
                
            case 'bi_monthly':
            case 'bi-monthly':
            case 'bimonthly':
                // Annual rate divided by 6 bi-monthly periods (2 months each)
                return $annualRate / 6;
                
            case 'monthly':
                // Annual rate divided by 12 months
                return $annualRate / 12;
                
            case 'semi_monthly':
            case 'semi-monthly':
            case 'semimonthly':
                // Annual rate divided by 24 semi-monthly periods (15 days each, ~2 per month)
                return $annualRate / 24;
                
            case 'bi_weekly':
            case 'bi-weekly':
            case 'biweekly':
                // Annual rate divided by 26 bi-weekly periods (14 days each)
                return $annualRate / 26;
                
            case 'weekly':
                // Annual rate divided by 52 weeks
                return $annualRate / 52;
                
            case 'daily':
                // Annual rate divided by 365 days
                return $annualRate / 365;
                
            case 'one_payment_off':
            case 'one payment off':
            case 'bullet':
                // Annual rate divided by specified months for bullet payment
                if ($bulletMonths && $bulletMonths > 0) {
                    return $annualRate / $bulletMonths;
                }
                Log::warning("One payment off cycle selected but no months specified. Defaulting to 12 months.");
                return $annualRate / 12;
                
            default:
                // Default to monthly if cycle not recognized
                Log::warning("Unknown interest cycle '{$cycle}' in loan calculator. Defaulting to monthly.");
                return $annualRate / 12;
        }
    }
    
    /**
     * Calculate due date for installment based on interest cycle.
     *
     * This mirrors the behaviour of actual loans and respects all
     * cycles exposed in the calculator UI (daily, weekly, bi-weekly,
     * semi-monthly, monthly, bi-monthly, quarterly, semi-annually,
     * annually and one-payment-off).
     */
    private function calculateDueDate(
        Carbon $startDate,
        int $installmentIndex,
        string $cycle,
        ?int $bulletMonths = null
    ): Carbon {
        $cycle = strtolower(trim($cycle));

        switch ($cycle) {
            case 'daily':
                return $startDate->copy()->addDays($installmentIndex);

            case 'weekly':
                return $startDate->copy()->addWeeks($installmentIndex);

            case 'bi_weekly':
            case 'bi-weekly':
            case 'biweekly':
                // Every 2 weeks
                return $startDate->copy()->addWeeks($installmentIndex * 2);

            case 'semi_monthly':
            case 'semi-monthly':
            case 'semimonthly':
                // Roughly every half month (~15 days)
                return $startDate->copy()->addDays($installmentIndex * 15);

            case 'monthly':
                return $startDate->copy()->addMonths($installmentIndex);

            case 'bi_monthly':
            case 'bi-monthly':
            case 'bimonthly':
                // Every 2 months
                return $startDate->copy()->addMonths($installmentIndex * 2);

            case 'quarterly':
                // Every 3 months
                return $startDate->copy()->addMonths($installmentIndex * 3);

            case 'semi_annually':
            case 'semi annually':
            case 'semi-annually':
                // Every 6 months
                return $startDate->copy()->addMonths($installmentIndex * 6);

            case 'annually':
            case 'yearly':
                return $startDate->copy()->addYears($installmentIndex);

            case 'one_payment_off':
            case 'one payment off':
            case 'bullet':
                // Bullet repayment: move by the specified bullet-month interval
                // for each installment (usually there is only one installment).
                if ($bulletMonths && $bulletMonths > 0) {
                    return $startDate->copy()->addMonths($bulletMonths * $installmentIndex);
                }
                // Fallback: treat like a monthly schedule if months not provided
                return $startDate->copy()->addMonths($installmentIndex);

            default:
                // Default to monthly progression
                return $startDate->copy()->addMonths($installmentIndex);
        }
    }
    
    /**
     * Calculate totals
     */
    private function calculateTotals(array $params, array $interestCalculation, array $fees, array $schedule): array
    {
        $principal = $params['amount'];
        $totalInterest = $interestCalculation['total_interest'];
        
        // Calculate total fees
        $totalFees = 0;
        foreach ($fees as $fee) {
            $totalFees += $fee['application']['total'];
        }
        
        // Calculate from schedule
        $totalScheduleAmount = array_sum(array_column($schedule, 'total_amount'));
        $totalScheduleFees = array_sum(array_column($schedule, 'fee_amount'));
        
        return [
            'principal' => round($principal, 2),
            'total_interest' => round($totalInterest, 2),
            'total_fees' => round($totalFees, 2),
            'total_amount' => round($principal + $totalInterest + $totalFees, 2),
            'monthly_payment' => round($totalScheduleAmount / count($schedule), 2),
            'schedule_total' => round($totalScheduleAmount, 2),
            'schedule_fees' => round($totalScheduleFees, 2)
        ];
    }
    
    /**
     * Generate summary
     */
    private function generateSummary(array $params, array $totals): array
    {
        return [
            'loan_amount' => $totals['principal'],
            'interest_rate' => $params['interest_rate'],
            'period' => $params['period'],
            'monthly_payment' => $totals['monthly_payment'],
            'total_interest' => $totals['total_interest'],
            'total_fees' => $totals['total_fees'],
            'total_amount' => $totals['total_amount'],
            'interest_percentage' => round(($totals['total_interest'] / $totals['principal']) * 100, 2)
        ];
    }
    
    /**
     * Generate comparison summary
     */
    private function generateComparisonSummary(array $results): array
    {
        $summaries = [];
        
        foreach ($results as $result) {
            if ($result['result']['success']) {
                $summaries[] = [
                    'name' => $result['name'],
                    'monthly_payment' => $result['result']['totals']['monthly_payment'],
                    'total_amount' => $result['result']['totals']['total_amount'],
                    'total_interest' => $result['result']['totals']['total_interest']
                ];
            }
        }
        
        return $summaries;
    }
    
    /**
     * Format product for response
     */
    private function formatProduct(LoanProduct $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'product_type' => $product->product_type,
            'interest_method' => $product->interest_method,
            'interest_cycle' => $product->interest_cycle,
            'grace_period' => $product->grace_period ?? 0
        ];
    }
    
    /**
     * Validate against product limits
     */
    private function validateProductLimits(array $params, LoanProduct $product): void
    {
        if ($params['amount'] < $product->minimum_principal) {
            throw new \InvalidArgumentException("Amount is below minimum: {$product->minimum_principal}");
        }
        
        if ($params['amount'] > $product->maximum_principal) {
            throw new \InvalidArgumentException("Amount exceeds maximum: {$product->maximum_principal}");
        }
        
        if ($params['period'] < $product->minimum_period) {
            throw new \InvalidArgumentException("Period is below minimum: {$product->minimum_period}");
        }
        
        if ($params['period'] > $product->maximum_period) {
            throw new \InvalidArgumentException("Period exceeds maximum: {$product->maximum_period}");
        }
        
        if ($params['interest_rate'] < $product->minimum_interest_rate) {
            throw new \InvalidArgumentException("Interest rate is below minimum: {$product->minimum_interest_rate}%");
        }
        
        if ($params['interest_rate'] > $product->maximum_interest_rate) {
            throw new \InvalidArgumentException("Interest rate exceeds maximum: {$product->maximum_interest_rate}%");
        }
    }
}
