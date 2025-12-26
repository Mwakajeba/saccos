<?php

namespace App\Http\Controllers;

use App\Models\ShareProduct;
use App\Models\ChartAccount;
use App\Models\Fee;
use App\Models\JournalReference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;

class ShareProductController extends Controller
{
    /**
     * Display a listing of share products
     */
    public function index()
    {
        return view('shares.products.index');
    }

    /**
     * Ajax endpoint for DataTables
     */
    public function getShareProductsData(Request $request)
    {
        if ($request->ajax()) {
            $shareProducts = ShareProduct::with([
                'journalReference',
                'liabilityAccount',
                'incomeAccount',
                'shareCapitalAccount',
                'charge'
            ])->select('share_products.*');

            return DataTables::eloquent($shareProducts)
                ->addColumn('required_share_formatted', function ($product) {
                    return number_format($product->required_share, 2);
                })
                ->addColumn('nominal_price_formatted', function ($product) {
                    return number_format($product->nominal_price, 2);
                })
                ->addColumn('minimum_active_period_display', function ($product) {
                    return $product->minimum_active_period . ' ' . $product->minimum_active_period_type;
                })
                ->addColumn('lockin_period_display', function ($product) {
                    return $product->lockin_period_frequency . ' ' . $product->lockin_period_frequency_type;
                })
                ->addColumn('status_badge', function ($product) {
                    if ($product->is_active) {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Inactive</span>';
                    }
                })
                ->addColumn('actions', function ($product) {
                    $actions = '';
                    $encodedId = Hashids::encode($product->id);

                    // View action
                    $actions .= '<a href="' . route('shares.products.show', $encodedId) . '" class="btn btn-sm btn-info me-1" title="View"><i class="bx bx-show"></i></a>';

                    // Edit action
                    $actions .= '<a href="' . route('shares.products.edit', $encodedId) . '" class="btn btn-sm btn-warning me-1" title="Edit"><i class="bx bx-edit"></i></a>';

                    // Toggle status action (Activate/Deactivate)
                    if ($product->is_active) {
                        $actions .= '<button class="btn btn-sm btn-outline-warning me-1 toggle-status-btn" data-id="' . $encodedId . '" data-name="' . e($product->share_name) . '" data-status="active" title="Deactivate"><i class="bx bx-pause"></i></button>';
                    } else {
                        $actions .= '<button class="btn btn-sm btn-outline-success me-1 toggle-status-btn" data-id="' . $encodedId . '" data-name="' . e($product->share_name) . '" data-status="inactive" title="Activate"><i class="bx bx-play"></i></button>';
                    }

                    // Delete action
                    $actions .= '<button class="btn btn-sm btn-danger delete-btn" data-id="' . $encodedId . '" data-name="' . e($product->share_name) . '" title="Delete"><i class="bx bx-trash"></i></button>';

                    return '<div class="text-center d-flex justify-content-center gap-1">' . $actions . '</div>';
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Show the form for creating a new share product
     */
    public function create()
    {
        // Get chart accounts for dropdowns
        $chartAccounts = ChartAccount::all();

        // Get fees for charges dropdown
        $fees = Fee::where('status', 'active')->get();
        
        // Get journal references (filtered by company and branch like in JournalReferenceController)
        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;
        
        $journalReferences = JournalReference::where('company_id', $companyId)
            ->where(function($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Define period type options
        $periodTypes = [
            'Days' => 'Days',
            'Weeks' => 'Weeks',
            'Months' => 'Months',
            'Years' => 'Years',
        ];

        // Define Yes/No options
        $yesNoOptions = [
            'Yes' => 'Yes',
            'No' => 'No',
        ];

        // Define charge type options
        $chargeTypes = [
            'fixed' => 'Fixed Amount',
            'percentage' => 'Percentage',
        ];

        // Define dividend calculation methods
        $dividendCalculationMethods = [
            'on_share_capital' => 'On Share Capital',
            'on_share_value' => 'On Share Value',
            'on_minimum_balance' => 'On Minimum Balance',
            'on_average_balance' => 'On Average Balance',
        ];

        // Define dividend payment frequencies
        $dividendPaymentFrequencies = [
            'Monthly' => 'Monthly',
            'Quarterly' => 'Quarterly',
            'Semi_Annually' => 'Semi-Annually',
            'Annually' => 'Annually',
        ];

        // Months for dropdown
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = date('F', mktime(0, 0, 0, $i, 1));
        }

        // Days for dropdown (1-31)
        $days = [];
        for ($i = 1; $i <= 31; $i++) {
            $days[$i] = $i;
        }

        return view('shares.products.create', compact(
            'chartAccounts',
            'fees',
            'journalReferences',
            'periodTypes',
            'yesNoOptions',
            'chargeTypes',
            'dividendCalculationMethods',
            'dividendPaymentFrequencies',
            'months',
            'days'
        ));
    }

    /**
     * Store a newly created share product
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'share_name' => 'required|string|max:255|unique:share_products',
            'required_share' => 'required|numeric|min:0',
            'nominal_price' => 'required|numeric|min:0',
            'minimum_purchase_amount' => 'nullable|numeric|min:0',
            'maximum_purchase_amount' => 'nullable|numeric|min:0',
            'maximum_shares_per_member' => 'nullable|numeric|min:0',
            'minimum_shares_for_membership' => 'nullable|numeric|min:0',
            'share_purchase_increment' => 'nullable|numeric|min:0',
            'minimum_active_period' => 'required|integer|min:1',
            'minimum_active_period_type' => 'required|string|in:Days,Weeks,Months,Years',
            'allow_dividends_for_inactive_member' => 'required|string|in:Yes,No',
            'dividend_rate' => 'nullable|numeric|min:0|max:100',
            'dividend_calculation_method' => 'nullable|string|in:on_share_capital,on_share_value,on_minimum_balance,on_average_balance',
            'dividend_payment_frequency' => 'nullable|string|in:Monthly,Quarterly,Semi_Annually,Annually',
            'dividend_payment_month' => 'nullable|integer|min:1|max:12',
            'dividend_payment_day' => 'nullable|integer|min:1|max:31',
            'minimum_balance_for_dividend' => 'nullable|numeric|min:0',
            'lockin_period_frequency' => 'required|integer|min:1',
            'lockin_period_frequency_type' => 'required|string|in:Days,Weeks,Months,Years',
            'description' => 'nullable|string',
            'certificate_number_prefix' => 'nullable|string|max:20',
            'certificate_number_format' => 'nullable|string|max:100',
            'auto_generate_certificate' => 'boolean',
            'opening_date' => 'nullable|date',
            'closing_date' => 'nullable|date|after_or_equal:opening_date',
            'allow_new_subscriptions' => 'boolean',
            'allow_additional_purchases' => 'boolean',
            'maximum_total_shares' => 'nullable|numeric|min:0',
            'allow_share_transfers' => 'boolean',
            'transfer_fee' => 'nullable|numeric|min:0',
            'transfer_fee_type' => 'nullable|string|in:fixed,percentage',
            'allow_share_withdrawals' => 'boolean',
            'withdrawal_fee' => 'nullable|numeric|min:0',
            'withdrawal_fee_type' => 'nullable|string|in:fixed,percentage',
            'withdrawal_notice_period' => 'nullable|integer|min:0',
            'withdrawal_notice_period_type' => 'nullable|string|in:Days,Weeks,Months',
            'minimum_withdrawal_amount' => 'nullable|numeric|min:0',
            'maximum_withdrawal_amount' => 'nullable|numeric|min:0',
            'allow_partial_withdrawal' => 'boolean',
            'has_charges' => 'boolean',
            'charge_id' => 'nullable|required_if:has_charges,1|exists:fees,id',
            'charge_type' => 'nullable|required_if:has_charges,1|in:fixed,percentage',
            'charge_amount' => 'nullable|required_if:has_charges,1|numeric|min:0',
            'journal_reference_id' => 'required|exists:journal_references,id',
            'hrms_code' => 'nullable|string|max:255',
            'liability_account_id' => 'required|exists:chart_accounts,id',
            'share_capital_account_id' => 'nullable|exists:chart_accounts,id',
            'fee_income_account_id' => 'nullable|exists:chart_accounts,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['allow_dividends_for_inactive_member'] = $request->allow_dividends_for_inactive_member === 'Yes';
        $data['has_charges'] = $request->has('has_charges');
        $data['auto_generate_certificate'] = $request->has('auto_generate_certificate');
        $data['allow_new_subscriptions'] = $request->has('allow_new_subscriptions');
        $data['allow_additional_purchases'] = $request->has('allow_additional_purchases');
        $data['allow_share_transfers'] = $request->has('allow_share_transfers');
        $data['allow_share_withdrawals'] = $request->has('allow_share_withdrawals');
        $data['allow_partial_withdrawal'] = $request->has('allow_partial_withdrawal');
        
        // Clear charge fields if has_charges is not checked
        if (!$data['has_charges']) {
            $data['charge_id'] = null;
            $data['charge_type'] = null;
            $data['charge_amount'] = null;
        }

        // Handle empty string dates
        $data['opening_date'] = !empty($data['opening_date']) ? $data['opening_date'] : null;
        $data['closing_date'] = !empty($data['closing_date']) ? $data['closing_date'] : null;
        
        // Set income_account_id to null if not provided
        $data['income_account_id'] = $data['income_account_id'] ?? null;

        ShareProduct::create($data);

        return redirect()->route('shares.products.index')
            ->with('success', 'Share product created successfully.');
    }

    /**
     * Display the specified share product
     */
    public function show($encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('shares.products.index')
                ->with('error', 'Share product not found.');
        }

        $shareProduct = ShareProduct::with([
            'journalReference',
            'liabilityAccount',
            'incomeAccount',
            'shareCapitalAccount',
            'charge'
        ])->findOrFail($decoded[0]);

        return view('shares.products.show', compact('shareProduct'));
    }

    /**
     * Show the form for editing the specified share product
     */
    public function edit($id)
    {
        $decoded = Hashids::decode($id);
        if (empty($decoded)) {
            abort(404, 'Share product not found.');
        }
        $shareProduct = ShareProduct::findOrFail($decoded[0]);
        $chartAccounts = ChartAccount::all();

        // Get fees for charges dropdown
        $fees = Fee::where('status', 'active')->get();
        
        // Get journal references (filtered by company and branch like in JournalReferenceController)
        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;
        
        $journalReferences = JournalReference::where('company_id', $companyId)
            ->where(function($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $periodTypes = [
            'Days' => 'Days',
            'Weeks' => 'Weeks',
            'Months' => 'Months',
            'Years' => 'Years',
        ];

        $yesNoOptions = [
            'Yes' => 'Yes',
            'No' => 'No',
        ];

        // Define charge type options
        $chargeTypes = [
            'fixed' => 'Fixed Amount',
            'percentage' => 'Percentage',
        ];

        // Define dividend calculation methods
        $dividendCalculationMethods = [
            'on_share_capital' => 'On Share Capital',
            'on_share_value' => 'On Share Value',
            'on_minimum_balance' => 'On Minimum Balance',
            'on_average_balance' => 'On Average Balance',
        ];

        // Define dividend payment frequencies
        $dividendPaymentFrequencies = [
            'Monthly' => 'Monthly',
            'Quarterly' => 'Quarterly',
            'Semi_Annually' => 'Semi-Annually',
            'Annually' => 'Annually',
        ];

        // Months for dropdown
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = date('F', mktime(0, 0, 0, $i, 1));
        }

        // Days for dropdown (1-31)
        $days = [];
        for ($i = 1; $i <= 31; $i++) {
            $days[$i] = $i;
        }

        return view('shares.products.edit', compact(
            'shareProduct',
            'chartAccounts',
            'fees',
            'journalReferences',
            'periodTypes',
            'yesNoOptions',
            'chargeTypes',
            'dividendCalculationMethods',
            'dividendPaymentFrequencies',
            'months',
            'days'
        ));
    }

    /**
     * Update the specified share product
     */
    public function update(Request $request, $id)
    {
        $decoded = Hashids::decode($id);
        if (empty($decoded)) {
            abort(404, 'Share product not found.');
        }
        $shareProduct = ShareProduct::findOrFail($decoded[0]);

        $validator = Validator::make($request->all(), [
            'share_name' => 'required|string|max:255|unique:share_products,share_name,' . $shareProduct->id,
            'required_share' => 'required|numeric|min:0',
            'nominal_price' => 'required|numeric|min:0',
            'minimum_purchase_amount' => 'nullable|numeric|min:0',
            'maximum_purchase_amount' => 'nullable|numeric|min:0',
            'maximum_shares_per_member' => 'nullable|numeric|min:0',
            'minimum_shares_for_membership' => 'nullable|numeric|min:0',
            'share_purchase_increment' => 'nullable|numeric|min:0',
            'minimum_active_period' => 'required|integer|min:1',
            'minimum_active_period_type' => 'required|string|in:Days,Weeks,Months,Years',
            'allow_dividends_for_inactive_member' => 'required|string|in:Yes,No',
            'dividend_rate' => 'nullable|numeric|min:0|max:100',
            'dividend_calculation_method' => 'nullable|string|in:on_share_capital,on_share_value,on_minimum_balance,on_average_balance',
            'dividend_payment_frequency' => 'nullable|string|in:Monthly,Quarterly,Semi_Annually,Annually',
            'dividend_payment_month' => 'nullable|integer|min:1|max:12',
            'dividend_payment_day' => 'nullable|integer|min:1|max:31',
            'minimum_balance_for_dividend' => 'nullable|numeric|min:0',
            'lockin_period_frequency' => 'required|integer|min:1',
            'lockin_period_frequency_type' => 'required|string|in:Days,Weeks,Months,Years',
            'description' => 'nullable|string',
            'certificate_number_prefix' => 'nullable|string|max:20',
            'certificate_number_format' => 'nullable|string|max:100',
            'auto_generate_certificate' => 'boolean',
            'opening_date' => 'nullable|date',
            'closing_date' => 'nullable|date|after_or_equal:opening_date',
            'allow_new_subscriptions' => 'boolean',
            'allow_additional_purchases' => 'boolean',
            'maximum_total_shares' => 'nullable|numeric|min:0',
            'allow_share_transfers' => 'boolean',
            'transfer_fee' => 'nullable|numeric|min:0',
            'transfer_fee_type' => 'nullable|string|in:fixed,percentage',
            'allow_share_withdrawals' => 'boolean',
            'withdrawal_fee' => 'nullable|numeric|min:0',
            'withdrawal_fee_type' => 'nullable|string|in:fixed,percentage',
            'withdrawal_notice_period' => 'nullable|integer|min:0',
            'withdrawal_notice_period_type' => 'nullable|string|in:Days,Weeks,Months',
            'minimum_withdrawal_amount' => 'nullable|numeric|min:0',
            'maximum_withdrawal_amount' => 'nullable|numeric|min:0',
            'allow_partial_withdrawal' => 'boolean',
            'has_charges' => 'boolean',
            'charge_id' => 'nullable|required_if:has_charges,1|exists:fees,id',
            'charge_type' => 'nullable|required_if:has_charges,1|in:fixed,percentage',
            'charge_amount' => 'nullable|required_if:has_charges,1|numeric|min:0',
            'journal_reference_id' => 'required|exists:journal_references,id',
            'hrms_code' => 'nullable|string|max:255',
            'liability_account_id' => 'required|exists:chart_accounts,id',
            'share_capital_account_id' => 'nullable|exists:chart_accounts,id',
            'fee_income_account_id' => 'nullable|exists:chart_accounts,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['allow_dividends_for_inactive_member'] = $request->allow_dividends_for_inactive_member === 'Yes';
        $data['has_charges'] = $request->has('has_charges');
        $data['auto_generate_certificate'] = $request->has('auto_generate_certificate');
        $data['allow_new_subscriptions'] = $request->has('allow_new_subscriptions');
        $data['allow_additional_purchases'] = $request->has('allow_additional_purchases');
        $data['allow_share_transfers'] = $request->has('allow_share_transfers');
        $data['allow_share_withdrawals'] = $request->has('allow_share_withdrawals');
        $data['allow_partial_withdrawal'] = $request->has('allow_partial_withdrawal');
        
        // Clear charge fields if has_charges is not checked
        if (!$data['has_charges']) {
            $data['charge_id'] = null;
            $data['charge_type'] = null;
            $data['charge_amount'] = null;
        }

        // Handle empty string dates
        $data['opening_date'] = !empty($data['opening_date']) ? $data['opening_date'] : null;
        $data['closing_date'] = !empty($data['closing_date']) ? $data['closing_date'] : null;
        
        // Set income_account_id to null if not provided
        $data['income_account_id'] = $data['income_account_id'] ?? null;

        $shareProduct->update($data);

        return redirect()->route('shares.products.index')
            ->with('success', 'Share product updated successfully.');
    }

    /**
     * Remove the specified share product
     */
    public function destroy($id)
    {
        $decoded = Hashids::decode($id);
        if (empty($decoded)) {
            abort(404, 'Share product not found.');
        }
        $shareProduct = ShareProduct::findOrFail($decoded[0]);
        $shareProduct->delete();

        return redirect()->route('shares.products.index')
            ->with('success', 'Share product deleted successfully.');
    }

    /**
     * Toggle the active status of a share product
     */
    public function toggleStatus($encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('shares.products.index')
                ->with('error', 'Share product not found.');
        }

        $shareProduct = ShareProduct::findOrFail($decoded[0]);

        try {
            $shareProduct->update([
                'is_active' => !$shareProduct->is_active
            ]);

            $status = $shareProduct->is_active ? 'activated' : 'deactivated';
            return redirect()->route('shares.products.index')
                ->with('success', "Share product {$status} successfully!");
        } catch (\Exception $e) {
            return redirect()->route('shares.products.index')
                ->with('error', 'Error updating share product status: ' . $e->getMessage());
        }
    }
}
