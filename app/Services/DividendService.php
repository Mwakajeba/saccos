<?php

namespace App\Services;

use App\Models\Dividend;
use App\Models\DividendPayment;
use App\Models\ProfitAllocation;
use App\Models\ShareAccount;
use App\Models\ShareProduct;
use App\Models\GlTransaction;
use App\Models\ChartAccount;
use App\Models\BankAccount;
use App\Models\ShareDeposit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DividendService
{
    /**
     * Calculate total profit from GL transactions for a financial year
     */
    public function calculateTotalProfit($financialYear, $branchId = null, $companyId = null)
    {
        $startDate = Carbon::create($financialYear, 1, 1)->startOfDay();
        $endDate = Carbon::create($financialYear, 12, 31)->endOfDay();

        $baseQuery = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->whereBetween('gl_transactions.date', [$startDate, $endDate]);

        if ($branchId) {
            $baseQuery->where('gl_transactions.branch_id', $branchId);
        }

        if ($companyId) {
            $baseQuery->where('account_class_groups.company_id', $companyId);
        }

        // Calculate revenue (Income/Revenue accounts - credit increases)
        $revenueQuery = (clone $baseQuery)
            ->whereIn('account_class.name', ['Income', 'Revenue'])
            ->selectRaw('COALESCE(SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END), 0) as total');
        
        $revenue = $revenueQuery->value('total') ?? 0;

        // Calculate expenses (Expense accounts - debit increases)
        $expensesQuery = (clone $baseQuery)
            ->whereIn('account_class.name', ['Expense', 'Expenses'])
            ->selectRaw('COALESCE(SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END), 0) as total');
        
        $expenses = $expensesQuery->value('total') ?? 0;

        $profit = $revenue - $expenses;

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'profit' => $profit,
            'financial_year' => $financialYear,
        ];
    }

    /**
     * Create profit allocation
     */
    public function createProfitAllocation($data)
    {
        DB::beginTransaction();
        try {
            // Calculate amounts based on percentages
            $totalProfit = $data['total_profit'];
            $data['statutory_reserve_amount'] = ($totalProfit * $data['statutory_reserve_percentage']) / 100;
            $data['education_fund_amount'] = ($totalProfit * $data['education_fund_percentage']) / 100;
            $data['community_fund_amount'] = ($totalProfit * $data['community_fund_percentage']) / 100;
            $data['dividend_amount'] = ($totalProfit * $data['dividend_percentage']) / 100;
            $data['other_allocation_amount'] = ($totalProfit * $data['other_allocation_percentage']) / 100;

            // Generate reference number
            if (empty($data['reference_number'])) {
                $data['reference_number'] = $this->generateProfitAllocationReference();
            }

            $profitAllocation = ProfitAllocation::create($data);

            // Create GL transactions if status is approved or posted
            if (in_array($data['status'], ['approved', 'posted'])) {
                $this->createProfitAllocationGLTransactions($profitAllocation);
            }

            DB::commit();
            return $profitAllocation;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating profit allocation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate dividends for a share product
     */
    public function calculateDividends($dividendId, $shareProductId, $totalDividendAmount, $calculationMethod = 'on_share_capital')
    {
        DB::beginTransaction();
        try {
            $dividend = Dividend::findOrFail($dividendId);
            $shareProduct = ShareProduct::findOrFail($shareProductId);

            // Get all active share accounts for this product
            $shareAccounts = ShareAccount::where('share_product_id', $shareProductId)
                ->where('status', 'active')
                ->get();

            $totalShares = 0;
            $dividendPayments = [];

            // Calculate total shares based on method
            foreach ($shareAccounts as $account) {
                switch ($calculationMethod) {
                    case 'on_share_capital':
                        $totalShares += $account->share_balance;
                        break;
                    case 'on_share_value':
                        $totalShares += ($account->share_balance * $account->nominal_value);
                        break;
                    case 'on_minimum_balance':
                        // Use minimum balance if configured
                        $totalShares += max($account->share_balance, $shareProduct->minimum_balance_for_dividend ?? 0);
                        break;
                    case 'on_average_balance':
                        // Calculate average balance (simplified - can be enhanced)
                        $totalShares += $account->share_balance;
                        break;
                }
            }

            // Calculate dividend per share
            $dividendPerShare = $totalShares > 0 ? ($totalDividendAmount / $totalShares) : 0;

            // Update dividend record
            $dividend->update([
                'total_shares' => $totalShares,
                'dividend_per_share' => $dividendPerShare,
                'status' => 'calculated',
            ]);

            // Create dividend payment records for each member
            foreach ($shareAccounts as $account) {
                $memberShares = match($calculationMethod) {
                    'on_share_capital' => $account->share_balance,
                    'on_share_value' => ($account->share_balance * $account->nominal_value),
                    'on_minimum_balance' => max($account->share_balance, $shareProduct->minimum_balance_for_dividend ?? 0),
                    'on_average_balance' => $account->share_balance,
                    default => $account->share_balance,
                };

                $dividendAmount = $memberShares * $dividendPerShare;

                // Skip if dividend amount is zero or below minimum
                if ($dividendAmount <= 0 || ($shareProduct->minimum_balance_for_dividend && $account->share_balance < $shareProduct->minimum_balance_for_dividend)) {
                    continue;
                }

                $paymentNumber = $this->generateDividendPaymentReference();

                DividendPayment::create([
                    'payment_number' => $paymentNumber,
                    'dividend_id' => $dividendId,
                    'share_account_id' => $account->id,
                    'customer_id' => $account->customer_id,
                    'member_shares' => $memberShares,
                    'dividend_amount' => $dividendAmount,
                    'payment_method' => 'cash', // Default, can be changed later
                    'status' => 'pending',
                    'branch_id' => $account->branch_id,
                    'company_id' => $account->company_id,
                    'created_by' => auth()->id(),
                ]);

                $dividendPayments[] = [
                    'account' => $account,
                    'shares' => $memberShares,
                    'amount' => $dividendAmount,
                ];
            }

            DB::commit();
            return [
                'dividend' => $dividend,
                'total_shares' => $totalShares,
                'dividend_per_share' => $dividendPerShare,
                'payments' => $dividendPayments,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error calculating dividends: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process dividend payment
     */
    public function processDividendPayment($paymentId, $paymentData)
    {
        DB::beginTransaction();
        try {
            $payment = DividendPayment::findOrFail($paymentId);

            if ($payment->status !== 'pending') {
                throw new \Exception('Payment is not in pending status');
            }

            $paymentMethod = $paymentData['payment_method'] ?? 'cash';

            switch ($paymentMethod) {
                case 'cash':
                    $this->processCashPayment($payment, $paymentData);
                    break;
                case 'savings_deposit':
                    $this->processSavingsDeposit($payment, $paymentData);
                    break;
                case 'convert_to_shares':
                    $this->processShareConversion($payment, $paymentData);
                    break;
            }

            $payment->update([
                'payment_method' => $paymentMethod,
                'payment_date' => $paymentData['payment_date'] ?? now(),
                'status' => $paymentMethod === 'convert_to_shares' ? 'converted' : 'paid',
                'notes' => $paymentData['notes'] ?? null,
            ]);

            DB::commit();
            return $payment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing dividend payment: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process cash payment
     */
    private function processCashPayment($payment, $paymentData)
    {
        $bankAccount = BankAccount::findOrFail($paymentData['bank_account_id']);
        $dividendPayableAccount = $payment->dividend->profitAllocation->dividendPayableAccount;

        // Debit: Dividend Payable Account
        GlTransaction::create([
            'chart_account_id' => $dividendPayableAccount->id,
            'amount' => $payment->dividend_amount,
            'nature' => 'debit',
            'date' => $paymentData['payment_date'] ?? now(),
            'description' => "Dividend payment to {$payment->customer->name} - {$payment->payment_number}",
            'branch_id' => $payment->branch_id,
            'user_id' => auth()->id(),
        ]);

        // Credit: Bank Account
        GlTransaction::create([
            'chart_account_id' => $bankAccount->chart_account_id,
            'amount' => $payment->dividend_amount,
            'nature' => 'credit',
            'date' => $paymentData['payment_date'] ?? now(),
            'description' => "Dividend payment to {$payment->customer->name} - {$payment->payment_number}",
            'branch_id' => $payment->branch_id,
            'user_id' => auth()->id(),
        ]);

        $payment->update(['bank_account_id' => $bankAccount->id]);
    }

    /**
     * Process savings deposit
     */
    private function processSavingsDeposit($payment, $paymentData)
    {
        // This would deposit into a savings account
        // Implementation depends on how savings accounts are structured
        // For now, we'll create a share deposit record if savings use share accounts
        $savingsAccount = ShareAccount::findOrFail($paymentData['savings_account_id']);

        ShareDeposit::create([
            'share_account_id' => $savingsAccount->id,
            'customer_id' => $payment->customer_id,
            'deposit_date' => $paymentData['payment_date'] ?? now(),
            'amount' => $payment->dividend_amount,
            'shares' => 0, // No shares for savings deposit
            'payment_method' => 'dividend',
            'reference' => $payment->payment_number,
            'description' => "Dividend deposited to savings - {$payment->payment_number}",
            'status' => 'approved',
            'branch_id' => $payment->branch_id,
            'company_id' => $payment->company_id,
            'created_by' => auth()->id(),
        ]);

        $payment->update(['savings_account_id' => $savingsAccount->id]);
    }

    /**
     * Process share conversion
     */
    private function processShareConversion($payment, $paymentData)
    {
        $shareProduct = ShareProduct::findOrFail($paymentData['share_product_id']);
        $nominalValue = $shareProduct->nominal_price ?? 1;
        $sharesToAdd = $payment->dividend_amount / $nominalValue;

        // Add shares to the account
        $shareAccount = $payment->shareAccount;
        $shareAccount->share_balance += $sharesToAdd;
        $shareAccount->last_transaction_date = $paymentData['payment_date'] ?? now();
        $shareAccount->save();

        // Create share deposit record
        ShareDeposit::create([
            'share_account_id' => $shareAccount->id,
            'customer_id' => $payment->customer_id,
            'deposit_date' => $paymentData['payment_date'] ?? now(),
            'amount' => $payment->dividend_amount,
            'shares' => $sharesToAdd,
            'payment_method' => 'dividend_conversion',
            'reference' => $payment->payment_number,
            'description' => "Dividend converted to shares - {$payment->payment_number}",
            'status' => 'approved',
            'branch_id' => $payment->branch_id,
            'company_id' => $payment->company_id,
            'created_by' => auth()->id(),
        ]);

        $payment->update([
            'share_product_id' => $shareProduct->id,
            'shares_converted' => $sharesToAdd,
        ]);
    }

    /**
     * Create GL transactions for profit allocation
     */
    private function createProfitAllocationGLTransactions($profitAllocation)
    {
        // Debit: Retained Earnings / Profit & Loss Account
        // Credit: Statutory Reserve, Education Fund, Community Fund, Dividend Payable

        // This is a simplified version - you may need to adjust based on your chart of accounts structure
        // The actual implementation depends on how profit/loss is tracked in your system
    }

    /**
     * Generate profit allocation reference number
     */
    private function generateProfitAllocationReference()
    {
        $year = date('Y');
        $last = ProfitAllocation::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $last ? ((int) substr($last->reference_number, -4)) + 1 : 1;
        return 'PA-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate dividend payment reference number
     */
    private function generateDividendPaymentReference()
    {
        $year = date('Y');
        $last = DividendPayment::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $last ? ((int) substr($last->payment_number, -4)) + 1 : 1;
        return 'DIV-PAY-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}

