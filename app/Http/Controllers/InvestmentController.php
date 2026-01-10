<?php

namespace App\Http\Controllers;

use App\Models\UTTFund;
use App\Models\SaccoUTTHolding;
use App\Models\UTTTransaction;
use App\Models\UTTNavPrice;
use App\Models\UTTCashFlow;
use App\Models\UTTReconciliation;
use App\Models\Company;
use App\Models\Branch;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Vinkla\Hashids\Facades\Hashids;
use Carbon\Carbon;

class InvestmentController extends Controller
{
    // ==================== FUNDS MANAGEMENT ====================

    /**
     * Display funds listing page
     */
    public function fundsIndex()
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        
        $totalFunds = UTTFund::where('company_id', $companyId)->count();
        $activeFunds = UTTFund::where('company_id', $companyId)->where('status', 'Active')->count();
        $closedFunds = UTTFund::where('company_id', $companyId)->where('status', 'Closed')->count();

        return view('investments.funds.index', compact('totalFunds', 'activeFunds', 'closedFunds'));
    }

    /**
     * Get funds data for DataTables
     */
    public function getFundsData(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $companyId = $user->company_id;

            $funds = UTTFund::with(['company', 'branch', 'creator'])
                ->where('company_id', $companyId)
                ->select('utt_funds.*');

            if ($request->filled('status')) {
                $funds->where('status', $request->status);
            }

            return DataTables::eloquent($funds)
                ->addColumn('status_badge', function ($fund) {
                    $badge = $fund->status === 'Active' ? 'success' : 'secondary';
                    return '<span class="badge bg-' . $badge . '">' . e($fund->status) . '</span>';
                })
                ->addColumn('horizon_badge', function ($fund) {
                    $badge = $fund->investment_horizon === 'LONG-TERM' ? 'info' : 'warning';
                    return '<span class="badge bg-' . $badge . '">' . e($fund->investment_horizon) . '</span>';
                })
                ->addColumn('expense_ratio_formatted', function ($fund) {
                    return $fund->expense_ratio ? number_format($fund->expense_ratio, 4) . '%' : 'N/A';
                })
                ->addColumn('actions', function ($fund) {
                    $encodedId = Hashids::encode($fund->id);
                    $actions = '';

                    if (auth()->user()->can('edit investment fund')) {
                        $actions .= '<a href="' . route('investments.funds.edit', $encodedId) . '" class="btn btn-sm btn-outline-primary me-1" title="Edit"><i class="bx bx-edit"></i></a>';
                    }

                    if (auth()->user()->can('view investment fund')) {
                        $actions .= '<a href="' . route('investments.funds.show', $encodedId) . '" class="btn btn-sm btn-outline-info me-1" title="View"><i class="bx bx-show"></i></a>';
                    }

                    return '<div class="text-center">' . $actions . '</div>';
                })
                ->rawColumns(['status_badge', 'horizon_badge', 'actions'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Show form to create a new fund
     */
    public function fundsCreate()
    {
        $user = auth()->user();
        $branches = Branch::where('company_id', $user->company_id)->get();
        
        return view('investments.funds.create', compact('branches'));
    }

    /**
     * Store a new fund
     */
    public function fundsStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fund_name' => 'required|string|max:255',
            'fund_code' => 'required|string|max:50|unique:utt_funds,fund_code',
            'currency' => 'required|string|size:3',
            'investment_horizon' => 'required|in:SHORT-TERM,LONG-TERM',
            'expense_ratio' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:Active,Closed',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = auth()->user();
            
            $fund = UTTFund::create([
                'fund_name' => $request->fund_name,
                'fund_code' => strtoupper($request->fund_code),
                'currency' => strtoupper($request->currency),
                'investment_horizon' => $request->investment_horizon,
                'expense_ratio' => $request->expense_ratio,
                'status' => $request->status,
                'company_id' => $user->company_id,
                'branch_id' => $request->branch_id,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'notes' => $request->notes,
            ]);

            // Create initial holding record
            SaccoUTTHolding::create([
                'utt_fund_id' => $fund->id,
                'company_id' => $user->company_id,
                'branch_id' => $request->branch_id,
                'total_units' => 0,
                'average_acquisition_cost' => 0,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            return redirect()->route('investments.funds.index')
                ->with('success', 'UTT Fund created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create fund: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show fund details
     */
    public function fundsShow($encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('investments.funds.index')
                ->with('error', 'Invalid fund ID');
        }

        $fund = UTTFund::with(['holdings', 'transactions', 'navPrices'])->findOrFail($decoded[0]);
        $holding = $fund->holdings()->first();
        $latestNav = $fund->navPrices()->latest('nav_date')->first();
        $currentValue = $holding ? $holding->getCurrentValue() : 0;

        return view('investments.funds.show', compact('fund', 'holding', 'latestNav', 'currentValue'));
    }

    /**
     * Show form to edit fund
     */
    public function fundsEdit($encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('investments.funds.index')
                ->with('error', 'Invalid fund ID');
        }

        $fund = UTTFund::findOrFail($decoded[0]);
        $user = auth()->user();
        $branches = Branch::where('company_id', $user->company_id)->get();

        return view('investments.funds.edit', compact('fund', 'branches'));
    }

    /**
     * Update fund
     */
    public function fundsUpdate(Request $request, $encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('investments.funds.index')
                ->with('error', 'Invalid fund ID');
        }

        $fund = UTTFund::findOrFail($decoded[0]);

        $validator = Validator::make($request->all(), [
            'fund_name' => 'required|string|max:255',
            'fund_code' => 'required|string|max:50|unique:utt_funds,fund_code,' . $fund->id,
            'currency' => 'required|string|size:3',
            'investment_horizon' => 'required|in:SHORT-TERM,LONG-TERM',
            'expense_ratio' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:Active,Closed',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = auth()->user();
            
            $fund->update([
                'fund_name' => $request->fund_name,
                'fund_code' => strtoupper($request->fund_code),
                'currency' => strtoupper($request->currency),
                'investment_horizon' => $request->investment_horizon,
                'expense_ratio' => $request->expense_ratio,
                'status' => $request->status,
                'branch_id' => $request->branch_id,
                'updated_by' => $user->id,
                'notes' => $request->notes,
            ]);

            return redirect()->route('investments.funds.index')
                ->with('success', 'UTT Fund updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update fund: ' . $e->getMessage())
                ->withInput();
        }
    }

    // ==================== HOLDINGS MANAGEMENT ====================

    /**
     * Display holdings register
     */
    public function holdingsIndex()
    {
        return view('investments.holdings.index');
    }

    /**
     * Get holdings data for DataTables
     */
    public function getHoldingsData(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $companyId = $user->company_id;

            $holdings = SaccoUTTHolding::with(['uttFund', 'company'])
                ->where('company_id', $companyId)
                ->select('sacco_utt_holdings.*');

            return DataTables::eloquent($holdings)
                ->addColumn('fund_name', function ($holding) {
                    return $holding->uttFund->fund_name ?? 'N/A';
                })
                ->addColumn('fund_code', function ($holding) {
                    return $holding->uttFund->fund_code ?? 'N/A';
                })
                ->addColumn('total_units_formatted', function ($holding) {
                    return number_format($holding->total_units, 4);
                })
                ->addColumn('average_cost_formatted', function ($holding) {
                    return number_format($holding->average_acquisition_cost, 4);
                })
                ->addColumn('current_nav', function ($holding) {
                    $latestNav = $holding->uttFund->navPrices()->latest('nav_date')->first();
                    return $latestNav ? number_format($latestNav->nav_per_unit, 4) : 'N/A';
                })
                ->addColumn('current_value', function ($holding) {
                    $value = $holding->getCurrentValue();
                    return number_format($value, 2);
                })
                ->addColumn('unrealized_gain', function ($holding) {
                    $gain = $holding->getUnrealizedGain();
                    $badge = $gain >= 0 ? 'success' : 'danger';
                    return '<span class="badge bg-' . $badge . '">' . number_format($gain, 2) . '</span>';
                })
                ->rawColumns(['unrealized_gain'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    // ==================== TRANSACTIONS MANAGEMENT ====================

    /**
     * Display transactions listing
     */
    public function transactionsIndex()
    {
        return view('investments.transactions.index');
    }

    /**
     * Get transactions data for DataTables
     */
    public function getTransactionsData(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $companyId = $user->company_id;

            $transactions = UTTTransaction::with(['uttFund', 'maker', 'checker'])
                ->where('company_id', $companyId)
                ->select('utt_transactions.*');

            if ($request->filled('status')) {
                $transactions->where('status', $request->status);
            }

            if ($request->filled('type')) {
                $transactions->where('transaction_type', $request->type);
            }

            return DataTables::eloquent($transactions)
                ->addColumn('fund_name', function ($transaction) {
                    return $transaction->uttFund->fund_name ?? 'N/A';
                })
                ->addColumn('type_badge', function ($transaction) {
                    $badges = [
                        'BUY' => 'success',
                        'SELL' => 'danger',
                        'REINVESTMENT' => 'info',
                    ];
                    $badge = $badges[$transaction->transaction_type] ?? 'secondary';
                    return '<span class="badge bg-' . $badge . '">' . e($transaction->transaction_type) . '</span>';
                })
                ->editColumn('trade_date', function ($transaction) {
                    return $transaction->trade_date ? Carbon::parse($transaction->trade_date)->format('M d, Y') : 'N/A';
                })
                ->addColumn('status_badge', function ($transaction) {
                    $badges = [
                        'PENDING' => 'warning',
                        'APPROVED' => 'info',
                        'SETTLED' => 'success',
                        'CANCELLED' => 'danger',
                    ];
                    $badge = $badges[$transaction->status] ?? 'secondary';
                    return '<span class="badge bg-' . $badge . '">' . e($transaction->status) . '</span>';
                })
                ->addColumn('units_formatted', function ($transaction) {
                    return number_format($transaction->units, 4);
                })
                ->addColumn('nav_formatted', function ($transaction) {
                    return number_format($transaction->nav_per_unit, 4);
                })
                ->addColumn('cash_value_formatted', function ($transaction) {
                    return number_format($transaction->total_cash_value, 2);
                })
                ->addColumn('maker_name', function ($transaction) {
                    return $transaction->maker->name ?? 'N/A';
                })
                ->addColumn('checker_name', function ($transaction) {
                    return $transaction->checker->name ?? 'N/A';
                })
                ->addColumn('actions', function ($transaction) {
                    $encodedId = Hashids::encode($transaction->id);
                    $actions = '';

                    if (auth()->user()->can('view investment transaction')) {
                        $actions .= '<a href="' . route('investments.transactions.show', $encodedId) . '" class="btn btn-sm btn-outline-info me-1" title="View"><i class="bx bx-show"></i></a>';
                    }

                    if ($transaction->canBeApproved() && auth()->user()->can('approve investment transaction')) {
                        $actions .= '<button class="btn btn-sm btn-outline-success approve-btn me-1" data-id="' . $encodedId . '" title="Approve"><i class="bx bx-check"></i></button>';
                    }

                    if ($transaction->canBeSettled() && auth()->user()->can('settle investment transaction')) {
                        $actions .= '<button class="btn btn-sm btn-outline-primary settle-btn me-1" data-id="' . $encodedId . '" title="Settle"><i class="bx bx-check-circle"></i></button>';
                    }

                    if ($transaction->canBeCancelled() && auth()->user()->can('cancel investment transaction')) {
                        $actions .= '<button class="btn btn-sm btn-outline-danger cancel-btn me-1" data-id="' . $encodedId . '" title="Cancel"><i class="bx bx-x"></i></button>';
                    }

                    return '<div class="text-center">' . $actions . '</div>';
                })
                ->rawColumns(['type_badge', 'status_badge', 'actions'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Show form to create a new transaction
     */
    public function transactionsCreate()
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        
        $funds = UTTFund::where('company_id', $companyId)
            ->where('status', 'Active')
            ->get();
        
        $bankAccounts = BankAccount::where('company_id', $companyId)
            ->where('status', 'active')
            ->get();

        return view('investments.transactions.create', compact('funds', 'bankAccounts'));
    }

    /**
     * Store a new transaction
     */
    public function transactionsStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'utt_fund_id' => 'required|exists:utt_funds,id',
            'transaction_type' => 'required|in:BUY,SELL,REINVESTMENT',
            'trade_date' => 'required|date|before_or_equal:today',
            'nav_date' => 'required|date|before_or_equal:today',
            'settlement_date' => 'required|date|after_or_equal:trade_date',
            'units' => 'required|numeric|min:0.0001',
            'nav_per_unit' => 'required|numeric|min:0.0001',
            'total_cash_value' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'bank_account_id' => 'required|exists:bank_accounts,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $fund = UTTFund::findOrFail($request->utt_fund_id);
            $holding = SaccoUTTHolding::where('utt_fund_id', $fund->id)
                ->where('company_id', $user->company_id)
                ->firstOrFail();

            // Validate SELL transactions don't exceed holdings
            if ($request->transaction_type === 'SELL' && $request->units > $holding->total_units) {
                return redirect()->back()
                    ->with('error', 'Insufficient units. Available: ' . number_format($holding->total_units, 4))
                    ->withInput();
            }

            // Generate reference number
            $referenceNumber = 'UTT-' . strtoupper($request->transaction_type) . '-' . date('Ymd') . '-' . str_pad(UTTTransaction::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            $transaction = UTTTransaction::create([
                'utt_fund_id' => $request->utt_fund_id,
                'sacco_utt_holding_id' => $holding->id,
                'transaction_type' => $request->transaction_type,
                'trade_date' => $request->trade_date,
                'nav_date' => $request->nav_date,
                'settlement_date' => $request->settlement_date,
                'units' => $request->units,
                'nav_per_unit' => $request->nav_per_unit,
                'total_cash_value' => $request->total_cash_value,
                'status' => 'PENDING',
                'reference_number' => $referenceNumber,
                'description' => $request->description,
                'maker_id' => $user->id,
                'company_id' => $user->company_id,
                'branch_id' => $user->branch_id,
            ]);

            // Create cash flow record
            $flowDirection = in_array($request->transaction_type, ['BUY', 'REINVESTMENT']) ? 'OUT' : 'IN';
            $classification = $request->transaction_type === 'REINVESTMENT' ? 'Income' : 'Capital';

            UTTCashFlow::create([
                'utt_fund_id' => $request->utt_fund_id,
                'utt_transaction_id' => $transaction->id,
                'cash_flow_type' => $request->transaction_type === 'BUY' ? 'Subscription' : ($request->transaction_type === 'SELL' ? 'Redemption' : 'Reinvestment'),
                'transaction_date' => $request->trade_date,
                'amount' => $request->total_cash_value,
                'flow_direction' => $flowDirection,
                'reference_number' => $referenceNumber,
                'description' => $request->description,
                'classification' => $classification,
                'bank_account_id' => $request->bank_account_id,
                'company_id' => $user->company_id,
                'branch_id' => $user->branch_id,
                'created_by' => $user->id,
            ]);

            DB::commit();

            return redirect()->route('investments.transactions.index')
                ->with('success', 'Transaction created successfully. Awaiting approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create transaction: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show transaction details
     */
    public function transactionsShow($encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('investments.transactions.index')
                ->with('error', 'Invalid transaction ID');
        }

        $transaction = UTTTransaction::with(['uttFund', 'saccoUTTHolding', 'maker', 'checker', 'cashFlows'])
            ->findOrFail($decoded[0]);

        return view('investments.transactions.show', compact('transaction'));
    }

    /**
     * Approve a transaction (checker action)
     */
    public function transactionsApprove(Request $request, $encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return response()->json(['error' => 'Invalid transaction ID'], 400);
        }

        try {
            DB::beginTransaction();

            $transaction = UTTTransaction::findOrFail($decoded[0]);

            if (!$transaction->canBeApproved()) {
                return response()->json(['error' => 'Transaction cannot be approved in current status'], 400);
            }

            $user = auth()->user();
            if ($transaction->maker_id === $user->id) {
                return response()->json(['error' => 'You cannot approve your own transaction'], 400);
            }

            $transaction->update([
                'status' => 'APPROVED',
                'checker_id' => $user->id,
                'approved_at' => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Transaction approved successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to approve transaction: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Settle a transaction
     */
    public function transactionsSettle(Request $request, $encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return response()->json(['error' => 'Invalid transaction ID'], 400);
        }

        try {
            DB::beginTransaction();

            $transaction = UTTTransaction::with(['uttFund', 'saccoUTTHolding'])->findOrFail($decoded[0]);

            if (!$transaction->canBeSettled()) {
                return response()->json(['error' => 'Transaction cannot be settled in current status'], 400);
            }

            $holding = $transaction->saccoUTTHolding;
            $oldUnits = $holding->total_units;
            $oldCost = $holding->average_acquisition_cost;

            // Update holdings
            if ($transaction->transaction_type === 'BUY' || $transaction->transaction_type === 'REINVESTMENT') {
                // Calculate new average cost
                $totalCost = ($oldUnits * $oldCost) + ($transaction->units * $transaction->nav_per_unit);
                $newUnits = $oldUnits + $transaction->units;
                $newAverageCost = $newUnits > 0 ? $totalCost / $newUnits : 0;

                $holding->update([
                    'total_units' => $newUnits,
                    'average_acquisition_cost' => $newAverageCost,
                ]);
            } elseif ($transaction->transaction_type === 'SELL') {
                $newUnits = $oldUnits - $transaction->units;
                $holding->update([
                    'total_units' => max(0, $newUnits),
                    // Average cost remains the same for sells (FIFO or average cost method)
                ]);
            }

            $transaction->update([
                'status' => 'SETTLED',
                'settled_at' => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Transaction settled successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to settle transaction: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cancel a transaction
     */
    public function transactionsCancel(Request $request, $encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return response()->json(['error' => 'Invalid transaction ID'], 400);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $transaction = UTTTransaction::findOrFail($decoded[0]);

            if (!$transaction->canBeCancelled()) {
                return response()->json(['error' => 'Transaction cannot be cancelled in current status'], 400);
            }

            $transaction->update([
                'status' => 'CANCELLED',
                'rejection_reason' => $request->rejection_reason,
            ]);

            return response()->json(['success' => true, 'message' => 'Transaction cancelled successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to cancel transaction: ' . $e->getMessage()], 500);
        }
    }

    // ==================== NAV PRICES MANAGEMENT ====================

    /**
     * Display NAV prices listing
     */
    public function navPricesIndex()
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        
        $funds = UTTFund::where('company_id', $companyId)
            ->where('status', 'Active')
            ->get();

        return view('investments.nav-prices.index', compact('funds'));
    }

    /**
     * Get NAV prices data for DataTables
     */
    public function getNavPricesData(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $companyId = $user->company_id;

            $navPrices = UTTNavPrice::with(['uttFund', 'enteredBy'])
                ->where('company_id', $companyId)
                ->select('utt_nav_prices.*');

            if ($request->filled('utt_fund_id')) {
                $navPrices->where('utt_fund_id', $request->utt_fund_id);
            }

            return DataTables::eloquent($navPrices)
                ->addColumn('fund_name', function ($navPrice) {
                    return $navPrice->uttFund->fund_name ?? 'N/A';
                })
                ->addColumn('fund_code', function ($navPrice) {
                    return $navPrice->uttFund->fund_code ?? 'N/A';
                })
                ->addColumn('nav_formatted', function ($navPrice) {
                    return number_format($navPrice->nav_per_unit, 4);
                })
                ->addColumn('entered_by_name', function ($navPrice) {
                    return $navPrice->enteredBy->name ?? 'N/A';
                })
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Show form to create a new NAV price
     */
    public function navPricesCreate()
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        
        $funds = UTTFund::where('company_id', $companyId)
            ->where('status', 'Active')
            ->get();

        return view('investments.nav-prices.create', compact('funds'));
    }

    /**
     * Store a new NAV price
     */
    public function navPricesStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'utt_fund_id' => 'required|exists:utt_funds,id',
            'nav_date' => 'required|date|before_or_equal:today',
            'nav_per_unit' => 'required|numeric|min:0.0001',
            'notes' => 'nullable|string',
        ]);

        // Check for duplicate NAV per fund per date
        $existingNav = UTTNavPrice::where('utt_fund_id', $request->utt_fund_id)
            ->where('nav_date', $request->nav_date)
            ->first();

        if ($existingNav) {
            return redirect()->back()
                ->with('error', 'NAV already exists for this fund on this date')
                ->withInput();
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = auth()->user();
            
            UTTNavPrice::create([
                'utt_fund_id' => $request->utt_fund_id,
                'nav_date' => $request->nav_date,
                'nav_per_unit' => $request->nav_per_unit,
                'notes' => $request->notes,
                'entered_by' => $user->id,
                'company_id' => $user->company_id,
            ]);

            return redirect()->route('investments.nav-prices.index')
                ->with('success', 'NAV price entered successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to enter NAV price: ' . $e->getMessage())
                ->withInput();
        }
    }

    // ==================== CASH FLOWS MANAGEMENT ====================

    /**
     * Display cash flows listing
     */
    public function cashFlowsIndex()
    {
        return view('investments.cash-flows.index');
    }

    /**
     * Get cash flows data for DataTables
     */
    public function getCashFlowsData(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $companyId = $user->company_id;

            $cashFlows = UTTCashFlow::with(['uttFund', 'bankAccount'])
                ->where('company_id', $companyId)
                ->select('utt_cash_flows.*');

            if ($request->filled('cash_flow_type')) {
                $cashFlows->where('cash_flow_type', $request->cash_flow_type);
            }

            if ($request->filled('flow_direction')) {
                $cashFlows->where('flow_direction', $request->flow_direction);
            }

            return DataTables::eloquent($cashFlows)
                ->addColumn('fund_name', function ($cashFlow) {
                    return $cashFlow->uttFund->fund_name ?? 'N/A';
                })
                ->addColumn('type_badge', function ($cashFlow) {
                    $badges = [
                        'Subscription' => 'danger',
                        'Redemption' => 'success',
                        'Income Distribution' => 'info',
                        'Reinvestment' => 'warning',
                    ];
                    $badge = $badges[$cashFlow->cash_flow_type] ?? 'secondary';
                    return '<span class="badge bg-' . $badge . '">' . e($cashFlow->cash_flow_type) . '</span>';
                })
                ->addColumn('direction_badge', function ($cashFlow) {
                    $badge = $cashFlow->flow_direction === 'IN' ? 'success' : 'danger';
                    return '<span class="badge bg-' . $badge . '">' . e($cashFlow->flow_direction) . '</span>';
                })
                ->addColumn('amount_formatted', function ($cashFlow) {
                    return number_format($cashFlow->amount, 2);
                })
                ->addColumn('classification_badge', function ($cashFlow) {
                    $badge = $cashFlow->classification === 'Income' ? 'info' : 'primary';
                    return '<span class="badge bg-' . $badge . '">' . e($cashFlow->classification) . '</span>';
                })
                ->rawColumns(['type_badge', 'direction_badge', 'classification_badge'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    // ==================== RECONCILIATIONS MANAGEMENT ====================

    /**
     * Display reconciliations listing
     */
    public function reconciliationsIndex()
    {
        return view('investments.reconciliations.index');
    }

    /**
     * Get reconciliations data for DataTables
     */
    public function getReconciliationsData(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $companyId = $user->company_id;

            $reconciliations = UTTReconciliation::with(['uttFund', 'saccoUTTHolding', 'reconciledBy', 'approvedBy'])
                ->where('company_id', $companyId)
                ->select('utt_reconciliations.*');

            if ($request->filled('status')) {
                $reconciliations->where('status', $request->status);
            }

            return DataTables::eloquent($reconciliations)
                ->addColumn('fund_name', function ($reconciliation) {
                    return $reconciliation->uttFund->fund_name ?? 'N/A';
                })
                ->addColumn('statement_units_formatted', function ($reconciliation) {
                    return number_format($reconciliation->statement_units, 4);
                })
                ->addColumn('system_units_formatted', function ($reconciliation) {
                    return number_format($reconciliation->system_units, 4);
                })
                ->addColumn('variance_formatted', function ($reconciliation) {
                    $badge = abs($reconciliation->variance) < 0.0001 ? 'success' : 'danger';
                    return '<span class="badge bg-' . $badge . '">' . number_format($reconciliation->variance, 4) . '</span>';
                })
                ->addColumn('status_badge', function ($reconciliation) {
                    $badges = [
                        'Draft' => 'secondary',
                        'In Progress' => 'warning',
                        'Completed' => 'success',
                        'Variance Identified' => 'danger',
                    ];
                    $badge = $badges[$reconciliation->status] ?? 'secondary';
                    return '<span class="badge bg-' . $badge . '">' . e($reconciliation->status) . '</span>';
                })
                ->addColumn('reconciled_by', function ($reconciliation) {
                    return $reconciliation->reconciledBy->name ?? 'N/A';
                })
                ->editColumn('reconciliation_date', function ($reconciliation) {
                    return $reconciliation->reconciliation_date ? Carbon::parse($reconciliation->reconciliation_date)->format('M d, Y') : 'N/A';
                })
                ->rawColumns(['variance_formatted', 'status_badge'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Show form to create a new reconciliation
     */
    public function reconciliationsCreate()
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        
        $holdings = SaccoUTTHolding::with('uttFund')
            ->where('company_id', $companyId)
            ->get();

        return view('investments.reconciliations.create', compact('holdings'));
    }

    /**
     * Store a new reconciliation
     */
    public function reconciliationsStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'utt_fund_id' => 'required|exists:utt_funds,id',
            'sacco_utt_holding_id' => 'required|exists:sacco_utt_holdings,id',
            'reconciliation_date' => 'required|date|before_or_equal:today',
            'statement_units' => 'required|numeric|min:0',
            'reconciliation_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = auth()->user();
            $holding = SaccoUTTHolding::findOrFail($request->sacco_utt_holding_id);

            $reconciliation = UTTReconciliation::create([
                'utt_fund_id' => $request->utt_fund_id,
                'sacco_utt_holding_id' => $request->sacco_utt_holding_id,
                'reconciliation_date' => $request->reconciliation_date,
                'statement_units' => $request->statement_units,
                'system_units' => $holding->total_units,
                'variance' => $request->statement_units - $holding->total_units,
                'status' => abs($request->statement_units - $holding->total_units) < 0.0001 ? 'Completed' : 'Variance Identified',
                'reconciliation_notes' => $request->reconciliation_notes,
                'company_id' => $user->company_id,
                'branch_id' => $user->branch_id,
            ]);

            // Update last reconciliation date on holding
            $holding->update([
                'last_reconciliation_date' => $request->reconciliation_date,
            ]);

            return redirect()->route('investments.reconciliations.index')
                ->with('success', 'Reconciliation created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create reconciliation: ' . $e->getMessage())
                ->withInput();
        }
    }

    // ==================== VALUATION & REPORTS ====================

    /**
     * Get portfolio valuation
     */
    public function getPortfolioValuation()
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        $holdings = SaccoUTTHolding::with(['uttFund.navPrices'])
            ->where('company_id', $companyId)
            ->get();

        $totalValue = 0;
        $totalCost = 0;
        $portfolio = [];

        foreach ($holdings as $holding) {
            $currentValue = $holding->getCurrentValue();
            $costBasis = $holding->total_units * $holding->average_acquisition_cost;
            $unrealizedGain = $holding->getUnrealizedGain();

            $portfolio[] = [
                'fund_name' => $holding->uttFund->fund_name,
                'fund_code' => $holding->uttFund->fund_code,
                'units' => $holding->total_units,
                'average_cost' => $holding->average_acquisition_cost,
                'current_nav' => $holding->uttFund->navPrices()->latest('nav_date')->first()->nav_per_unit ?? 0,
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

        return response()->json([
            'portfolio' => $portfolio,
            'summary' => [
                'total_value' => $totalValue,
                'total_cost' => $totalCost,
                'total_unrealized_gain' => $totalUnrealizedGain,
                'total_return_pct' => $totalReturnPct,
            ],
        ]);
    }

    /**
     * Member view - read-only investment information
     */
    public function memberView()
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        $holdings = SaccoUTTHolding::with(['uttFund.navPrices'])
            ->where('company_id', $companyId)
            ->get();

        $totalValue = 0;
        $portfolio = [];

        foreach ($holdings as $holding) {
            $currentValue = $holding->getCurrentValue();
            $latestNav = $holding->uttFund->navPrices()->latest('nav_date')->first();

            $portfolio[] = [
                'fund_name' => $holding->uttFund->fund_name,
                'fund_code' => $holding->uttFund->fund_code,
                'investment_horizon' => $holding->uttFund->investment_horizon,
                'current_nav' => $latestNav->nav_per_unit ?? 0,
                'nav_date' => $latestNav->nav_date ?? null,
                'current_value' => $currentValue,
            ];

            $totalValue += $currentValue;
        }

        return view('investments.member.view', compact('portfolio', 'totalValue'));
    }
}
