<?php

namespace App\Http\Controllers;

use App\Models\ShareAccount;
use App\Models\Customer;
use App\Models\ShareProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;

class ShareAccountController extends Controller
{
    /**
     * Display a listing of share accounts
     */
    public function index()
    {
        return view('shares.accounts.index');
    }

    /**
     * Ajax endpoint for DataTables
     */
    public function getShareAccountsData(Request $request)
    {
        if ($request->ajax()) {
            try {
                $shareAccounts = ShareAccount::with([
                    'customer',
                    'shareProduct',
                    'branch',
                    'company'
                ])->select('share_accounts.*');

                return DataTables::eloquent($shareAccounts)
                ->addIndexColumn()
                ->addColumn('customer_name', function ($account) {
                    return $account->customer->name ?? 'N/A';
                })
                ->addColumn('customer_number', function ($account) {
                    return $account->customer->customerNo ?? 'N/A';
                })
                ->addColumn('share_product_name', function ($account) {
                    return $account->shareProduct->share_name ?? 'N/A';
                })
                ->addColumn('share_balance_formatted', function ($account) {
                    return number_format($account->share_balance, 2);
                })
                ->addColumn('nominal_value_formatted', function ($account) {
                    return number_format($account->nominal_value, 2);
                })
                ->addColumn('opening_date_formatted', function ($account) {
                    return $account->opening_date ? $account->opening_date->format('Y-m-d') : 'N/A';
                })
                ->addColumn('status_badge', function ($account) {
                    $badges = [
                        'active' => '<span class="badge bg-success">Active</span>',
                        'inactive' => '<span class="badge bg-warning">Inactive</span>',
                        'closed' => '<span class="badge bg-danger">Closed</span>',
                    ];
                    return $badges[$account->status] ?? '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('actions', function ($account) {
                    $actions = '';
                    $encodedId = Hashids::encode($account->id);

                    // View action
                    $actions .= '<a href="' . route('shares.accounts.show', $encodedId) . '" class="btn btn-sm btn-info me-1" title="View"><i class="bx bx-show"></i></a>';

                    // Edit action
                    $actions .= '<a href="' . route('shares.accounts.edit', $encodedId) . '" class="btn btn-sm btn-warning me-1" title="Edit"><i class="bx bx-edit"></i></a>';

                    // Delete action
                    $actions .= '<button class="btn btn-sm btn-danger delete-btn" data-id="' . $encodedId . '" data-name="' . e($account->account_number) . '" title="Delete"><i class="bx bx-trash"></i></button>';

                    return '<div class="text-center d-flex justify-content-center gap-1">' . $actions . '</div>';
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
            } catch (\Exception $e) {
                Log::error('Share Accounts DataTable Error: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                
                // Check if table doesn't exist
                if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), 'Base table or view not found')) {
                    return response()->json([
                        'draw' => $request->input('draw', 0),
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => [],
                        'error' => 'Share accounts table does not exist. Please run: php artisan migrate'
                    ], 500);
                }
                
                return response()->json([
                    'draw' => $request->input('draw', 0),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Failed to load data: ' . $e->getMessage()
                ], 500);
            }
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Show the form for creating a new share account
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $shareProducts = ShareProduct::where('is_active', true)->orderBy('share_name')->get();

        return view('shares.accounts.create', compact('customers', 'shareProducts'));
    }

    /**
     * Store a newly created share account(s)
     */
    public function store(Request $request)
    {
        // Validate multiple lines
        $rules = [];
        $messages = [];

        if ($request->has('lines')) {
            foreach ($request->lines as $index => $line) {
                $rules["lines.{$index}.customer_id"] = 'required|exists:customers,id';
                $rules["lines.{$index}.share_product_id"] = 'required|exists:share_products,id';
                $rules["lines.{$index}.opening_date"] = 'required|date';
                $rules["lines.{$index}.notes"] = 'nullable|string';

                $messages["lines.{$index}.customer_id.required"] = "Line " . ($index + 1) . ": Member name is required";
                $messages["lines.{$index}.share_product_id.required"] = "Line " . ($index + 1) . ": Share product is required";
                $messages["lines.{$index}.opening_date.required"] = "Line " . ($index + 1) . ": Opening date is required";
            }
        } else {
            // Fallback to single line validation
            $rules = [
                'customer_id' => 'required|exists:customers,id',
                'share_product_id' => 'required|exists:share_products,id',
                'opening_date' => 'required|date',
                'notes' => 'nullable|string',
            ];
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        // Additional validation: Check for duplicates within the same request
        $lines = $request->lines ?? [
            [
                'customer_id' => $request->customer_id,
                'share_product_id' => $request->share_product_id,
                'opening_date' => $request->opening_date,
                'notes' => $request->notes,
            ]
        ];

        // Check for duplicates within the same request (same customer + share product combination)
        $combinations = [];
        foreach ($lines as $index => $line) {
            if (!empty($line['customer_id']) && !empty($line['share_product_id'])) {
                $combination = $line['customer_id'] . '_' . $line['share_product_id'];
                if (isset($combinations[$combination])) {
                    $validator->errors()->add(
                        "lines.{$index}.customer_id",
                        "Line " . ($index + 1) . ": This member already has this share product selected in another line."
                    );
                } else {
                    $combinations[$combination] = $index;
                }
            }
        }

        // Check for duplicates against existing records in database
        foreach ($lines as $index => $line) {
            if (!empty($line['customer_id']) && !empty($line['share_product_id'])) {
                // Check if this combination already exists in the database
                $exists = ShareAccount::where('customer_id', $line['customer_id'])
                    ->where('share_product_id', $line['share_product_id'])
                    ->exists();
                
                if ($exists) {
                    $customer = Customer::find($line['customer_id']);
                    $product = ShareProduct::find($line['share_product_id']);
                    $customerName = $customer ? $customer->name : 'Unknown';
                    $productName = $product ? $product->share_name : 'Unknown';
                    
                    $validator->errors()->add(
                        "lines.{$index}.customer_id",
                        "Line " . ($index + 1) . ": Member \"{$customerName}\" already has a share account for product \"{$productName}\"."
                    );
                }
            }
        }

        // Validate fails will be true if there are any errors (either from validation rules or manually added)
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $createdCount = 0;
        $createdInBatch = []; // Track what we're creating in this batch to prevent duplicates

        foreach ($lines as $lineIndex => $line) {
            // Skip empty lines
            if (empty($line['customer_id']) || empty($line['share_product_id'])) {
                continue;
            }

            $combination = $line['customer_id'] . '_' . $line['share_product_id'];
            
            // Final safety check: Skip if we've already created this combination in this batch
            // This prevents duplicates even if validation somehow passes
            if (isset($createdInBatch[$combination])) {
                continue; // Skip duplicate within the same batch - don't save
            }
            
            // Final check: Verify this combination doesn't exist in database before creating
            $exists = ShareAccount::where('customer_id', $line['customer_id'])
                ->where('share_product_id', $line['share_product_id'])
                ->exists();
            
            if ($exists) {
                // This should have been caught by validation, but skip just in case
                continue; // Don't save if duplicate exists
            }

            // Generate account number
            $accountNumber = $this->generateAccountNumber();

            // Get share product to get nominal price
            $shareProduct = ShareProduct::findOrFail($line['share_product_id']);

            ShareAccount::create([
                'customer_id' => $line['customer_id'],
                'share_product_id' => $line['share_product_id'],
                'account_number' => $accountNumber,
                'share_balance' => 0,
                'nominal_value' => $shareProduct->nominal_price ?? 0,
                'opening_date' => $line['opening_date'],
                'notes' => $line['notes'] ?? null,
                'status' => 'active',
                'branch_id' => auth()->user()->branch_id ?? null,
                'company_id' => auth()->user()->company_id ?? null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            // Mark this combination as created in this batch
            $createdInBatch[$combination] = true;
            $createdCount++;
        }

        if ($createdCount > 0) {
            return redirect()->route('shares.accounts.index')
                ->with('success', $createdCount . ' share account(s) created successfully.');
        } else {
            return redirect()->back()
                ->with('error', 'No valid accounts to create.');
        }
    }

    /**
     * Display the specified share account
     */
    public function show($id)
    {
        $decoded = Hashids::decode($id);
        if (empty($decoded)) {
            abort(404, 'Share account not found.');
        }
        
        $shareAccount = ShareAccount::with([
            'customer',
            'shareProduct',
            'branch',
            'company',
            'createdBy',
            'updatedBy'
        ])->findOrFail($decoded[0]);

        return view('shares.accounts.show', compact('shareAccount'));
    }

    /**
     * Show the form for editing the specified share account
     */
    public function edit($id)
    {
        $decoded = Hashids::decode($id);
        if (empty($decoded)) {
            abort(404, 'Share account not found.');
        }
        $shareAccount = ShareAccount::findOrFail($decoded[0]);
        $customers = Customer::orderBy('name')->get();
        $shareProducts = ShareProduct::where('is_active', true)->orderBy('share_name')->get();

        return view('shares.accounts.edit', compact('shareAccount', 'customers', 'shareProducts'));
    }

    /**
     * Update the specified share account
     */
    public function update(Request $request, $id)
    {
        $decoded = Hashids::decode($id);
        if (empty($decoded)) {
            abort(404, 'Share account not found.');
        }
        $shareAccount = ShareAccount::findOrFail($decoded[0]);

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'share_product_id' => 'required|exists:share_products,id',
            'opening_date' => 'required|date',
            'status' => 'required|in:active,inactive,closed',
            'notes' => 'nullable|string',
        ]);

        // Check for duplicate: same customer + share product combination (excluding current account)
        if ($request->customer_id != $shareAccount->customer_id || 
            $request->share_product_id != $shareAccount->share_product_id) {
            
            $exists = ShareAccount::where('customer_id', $request->customer_id)
                ->where('share_product_id', $request->share_product_id)
                ->where('id', '!=', $shareAccount->id)
                ->exists();
            
            if ($exists) {
                $customer = Customer::find($request->customer_id);
                $product = ShareProduct::find($request->share_product_id);
                $customerName = $customer ? $customer->name : 'Unknown';
                $productName = $product ? $product->share_name : 'Unknown';
                
                $validator->errors()->add(
                    'customer_id',
                    "Member \"{$customerName}\" already has a share account for product \"{$productName}\"."
                );
            }
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['updated_by'] = auth()->id();

        // Update nominal value if share product changed
        if ($request->share_product_id != $shareAccount->share_product_id) {
            $shareProduct = ShareProduct::findOrFail($request->share_product_id);
            $data['nominal_value'] = $shareProduct->nominal_price ?? 0;
        }

        $shareAccount->update($data);

        return redirect()->route('shares.accounts.index')
            ->with('success', 'Share account updated successfully.');
    }

    /**
     * Remove the specified share account
     */
    public function destroy($id)
    {
        try {
            $decoded = Hashids::decode($id);
            if (empty($decoded)) {
                abort(404, 'Share account not found.');
            }
            $shareAccount = ShareAccount::findOrFail($decoded[0]);
            $accountNumber = $shareAccount->account_number;
            $shareAccount->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Share account deleted successfully.'
                ]);
            }

            return redirect()->route('shares.accounts.index')
                ->with('success', 'Share account deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Share Account Delete Error: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete share account: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to delete share account. Please try again.');
        }
    }

    /**
     * Generate unique account number
     */
    private function generateAccountNumber()
    {
        do {
            $accountNumber = 'SA' . strtoupper(Str::random(8));
        } while (ShareAccount::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }
}

