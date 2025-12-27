<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Dividend;
use App\Models\DividendPayment;
use App\Models\ProfitAllocation;
use App\Models\ShareProduct;
use App\Models\ChartAccount;
use App\Models\BankAccount;
use App\Models\Company;
use App\Services\DividendService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class DividendController extends Controller
{
    protected $dividendService;

    public function __construct(DividendService $dividendService)
    {
        $this->dividendService = $dividendService;
    }

    /**
     * Display profit allocation index
     */
    public function profitAllocations()
    {
        return view('dividends.profit-allocations.index');
    }

    /**
     * Show form for creating profit allocation
     */
    public function createProfitAllocation()
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        $company = Company::find($companyId);
        
        $chartAccounts = ChartAccount::whereHas('accountClassGroup', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->whereHas('accountClassGroup.accountClass', function($query) {
                $query->whereIn('name', ['Equity', 'Liabilities']);
            })
            ->orderBy('account_name')
            ->get();

        return view('dividends.profit-allocations.create', compact('chartAccounts', 'company'));
    }

    /**
     * Calculate profit for a financial year
     */
    public function calculateProfit(Request $request)
    {
        $request->validate([
            'financial_year' => 'required|integer|min:2000|max:2100',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        try {
            $result = $this->dividendService->calculateTotalProfit(
                $request->financial_year,
                $request->branch_id,
                current_company_id()
            );

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Error calculating profit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate profit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store profit allocation
     */
    public function storeProfitAllocation(Request $request)
    {
        $request->validate([
            'allocation_date' => 'required|date',
            'financial_year' => 'required|integer|min:2000|max:2100',
            'total_profit' => 'required|numeric|min:0',
            'statutory_reserve_percentage' => 'required|numeric|min:0|max:100',
            'education_fund_percentage' => 'required|numeric|min:0|max:100',
            'community_fund_percentage' => 'required|numeric|min:0|max:100',
            'dividend_percentage' => 'required|numeric|min:0|max:100',
            'other_allocation_percentage' => 'nullable|numeric|min:0|max:100',
            'statutory_reserve_account_id' => 'required|exists:chart_accounts,id',
            'education_fund_account_id' => 'nullable|exists:chart_accounts,id',
            'community_fund_account_id' => 'nullable|exists:chart_accounts,id',
            'dividend_payable_account_id' => 'required|exists:chart_accounts,id',
            'other_allocation_account_id' => 'nullable|exists:chart_accounts,id',
        ]);

        // Validate percentages sum to 100
        $totalPercentage = $request->statutory_reserve_percentage 
            + $request->education_fund_percentage 
            + $request->community_fund_percentage 
            + $request->dividend_percentage 
            + ($request->other_allocation_percentage ?? 0);

        if (abs($totalPercentage - 100) > 0.01) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Total allocation percentages must equal 100%. Current total: ' . number_format($totalPercentage, 2) . '%');
        }

        try {
            $data = $request->all();
            $data['company_id'] = current_company_id();
            $data['branch_id'] = auth()->user()->branch_id;
            $data['created_by'] = auth()->id();
            $data['status'] = $request->status ?? 'draft';

            $profitAllocation = $this->dividendService->createProfitAllocation($data);

            return redirect()->route('dividends.profit-allocations.show', Hashids::encode($profitAllocation->id))
                ->with('success', 'Profit allocation created successfully!');
        } catch (\Exception $e) {
            Log::error('Error storing profit allocation: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create profit allocation: ' . $e->getMessage());
        }
    }

    /**
     * Show profit allocation details
     */
    public function showProfitAllocation($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            return redirect()->route('dividends.profit-allocations')
                ->with('error', 'Invalid profit allocation ID');
        }

        $profitAllocation = ProfitAllocation::with([
            'statutoryReserveAccount',
            'educationFundAccount',
            'communityFundAccount',
            'dividendPayableAccount',
            'otherAllocationAccount',
            'dividends.shareProduct',
            'branch',
            'company'
        ])->findOrFail($id);

        return view('dividends.profit-allocations.show', compact('profitAllocation'));
    }

    /**
     * Show the form for editing the specified profit allocation
     */
    public function editProfitAllocation($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            return redirect()->route('dividends.profit-allocations')
                ->with('error', 'Invalid profit allocation ID');
        }

        $profitAllocation = ProfitAllocation::findOrFail($id);
        $user = auth()->user();
        $companyId = $user->company_id;
        
        $chartAccounts = ChartAccount::whereHas('accountClassGroup', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->whereHas('accountClassGroup.accountClass', function($query) {
                $query->whereIn('name', ['Equity', 'Liabilities']);
            })
            ->orderBy('account_name')
            ->get();

        return view('dividends.profit-allocations.edit', compact('profitAllocation', 'chartAccounts'));
    }

    /**
     * Update the specified profit allocation
     */
    public function updateProfitAllocation(Request $request, $encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            return redirect()->route('dividends.profit-allocations')
                ->with('error', 'Invalid profit allocation ID');
        }

        $profitAllocation = ProfitAllocation::findOrFail($id);

        $request->validate([
            'allocation_date' => 'required|date',
            'financial_year' => 'required|integer|min:2000|max:2100',
            'total_profit' => 'required|numeric|min:0',
            'statutory_reserve_percentage' => 'required|numeric|min:0|max:100',
            'education_fund_percentage' => 'required|numeric|min:0|max:100',
            'community_fund_percentage' => 'required|numeric|min:0|max:100',
            'dividend_percentage' => 'required|numeric|min:0|max:100',
            'other_allocation_percentage' => 'nullable|numeric|min:0|max:100',
            'statutory_reserve_account_id' => 'required|exists:chart_accounts,id',
            'education_fund_account_id' => 'nullable|exists:chart_accounts,id',
            'community_fund_account_id' => 'nullable|exists:chart_accounts,id',
            'dividend_payable_account_id' => 'required|exists:chart_accounts,id',
            'other_allocation_account_id' => 'nullable|exists:chart_accounts,id',
            'notes' => 'nullable|string',
        ]);

        // Validate percentages sum to 100
        $totalPercentage = $request->statutory_reserve_percentage 
            + $request->education_fund_percentage 
            + $request->community_fund_percentage 
            + $request->dividend_percentage 
            + ($request->other_allocation_percentage ?? 0);

        if (abs($totalPercentage - 100) > 0.01) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Total allocation percentages must equal 100%. Current total: ' . number_format($totalPercentage, 2) . '%');
        }

        try {
            $profitAllocation->update([
                'allocation_date' => $request->allocation_date,
                'financial_year' => $request->financial_year,
                'total_profit' => $request->total_profit,
                'statutory_reserve_percentage' => $request->statutory_reserve_percentage,
                'statutory_reserve_amount' => ($request->total_profit * $request->statutory_reserve_percentage) / 100,
                'education_fund_percentage' => $request->education_fund_percentage,
                'education_fund_amount' => ($request->total_profit * $request->education_fund_percentage) / 100,
                'community_fund_percentage' => $request->community_fund_percentage,
                'community_fund_amount' => ($request->total_profit * $request->community_fund_percentage) / 100,
                'dividend_percentage' => $request->dividend_percentage,
                'dividend_amount' => ($request->total_profit * $request->dividend_percentage) / 100,
                'other_allocation_percentage' => $request->other_allocation_percentage ?? 0,
                'other_allocation_amount' => ($request->total_profit * ($request->other_allocation_percentage ?? 0)) / 100,
                'other_allocation_description' => $request->other_allocation_description,
                'other_allocation_account_id' => $request->other_allocation_account_id,
                'statutory_reserve_account_id' => $request->statutory_reserve_account_id,
                'education_fund_account_id' => $request->education_fund_account_id,
                'community_fund_account_id' => $request->community_fund_account_id,
                'dividend_payable_account_id' => $request->dividend_payable_account_id,
                'notes' => $request->notes,
                'updated_by' => auth()->id(),
            ]);

            return redirect()->route('dividends.profit-allocations.show', $encodedId)
                ->with('success', 'Profit allocation updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating profit allocation: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update profit allocation: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified profit allocation
     */
    public function destroyProfitAllocation($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Invalid profit allocation ID'], 400);
        }

        try {
            $profitAllocation = ProfitAllocation::findOrFail($id);
            
            // Check if there are associated dividends
            if ($profitAllocation->dividends()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete profit allocation with associated dividends. Please delete the dividends first.'
                ], 400);
            }

            $profitAllocation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Profit allocation deleted successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting profit allocation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete profit allocation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change profit allocation status
     */
    public function changeProfitAllocationStatus(Request $request, $encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Invalid profit allocation ID'], 400);
        }

        $request->validate([
            'status' => 'required|in:draft,approved,posted,rejected'
        ]);

        try {
            $profitAllocation = ProfitAllocation::findOrFail($id);
            $oldStatus = $profitAllocation->status;
            $profitAllocation->update([
                'status' => $request->status,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profit allocation status changed from ' . ucfirst($oldStatus) . ' to ' . ucfirst($request->status) . ' successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error changing profit allocation status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to change status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display dividends index
     */
    public function dividends()
    {
        $shareProducts = ShareProduct::where('is_active', true)
            ->orderBy('share_name')
            ->get();

        return view('dividends.dividends.index', compact('shareProducts'));
    }

    /**
     * Show form for creating dividend
     */
    public function createDividend()
    {
        $shareProducts = ShareProduct::where('is_active', true)
            ->orderBy('share_name')
            ->get();

        $profitAllocations = ProfitAllocation::where('status', 'approved')
            ->where('dividend_amount', '>', 0)
            ->orderBy('financial_year', 'desc')
            ->orderBy('allocation_date', 'desc')
            ->get();

        return view('dividends.dividends.create', compact('shareProducts', 'profitAllocations'));
    }

    /**
     * Store dividend declaration
     */
    public function storeDividend(Request $request)
    {
        $request->validate([
            'profit_allocation_id' => 'nullable|exists:profit_allocations,id',
            'share_product_id' => 'required|exists:share_products,id',
            'declaration_date' => 'required|date',
            'financial_year' => 'required|integer|min:2000|max:2100',
            'total_dividend_amount' => 'required|numeric|min:0',
            'dividend_rate' => 'nullable|numeric|min:0|max:100',
            'calculation_method' => 'required|in:on_share_capital,on_share_value,on_minimum_balance,on_average_balance',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Generate dividend number
            $year = $request->financial_year;
            $last = Dividend::where('financial_year', $year)
                ->orderBy('id', 'desc')
                ->first();
            $number = $last ? ((int) substr($last->dividend_number, -4)) + 1 : 1;
            $dividendNumber = 'DIV-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);

            $dividend = Dividend::create([
                'dividend_number' => $dividendNumber,
                'profit_allocation_id' => $request->profit_allocation_id,
                'share_product_id' => $request->share_product_id,
                'declaration_date' => $request->declaration_date,
                'financial_year' => $request->financial_year,
                'total_dividend_amount' => $request->total_dividend_amount,
                'dividend_rate' => $request->dividend_rate,
                'calculation_method' => $request->calculation_method,
                'description' => $request->description,
                'status' => 'draft',
                'branch_id' => auth()->user()->branch_id,
                'company_id' => current_company_id(),
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('dividends.dividends.show', Hashids::encode($dividend->id))
                ->with('success', 'Dividend declared successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing dividend: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create dividend: ' . $e->getMessage());
        }
    }

    /**
     * Calculate dividends for members
     */
    public function calculateDividends($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            return redirect()->route('dividends.dividends')
                ->with('error', 'Invalid dividend ID');
        }

        $dividend = Dividend::findOrFail($id);

        try {
            $result = $this->dividendService->calculateDividends(
                $dividend->id,
                $dividend->share_product_id,
                $dividend->total_dividend_amount,
                $dividend->calculation_method
            );

            return redirect()->route('dividends.dividends.show', $encodedId)
                ->with('success', 'Dividends calculated successfully! ' . count($result['payments']) . ' payments created.');
        } catch (\Exception $e) {
            Log::error('Error calculating dividends: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to calculate dividends: ' . $e->getMessage());
        }
    }

    /**
     * Show dividend details
     */
    public function showDividend($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            return redirect()->route('dividends.dividends')
                ->with('error', 'Invalid dividend ID');
        }

        $dividend = Dividend::with([
            'shareProduct',
            'profitAllocation',
            'dividendPayments.customer',
            'dividendPayments.shareAccount',
            'branch',
            'company'
        ])->findOrFail($id);

        return view('dividends.dividends.show', compact('dividend'));
    }

    /**
     * Process dividend payment
     */
    public function processPayment(Request $request, $encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Invalid payment ID'], 400);
        }

        $request->validate([
            'payment_method' => 'required|in:cash,savings_deposit,convert_to_shares',
            'payment_date' => 'required|date',
            'bank_account_id' => 'required_if:payment_method,cash|exists:bank_accounts,id',
            'savings_account_id' => 'required_if:payment_method,savings_deposit|exists:share_accounts,id',
            'share_product_id' => 'required_if:payment_method,convert_to_shares|exists:share_products,id',
            'notes' => 'nullable|string',
        ]);

        try {
            $payment = $this->dividendService->processDividendPayment($id, $request->all());
            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing dividend payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DataTables endpoint for profit allocations
     */
    public function getProfitAllocationsData(Request $request)
    {
        if ($request->ajax()) {
            $allocations = ProfitAllocation::with(['branch', 'company'])
                ->select('profit_allocations.*');

            return DataTables::eloquent($allocations)
                ->addIndexColumn()
                ->addColumn('allocation_date', function ($allocation) {
                    return $allocation->allocation_date ? $allocation->allocation_date->format('Y-m-d') : 'N/A';
                })
                ->addColumn('actions', function ($allocation) {
                    $encodedId = Hashids::encode($allocation->id);
                    $actions = '<a href="' . route('dividends.profit-allocations.show', $encodedId) . '" class="btn btn-sm btn-info me-1" title="View"><i class="bx bx-show"></i></a>';
                    $actions .= '<a href="' . route('dividends.profit-allocations.edit', $encodedId) . '" class="btn btn-sm btn-warning me-1" title="Edit"><i class="bx bx-edit"></i></a>';
                    $actions .= '<button class="btn btn-sm btn-secondary change-status-btn me-1" data-id="' . $encodedId . '" data-status="' . $allocation->status . '" title="Change Status"><i class="bx bx-transfer"></i></button>';
                    $actions .= '<button class="btn btn-sm btn-danger delete-btn" data-id="' . $encodedId . '" data-name="' . e($allocation->reference_number ?? 'Profit Allocation') . '" title="Delete"><i class="bx bx-trash"></i></button>';
                    return '<div class="text-center d-flex justify-content-center gap-1">' . $actions . '</div>';
                })
                ->rawColumns(['actions', 'status'])
                ->make(true);
        }
    }

    /**
     * DataTables endpoint for dividends
     */
    public function getDividendsData(Request $request)
    {
        if ($request->ajax()) {
            $dividends = Dividend::with(['shareProduct', 'profitAllocation'])
                ->select('dividends.*');

            if ($request->filled('share_product_id')) {
                $dividends->where('share_product_id', $request->share_product_id);
            }

            if ($request->filled('status')) {
                $dividends->where('status', $request->status);
            }

            return DataTables::eloquent($dividends)
                ->addIndexColumn()
                ->addColumn('share_product_name', function ($dividend) {
                    return $dividend->shareProduct->share_name ?? 'N/A';
                })
                ->addColumn('actions', function ($dividend) {
                    $encodedId = Hashids::encode($dividend->id);
                    $actions = '<a href="' . route('dividends.dividends.show', $encodedId) . '" class="btn btn-sm btn-info me-1" title="View"><i class="bx bx-show"></i></a>';
                    if ($dividend->status === 'draft') {
                        $actions .= '<a href="' . route('dividends.dividends.calculate', $encodedId) . '" class="btn btn-sm btn-primary me-1" title="Calculate" onclick="return confirm(\'Are you sure you want to calculate dividends?\')"><i class="bx bx-calculator"></i></a>';
                    }
                    return '<div class="text-center d-flex justify-content-center gap-1">' . $actions . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
    }
}
