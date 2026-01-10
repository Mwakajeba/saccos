<?php

namespace App\Services;

use App\Models\SaccoUTTHolding;
use App\Models\UTTNavPrice;
use Carbon\Carbon;

class InvestmentValuationService
{
    /**
     * Calculate current portfolio value
     */
    public function calculatePortfolioValue($companyId, $asOfDate = null)
    {
        $holdings = SaccoUTTHolding::with(['uttFund'])
            ->where('company_id', $companyId)
            ->get();

        $totalValue = 0;
        $totalCost = 0;
        $portfolio = [];

        foreach ($holdings as $holding) {
            $latestNav = $this->getLatestNav($holding->utt_fund_id, $asOfDate);
            
            if (!$latestNav) {
                continue;
            }

            $currentValue = $holding->total_units * $latestNav->nav_per_unit;
            $costBasis = $holding->total_units * $holding->average_acquisition_cost;
            $unrealizedGain = $currentValue - $costBasis;

            $portfolio[] = [
                'holding_id' => $holding->id,
                'fund_id' => $holding->utt_fund_id,
                'fund_name' => $holding->uttFund->fund_name,
                'fund_code' => $holding->uttFund->fund_code,
                'units' => $holding->total_units,
                'average_cost' => $holding->average_acquisition_cost,
                'current_nav' => $latestNav->nav_per_unit,
                'nav_date' => $latestNav->nav_date,
                'current_value' => $currentValue,
                'cost_basis' => $costBasis,
                'unrealized_gain' => $unrealizedGain,
                'unrealized_gain_pct' => $costBasis > 0 ? ($unrealizedGain / $costBasis) * 100 : 0,
            ];

            $totalValue += $currentValue;
            $totalCost += $costBasis;
        }

        $totalUnrealizedGain = $totalValue - $totalCost;
        $totalReturnPct = $totalCost > 0 ? ($totalUnrealizedGain / $totalCost) * 100 : 0;

        return [
            'portfolio' => $portfolio,
            'summary' => [
                'total_value' => $totalValue,
                'total_cost' => $totalCost,
                'total_unrealized_gain' => $totalUnrealizedGain,
                'total_return_pct' => $totalReturnPct,
                'valuation_date' => $asOfDate ?? Carbon::today(),
            ],
        ];
    }

    /**
     * Get latest NAV for a fund as of a specific date
     */
    public function getLatestNav($fundId, $asOfDate = null)
    {
        $query = UTTNavPrice::where('utt_fund_id', $fundId);

        if ($asOfDate) {
            $query->where('nav_date', '<=', $asOfDate);
        }

        return $query->orderBy('nav_date', 'desc')->first();
    }

    /**
     * Calculate fund value at a specific date
     */
    public function calculateFundValue($fundId, $units, $asOfDate = null)
    {
        $latestNav = $this->getLatestNav($fundId, $asOfDate);
        
        if (!$latestNav) {
            return 0;
        }

        return $units * $latestNav->nav_per_unit;
    }
}

