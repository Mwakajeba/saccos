<?php
namespace App\Services;

use App\Models\Loan;
use App\Models\LoanSchedule;
use Illuminate\Support\Facades\DB;
use App\Models\Journal;
use App\Models\JournalItem;
use App\Models\GlTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LoanRestructuringService
{
    /**
     * Restructure a loan's repayment schedule.
     *
     * @param Loan $loan
     * @param array $params [
     *   'new_tenure' => int,
     *   'new_interest_rate' => float,
     *   'new_start_date' => date,
     *   'penalty_waived' => bool,
     *   'interest_type' => 'flat'|'reducing'
     * ]
     * @param int $userId
     * @return Loan The new restructured loan
     */
    public function restructure(Loan $loan, array $params, int $userId)
    {
        return DB::transaction(function () use ($loan, $params, $userId) {
            // 1. Get all schedules with repayments
            $schedules = $loan->schedule()->with('repayments')->get();
            
            // 2. Separate paid and unpaid schedules
            $paidSchedules = $schedules->filter(function ($schedule) {
                return $schedule->is_fully_paid;
            });
            
            $unpaidSchedules = $schedules->filter(function ($schedule) {
                return !$schedule->is_fully_paid;
            });

            // 3. Calculate outstanding balances
            // Calculate outstanding principal from original loan amount minus total paid
            // This avoids rounding errors from summing schedule principal amounts
            $totalPaidPrincipal = $schedules->sum(function ($schedule) {
                return $schedule->repayments->sum('principal');
            });
            $outstandingPrincipal = max(0, $loan->amount - $totalPaidPrincipal);
            
            // Calculate outstanding interest and penalty from unpaid schedules
            $outstandingInterest = 0;
            $outstandingPenalty = 0;
            
            foreach ($unpaidSchedules as $schedule) {
                $paidInterest = $schedule->repayments->sum('interest');
                $paidPenalty = $schedule->repayments->sum('penalt_amount');
                
                $outstandingInterest += max(0, $schedule->interest - $paidInterest);
                $outstandingPenalty += max(0, $schedule->penalty_amount - $paidPenalty);
            }
            
            // Round to 2 decimal places to avoid floating point precision issues
            $outstandingPrincipal = round($outstandingPrincipal, 2);
            $outstandingInterest = round($outstandingInterest, 2);
            $outstandingPenalty = round($outstandingPenalty, 2);

            // Apply penalty waiver if requested
            if (isset($params['penalty_waived']) && $params['penalty_waived']) {
                $outstandingPenalty = 0;
            }

            // 4. Calculate new principal and interest amount for the new loan
            $newPrincipal = $outstandingPrincipal + $outstandingInterest + $outstandingPenalty;
            $newInterestAmount = 0;
            $tenure = $params['new_tenure'];
            $interestRate = $params['new_interest_rate'];
            $interestCycle = $loan->interest_cycle ?? 'monthly';
            
            // Get interest type from product
            $interestType = 'flat_rate';
            if ($loan->product && $loan->product->interest_method) {
                $method = strtolower($loan->product->interest_method);
                if (in_array($method, ['flat_rate', 'reducing_balance_with_equal_installment', 'reducing_balance_with_equal_principal'])) {
                    $interestType = $method;
                }
            }
            
            // Calculate interest based on type
            if ($interestType === 'flat_rate') {
                $newInterestAmount = ($newPrincipal * $interestRate / 100) * $tenure;
            } elseif ($interestType === 'reducing_balance_with_equal_installment') {
                $monthlyRate = $interestRate / 100;
                $remainingPrincipal = $newPrincipal;
                for ($i = 1; $i <= $tenure; $i++) {
                    $interestPart = $remainingPrincipal * $monthlyRate;
                    $newInterestAmount += $interestPart;
                    $emi = ($monthlyRate > 0)
                        ? ($newPrincipal * $monthlyRate * pow(1 + $monthlyRate, $tenure)) / (pow(1 + $monthlyRate, $tenure) - 1)
                        : $newPrincipal / $tenure;
                    $principalPart = $emi - $interestPart;
                    $remainingPrincipal -= $principalPart;

                    // Log each installment
                    Log::info("Restructure Schedule", [
                        'installment_no'     => $i,
                        'interest_part'      => round($interestPart, 2),
                        'principal_part'     => round($principalPart, 2),
                        'emi'                => round($emi, 2),
                        'remaining_balance'  => round(max($remainingPrincipal, 0), 2),
                    ]);
                }
            } elseif ($interestType === 'reducing_balance_with_equal_principal') {
                $monthlyPrincipal = $newPrincipal / $tenure;
                for ($i = 1; $i <= $tenure; $i++) {
                    $remainingPrincipal = $newPrincipal - $monthlyPrincipal * ($i - 1);
                    $interestPart = $remainingPrincipal * $interestRate / 100 / 12;
                    $newInterestAmount += $interestPart;
                }
            }

            // 5. Calculate last repayment date
            $lastRepaymentDate = Carbon::parse($params['new_start_date'])->addMonths($tenure - 1);
            
            // 6. Create the new restructured loan
            $newLoan = Loan::create([
                'customer_id' => $loan->customer_id,
                'group_id' => $loan->group_id,
                'product_id' => $loan->product_id,
                'amount' => $newPrincipal,
                'interest' => $params['new_interest_rate'],
                'interest_amount' => round($newInterestAmount, 2),
                'period' => $params['new_tenure'],
                'amount_total' => $newPrincipal + round($newInterestAmount, 2),
                'bank_account_id' => $loan->bank_account_id,
                'date_applied' => Carbon::parse($params['new_start_date'])->toDateString(),
                'disbursed_on' => Carbon::parse($params['new_start_date'])->toDateString(),
                'status' => 'active',
                'sector' => $loan->sector,
                'interest_cycle' => $loan->interest_cycle,
                'loan_officer_id' => $loan->loan_officer_id,
                'top_up_id' => $loan->id,
                'first_repayment_date' => $params['new_start_date'],
                'last_repayment_date' => $lastRepaymentDate->toDateString(),
                'branch_id' => $loan->branch_id,
            ]);

            // Record the restructuring status for old loan
            $loan->status = 'restructured';
            $loan->save();

            // 7. Handle double entry for capitalization if needed
            if ($outstandingInterest > 0 || $outstandingPenalty > 0) {
                $product = $newLoan->product;
                $principalAccountId = $product->principal_receivable_account_id;
                $interestAccountId = $product->interest_receivable_account_id;
                $penaltyAccountId = null;
                
                $penalty = $product->penalty; // uses getPenaltyAttribute()
                if ($penalty && $penalty->penalty_receivables_account_id) {
                    $penaltyAccountId = $penalty->penalty_receivables_account_id;
                }

                $journal = Journal::create([
                    'date' => now(),
                    'reference' => $newLoan->id,
                    'reference_type' => 'Loan Restructuring',
                    'customer_id' => $newLoan->customer_id,
                    'description' => 'Loan Restructuring Capitalization',
                    'branch_id' => $newLoan->branch_id,
                    'user_id' => $userId,
                ]);

                // Interest capitalization
                if ($outstandingInterest > 0 && $interestAccountId && $principalAccountId) {
                    JournalItem::create([
                        'journal_id' => $journal->id,
                        'chart_account_id' => $interestAccountId,
                        'amount' => $outstandingInterest,
                        'description' => 'Capitalize Interest',
                        'nature' => 'credit',
                    ]);
                    JournalItem::create([
                        'journal_id' => $journal->id,
                        'chart_account_id' => $principalAccountId,
                        'amount' => $outstandingInterest,
                        'description' => 'Capitalize Interest',
                        'nature' => 'debit',
                    ]);
                    GlTransaction::create([
                        'chart_account_id' => $interestAccountId,
                        'customer_id' => $newLoan->customer_id,
                        'amount' => $outstandingInterest,
                        'nature' => 'credit',
                        'transaction_id' => $newLoan->id,
                        'transaction_type' => 'Loan Restructuring',
                        'date' => now(),
                        'description' => 'Capitalize Interest',
                        'branch_id' => $newLoan->branch_id,
                        'user_id' => $userId,
                    ]);
                    GlTransaction::create([
                        'chart_account_id' => $principalAccountId,
                        'customer_id' => $newLoan->customer_id,
                        'amount' => $outstandingInterest,
                        'nature' => 'debit',
                        'transaction_id' => $newLoan->id,
                        'transaction_type' => 'Loan Restructuring',
                        'date' => now(),
                        'description' => 'Capitalize Interest',
                        'branch_id' => $newLoan->branch_id,
                        'user_id' => $userId,
                    ]);
                }

                // Penalty capitalization
                if ($outstandingPenalty > 0 && $penaltyAccountId && $principalAccountId) {
                    JournalItem::create([
                        'journal_id' => $journal->id,
                        'chart_account_id' => $penaltyAccountId,
                        'amount' => $outstandingPenalty,
                        'description' => 'Capitalize Penalty',
                        'nature' => 'credit',
                    ]);
                    JournalItem::create([
                        'journal_id' => $journal->id,
                        'chart_account_id' => $principalAccountId,
                        'amount' => $outstandingPenalty,
                        'description' => 'Capitalize Penalty',
                        'nature' => 'debit',
                    ]);
                    GlTransaction::create([
                        'chart_account_id' => $penaltyAccountId,
                        'customer_id' => $newLoan->customer_id,
                        'amount' => $outstandingPenalty,
                        'nature' => 'credit',
                        'transaction_id' => $newLoan->id,
                        'transaction_type' => 'Loan Restructuring',
                        'date' => now(),
                        'description' => 'Capitalize Penalty',
                        'branch_id' => $newLoan->branch_id,
                        'user_id' => $userId,
                    ]);
                    GlTransaction::create([
                        'chart_account_id' => $principalAccountId,
                        'customer_id' => $newLoan->customer_id,
                        'amount' => $outstandingPenalty,
                        'nature' => 'debit',
                        'transaction_id' => $newLoan->id,
                        'transaction_type' => 'Loan Restructuring',
                        'date' => now(),
                        'description' => 'Capitalize Penalty',
                        'branch_id' => $newLoan->branch_id,
                        'user_id' => $userId,
                    ]);
                }
            }

            // 8. Generate new repayment schedule for the new loan
            $newLoan->generateRepaymentSchedule($params['new_interest_rate']);

            Log::info('Loan restructured successfully', [
                'old_loan_id' => $loan->id,
                'new_loan_id' => $newLoan->id,
                'new_principal' => $newPrincipal,
                'new_interest_amount' => $newInterestAmount,
                'new_tenure' => $tenure,
                'new_interest_rate' => $interestRate,
                'outstanding_principal' => $outstandingPrincipal,
                'outstanding_interest' => $outstandingInterest,
                'outstanding_penalty' => $outstandingPenalty,
            ]);

            return $newLoan;
        });
    }
}
