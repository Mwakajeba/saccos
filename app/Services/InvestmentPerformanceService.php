<?php

namespace App\Services;

use App\Models\SaccoUTTHolding;
use App\Models\UTTTransaction;
use App\Models\UTTCashFlow;
use Carbon\Carbon;

class InvestmentPerformanceService
{
    /**
     * Calculate investment performance metrics
     */
    public function calculatePerformance($companyId, $startDate = null, $endDate = null)
    {
        $endDate = $endDate ?? Carbon::today();
        $startDate = $startDate ?? Carbon::today()->subYear();

        $holdings = SaccoUTTHolding::with(['uttFund'])
            ->where('company_id', $companyId)
            ->get();

        $totalIncome = 0;
        $totalCapitalGain = 0;
        $totalRealizedGain = 0;
        $totalUnrealizedGain = 0;

        // Get cash flows for income
        $incomeFlows = UTTCashFlow::where('company_id', $companyId)
            ->where('classification', 'Income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        foreach ($holdings as $holding) {
            $valuationService = new InvestmentValuationService();
            $currentValue = $valuationService->calculateFundValue($holding->utt_fund_id, $holding->total_units, $endDate);
            $costBasis = $holding->total_units * $holding->average_acquisition_cost;
            
            $unrealizedGain = $currentValue - $costBasis;
            $totalUnrealizedGain += $unrealizedGain;

            // Calculate realized gains from settled SELL transactions
            $realizedGains = UTTTransaction::where('sacco_utt_holding_id', $holding->id)
                ->where('transaction_type', 'SELL')
                ->where('status', 'SETTLED')
                ->whereBetween('settlement_date', [$startDate, $endDate])
                ->get()
                ->sum(function ($transaction) use ($holding) {
                    $costBasis = $transaction->units * $holding->average_acquisition_cost;
                    $proceeds = $transaction->total_cash_value;
                    return $proceeds - $costBasis;
                });

            $totalRealizedGain += $realizedGains;
        }

        $totalIncome = $incomeFlows;
        $totalCapitalGain = $totalRealizedGain + $totalUnrealizedGain;
        $totalReturn = $totalIncome + $totalCapitalGain;

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'income' => [
                'total' => $totalIncome,
                'type' => 'Income Distribution',
            ],
            'capital_gains' => [
                'realized' => $totalRealizedGain,
                'unrealized' => $totalUnrealizedGain,
                'total' => $totalCapitalGain,
            ],
            'total_return' => $totalReturn,
        ];
    }

    /**
     * Calculate returns by investment horizon
     */
    public function calculateReturnsByHorizon($companyId)
    {
        $holdings = SaccoUTTHolding::with(['uttFund'])
            ->where('company_id', $companyId)
            ->get();

        $shortTerm = [
            'total_value' => 0,
            'total_cost' => 0,
            'unrealized_gain' => 0,
        ];

        $longTerm = [
            'total_value' => 0,
            'total_cost' => 0,
            'unrealized_gain' => 0,
        ];

        $valuationService = new InvestmentValuationService();

        foreach ($holdings as $holding) {
            $currentValue = $valuationService->calculateFundValue($holding->utt_fund_id, $holding->total_units);
            $costBasis = $holding->total_units * $holding->average_acquisition_cost;
            $unrealizedGain = $currentValue - $costBasis;

            if ($holding->uttFund->investment_horizon === 'SHORT-TERM') {
                $shortTerm['total_value'] += $currentValue;
                $shortTerm['total_cost'] += $costBasis;
                $shortTerm['unrealized_gain'] += $unrealizedGain;
            } else {
                $longTerm['total_value'] += $currentValue;
                $longTerm['total_cost'] += $costBasis;
                $longTerm['unrealized_gain'] += $unrealizedGain;
            }
        }

        return [
            'short_term' => [
                ...$shortTerm,
                'return_pct' => $shortTerm['total_cost'] > 0 ? ($shortTerm['unrealized_gain'] / $shortTerm['total_cost']) * 100 : 0,
            ],
            'long_term' => [
                ...$longTerm,
                'return_pct' => $longTerm['total_cost'] > 0 ? ($longTerm['unrealized_gain'] / $longTerm['total_cost']) * 100 : 0,
            ],
        ];
    }
}

