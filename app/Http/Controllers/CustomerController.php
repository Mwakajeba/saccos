<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Region;
use App\Models\District;
use App\Models\User;
use App\Models\CashCollateralType;
use App\Models\Filetype;
use App\Services\LoanPenaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use App\Helpers\HashidsHelper;
use Yajra\DataTables\Facades\DataTables;

set_time_limit(0);              // no limit for this request
ini_set('max_execution_time', 0);

class CustomerController extends Controller
{
    /**
     * Format phone number to standard format (255 prefix, no +)
     * - If starts with 0, remove 0 and add 255
     * - If starts with +255, remove + to get 255
     * - Otherwise return as is
     */
    private function formatPhoneNumber($phoneNumber)
    {
        if (empty($phoneNumber)) {
            return $phoneNumber;
        }

        // Remove any spaces, dashes, or special characters except +
        $phoneNumber = preg_replace("/[^0-9+]/", "", $phoneNumber);

        // If starts with 0, remove 0 and add 255
        if (substr($phoneNumber, 0, 1) === "0") {
            return "255" . substr($phoneNumber, 1);
        }

        // If starts with +255, remove + to get 255
        if (substr($phoneNumber, 0, 4) === "+255") {
            return substr($phoneNumber, 1);
        }

        // Return as is if already in correct format (255 prefix)
        return $phoneNumber;
    }

    /**
     * Format phone number to ensure 255 prefix (without +)
     * - Input should be 12 digits starting with 255 OR 9 digits
     * - Returns 255 followed by the 9 digits
     */
    private function formatPhoneNumberWithPrefix($phoneNumber)
    {
        if (empty($phoneNumber)) {
            return $phoneNumber;
        }

        // Remove any spaces, dashes, or special characters
        $phoneNumber = preg_replace("/[^0-9+]/", "", $phoneNumber);

        // If it already starts with +255, remove + and return
        if (substr($phoneNumber, 0, 4) === "+255") {
            return substr($phoneNumber, 1);
        }

        // If it starts with 255, return as is
        if (substr($phoneNumber, 0, 3) === "255" && strlen($phoneNumber) === 12) {
            return $phoneNumber;
        }

        // If it starts with 0, remove 0 and add 255
        if (substr($phoneNumber, 0, 1) === "0" && strlen($phoneNumber) === 10) {
            return "255" . substr($phoneNumber, 1);
        }

        // If it's 9 digits, add 255 prefix
        if (strlen($phoneNumber) === 9) {
            return "255" . $phoneNumber;
        }

        // Return as is
        return $phoneNumber;
    }

    // Display all customers
    public function index()
    {
        $branchId = auth()->user()->branch_id;
        $activeCount = Customer::where('status', 'active')->where('branch_id', $branchId)->count();
        $inactiveCount = Customer::where('status', 'inactive')->where('branch_id', $branchId)->count();

        return view('customers.index', compact('activeCount', 'inactiveCount'));
    }

    // Ajax endpoint for DataTables
    public function getCustomersData(Request $request)
    {
        try {
            if ($request->ajax()) {
                $user = auth()->user();
                $branchId = $user->branch_id;
                
                if (!$branchId) {
                    return response()->json(['error' => 'User branch not found'], 400);
                }

                // Check permissions once before the loop
                $canView = $user->can('view customer profile');
                $canEdit = $user->can('edit customer');
                $canDelete = $user->can('delete customer');

                // Build query with eager loading to avoid N+1 queries
                // Exclude large columns like photo, document, password to improve performance
                $customers = Customer::with([
                        'branch' => function($query) {
                            $query->select('id', 'name');
                        },
                        'region' => function($query) {
                            $query->select('id', 'name');
                        },
                        'district' => function($query) {
                            $query->select('id', 'name');
                        }
                    ])
                    ->where('branch_id', $branchId)
                    ->select([
                        'customers.id',
                        'customers.customerNo',
                        'customers.name',
                        'customers.phone1',
                        'customers.category',
                        'customers.status',
                        'customers.branch_id',
                        'customers.region_id',
                        'customers.district_id',
                        'customers.sex',
                        'customers.dateRegistered',
                        'customers.created_at',
                        'customers.updated_at'
                    ]);

                // Filter by status if provided and not empty
                if ($request->filled('status')) {
                    $customers->where('status', $request->status);
                }

                return DataTables::eloquent($customers)
                    ->addColumn('avatar_name', function ($customer) {
                        $isGuarantor = isset($customer->category) && strtolower($customer->category) === 'guarantor';
                        $avatarClass = $isGuarantor ? 'bg-success' : 'bg-primary';
                        $initial = strtoupper(substr($customer->name ?? '', 0, 1));

                        return '<div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm ' . $avatarClass . ' rounded-circle me-2 d-flex align-items-center justify-content-center shadow" style="width:36px; height:36px;">
                                        <span class="avatar-title text-white fw-bold" style="font-size:1.25rem;">' . $initial . '</span>
                                    </div>
                                    <div>
                                        <div class="fw-bold">' . e($customer->name ?? '') . '</div>
                                    </div>
                                </div>';
                    })
                    ->addColumn('region_name', function ($customer) {
                        try {
                            return $customer->region ? $customer->region->name : '';
                        } catch (\Exception $e) {
                            return '';
                        }
                    })
                    ->addColumn('district_name', function ($customer) {
                        try {
                            return $customer->district ? $customer->district->name : '';
                        } catch (\Exception $e) {
                            return '';
                        }
                    })
                    ->addColumn('branch_name', function ($customer) {
                        try {
                            return $customer->branch ? $customer->branch->name : '';
                        } catch (\Exception $e) {
                            return '';
                        }
                    })
                    ->addColumn('actions', function ($customer) use ($canView, $canEdit, $canDelete) {
                        try {
                            $actions = '';
                            
                            try {
                                $encodedId = HashidsHelper::encode($customer->id);
                            } catch (\RuntimeException $e) {
                                \Log::error('Hashids encode error in actions column: ' . $e->getMessage());
                                // Return error message if Hashids fails
                                return '<div class="text-center text-danger">Error encoding ID</div>';
                            }

                            // View action
                            if ($canView) {
                                $actions .= '<a href="' . route('customers.show', $encodedId) . '" class="btn btn-sm btn-outline-info me-1" title="View"><i class="bx bx-show"></i> Show</a>';
                            }

                            // Edit action
                            if ($canEdit) {
                                $actions .= '<a href="' . route('customers.edit', $encodedId) . '" class="btn btn-sm btn-outline-primary me-1" title="Edit"><i class="bx bx-edit"></i> Edit</a>';
                            }

                            // Block/Unblock action
                            if ($canEdit) {
                                $status = $customer->status ?? 'active';
                                $isActive = $status === 'active';
                                $btnClass = $isActive ? 'btn-outline-warning' : 'btn-outline-success';
                                $btnIcon = $isActive ? 'bx-block' : 'bx-check-circle';
                                $btnText = $isActive ? 'Block' : 'Unblock';
                                $actions .= '<button class="btn btn-sm ' . $btnClass . ' toggle-status-btn me-1" data-id="' . $encodedId . '" data-name="' . e($customer->name) . '" data-status="' . $status . '" title="' . $btnText . ' Customer"><i class="bx ' . $btnIcon . '"></i> ' . $btnText . '</button>';
                            }

                            // Delete action
                            if ($canDelete) {
                                $actions .= '<button class="btn btn-sm btn-outline-danger delete-btn" data-id="' . $encodedId . '" data-name="' . e($customer->name) . '" title="Delete"><i class="bx bx-trash"></i> Delete</button>';
                            }

                            return '<div class="text-center">' . $actions . '</div>';
                        } catch (\Exception $e) {
                            \Log::error('Error in actions column: ' . $e->getMessage());
                            return '<div class="text-center text-danger">Error</div>';
                        }
                    })
                    ->orderColumn('avatar_name', 'customers.name $1')
                    ->rawColumns(['avatar_name', 'actions'])
                    ->make(true);
            }

            return response()->json(['error' => 'Invalid request'], 400);
        } catch (\Exception $e) {
            \Log::error('Error in getCustomersData: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'An error occurred while loading customers data: ' . $e->getMessage()], 500);
        }
    }



    /////////DISPLAY ALL CUSTOMER WITH PENALTY AMOUNT  FOR THEIR LAON ///////
    public function penaltList()
    {
        $customerPenalties = LoanPenaltyService::getCustomerPenaltyBalances();
        $penaltyBalance = LoanPenaltyService::getTotalPenaltyBalance();
        return view('customers.penalty', compact('customerPenalties', 'penaltyBalance'));
    }

    // Show form to create a new customer
    public function create()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;
        $loanOfficers = User::where('branch_id', $branchId)->get();
        $filetypes = Filetype::orderBy('name')->get();
        $branches = Branch::all();
        $companies = Company::all();
        $registrars = User::all();
        $regions = Region::all();
        $groups = \App\Models\Group::where('branch_id', $branchId)->where('id', '!=', 1)->get();

        // Get share products (share products don't have branch_id/company_id)
        $shareProducts = \App\Models\ShareProduct::where('is_active', true)
            ->orderBy('share_name')
            ->get();
        
        // Get contribution products for the current branch/company
        $contributionProducts = \App\Models\ContributionProduct::where('is_active', true)
            ->where('branch_id', $branchId)
            ->where('company_id', $companyId)
            ->orderBy('product_name')
            ->get();

        $customer = null;
        return view('customers.create', compact('branches', 'companies', 'registrars', 'regions', 'loanOfficers', 'filetypes', 'groups', 'customer', 'shareProducts', 'contributionProducts'));
    }

    // Store a new customer
    public function store(Request $request)
    {
        // Basic validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'phone1' => ['required', 'string', 'regex:/^255[0-9]{9}$/', 'unique:customers,phone1'],
            'phone2' => ['nullable', 'string', 'regex:/^255[0-9]{9}$/', 'unique:customers,phone2'],
            'email' => 'nullable|email|max:255',
            'reference' => 'nullable|string|max:255',
            'dob' => ['required', 'date', 'before_or_equal:' . now()->subYears(18)->format('Y-m-d')],
            'sex' => 'required|in:M,F',
            'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'region_id' => 'required|exists:regions,id',
            'district_id' => 'required|exists:districts,id',
            'street' => 'nullable|string|max:500',
            'work' => 'nullable|string|max:255',
            'workAddress' => 'nullable|string|max:500',
            'employment_status' => 'nullable|in:Employed,Self Employed,Unemployed,Student,Retired',
            'idType' => 'nullable|string|max:100',
            'idNumber' => 'nullable|string|max:100',
            'relation' => 'nullable|string|max:255',
            'number_of_spouse' => 'nullable|integer|min:0|max:10',
            'number_of_children' => 'nullable|integer|min:0|max:50',
            'monthly_income' => 'nullable|numeric|min:0',
            'monthly_expenses' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'category' => 'required|in:Member,Guarantor,Borrower',
            'group_id' => 'nullable|exists:groups,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'loan_officer_ids' => 'nullable|array',
            'loan_officer_ids.*' => 'exists:users,id',
            'has_shares' => 'nullable|boolean',
            'share_product_id' => 'nullable|required_if:has_shares,1|exists:share_products,id',
            'has_contributions' => 'nullable|boolean',
            'contribution_product_id' => 'nullable|required_if:has_contributions,1|exists:contribution_products,id',

            // Dynamic filetypes + documents
            'filetypes' => 'nullable|array',
            'filetypes.*' => 'exists:filetypes,id',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ];

        $validated = $request->validate($rules);

        // Validate shares and contributions
        if ($request->has('has_shares') && !$request->share_product_id) {
            return back()->withErrors(['share_product_id' => 'Please select a share product when creating share account.'])->withInput();
        }

        if ($request->has('has_contributions') && !$request->contribution_product_id) {
            return back()->withErrors(['contribution_product_id' => 'Please select a contribution product when creating contribution account.'])->withInput();
        }

        // Prepare customer data
        $data = $request->except(['customerNo', 'loan_officer_ids', 'filetypes', 'documents', 'group_id', 'has_shares', 'share_product_id', 'has_contributions', 'contribution_product_id']);
        
        // Format phone numbers - ensure +255 prefix
        $data["phone1"] = $this->formatPhoneNumberWithPrefix($data["phone1"]);
        if (!empty($data["phone2"])) {
            $data["phone2"] = $this->formatPhoneNumberWithPrefix($data["phone2"]);
        }
        $data['category'] = $request->category;
        $password = '1234567890';
        $date = now()->toDateString();

        $data['customerNo'] = 100000 + (\App\Models\Customer::max('id') ?? 0) + 1;
        $data['password'] = Hash::make($password);
        $data['branch_id'] = auth()->user()->branch_id;
        $data['company_id'] = auth()->user()->company_id;
        $data['registrar'] = auth()->id();
        $data['dateRegistered'] = $date;

        // Upload photo
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }

        // Upload document
        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store('documents', 'public');
        }

        DB::beginTransaction();
        try {
            $customer = \App\Models\Customer::create($data);

            // Save group membership - check if customer is already in a group first
            $existingMembership = DB::table('group_members')->where('customer_id', $customer->id)->first();

            if (!$existingMembership) {
                if ($request->filled('group_id')) {
                    DB::table('group_members')->insert([
                        'group_id' => $request->group_id,
                        'customer_id' => $customer->id,
                        'status' => 'active',
                        'joined_date' => now()->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('group_members')->insert([
                        'group_id' => 1,
                        'customer_id' => $customer->id,
                        'status' => 'active',
                        'joined_date' => now()->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Attach loan officers
            if ($request->has('loan_officer_ids')) {
                foreach ($request->loan_officer_ids as $officerId) {
                    DB::table('customer_officer')->insert([
                        'customer_id' => $customer->id,
                        'officer_id' => $officerId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Create share account if selected
            if ($request->has('has_shares') && $request->share_product_id) {
                $shareProduct = \App\Models\ShareProduct::find($request->share_product_id);
                $accountNumber = $this->generateShareAccountNumber();
                
                \App\Models\ShareAccount::create([
                    'customer_id' => $customer->id,
                    'share_product_id' => $request->share_product_id,
                    'account_number' => $accountNumber,
                    'share_balance' => 0,
                    'nominal_value' => $shareProduct->nominal_price ?? 0,
                    'opening_date' => now()->toDateString(),
                    'status' => 'active',
                    'branch_id' => auth()->user()->branch_id,
                    'company_id' => auth()->user()->company_id,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            }

            // Create contribution account if selected
            if ($request->has('has_contributions') && $request->contribution_product_id) {
                $accountNumber = $this->generateContributionAccountNumber();
                
                \App\Models\ContributionAccount::create([
                    'customer_id' => $customer->id,
                    'contribution_product_id' => $request->contribution_product_id,
                    'account_number' => $accountNumber,
                    'balance' => 0,
                    'opening_date' => now()->toDateString(),
                    'branch_id' => auth()->user()->branch_id,
                    'company_id' => auth()->user()->company_id,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            }

            // Save uploaded filetypes + documents
            if ($request->has('filetypes') && $request->hasFile('documents')) {
                $filetypes = $request->input('filetypes');
                $documents = $request->file('documents');

                foreach ($filetypes as $index => $filetypeId) {
                    if (isset($documents[$index])) {
                        $file = $documents[$index];
                        $path = $file->store('documents', 'public');

                        DB::table('customer_file_types')->insert([
                            'customer_id' => $customer->id,
                            'filetype_id' => $filetypeId,
                            'document_path' => $path,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create customer: ' . $e->getMessage());
        }
    }


    // Display one customer
    public function show($encodedId)
    {
        $decoded = HashidsHelper::decode($encodedId);
        $id = $decoded[0] ?? null;

        if (!$id) {
            abort(404);
        }

        $customer = Customer::with('collaterals.type', 'loans', 'loanOfficers', 'filetypes', 'shareAccounts.shareProduct', 'contributionAccounts.contributionProduct', 'nextOfKin')->findOrFail($id);

        return view('customers.show', compact('customer'));
    }

    // Show form to edit a customer
    public function edit($encodedId)
    {
        $decoded = HashidsHelper::decode($encodedId);
        $id = $decoded[0] ?? null;
        if (!$id) {
            abort(404);
        }
        $customer = Customer::findOrFail($id);
        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;
        $loanOfficers = User::where('branch_id', $branchId)->get();
        $branches = \App\Models\Branch::all();
        $companies = \App\Models\Company::all();
        $registrars = \App\Models\User::all();
        $regions = \App\Models\Region::all();
        $filetypes = \App\Models\Filetype::orderBy('name')->get();
        $groups = \App\Models\Group::where('branch_id', $branchId)->get();

        // Get share products (share products don't have branch_id/company_id)
        $shareProducts = \App\Models\ShareProduct::where('is_active', true)
            ->orderBy('share_name')
            ->get();
        
        // Get contribution products for the current branch/company
        $contributionProducts = \App\Models\ContributionProduct::where('is_active', true)
            ->where('branch_id', $branchId)
            ->where('company_id', $companyId)
            ->orderBy('product_name')
            ->get();

        $customer->load('loanOfficers', 'filetypes');
        return view('customers.edit', compact('branches', 'companies', 'registrars', 'regions', 'loanOfficers', 'customer', 'filetypes', 'groups', 'shareProducts', 'contributionProducts'));
    }

    // Update customer data
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'phone1' => ['required', 'string', 'regex:/^255[0-9]{9}$/', 'unique:customers,phone1,' . $customer->id],
            'phone2' => ['nullable', 'string', 'regex:/^255[0-9]{9}$/', 'unique:customers,phone2,' . $customer->id],
            'email' => 'nullable|email|max:255',
            'reference' => 'nullable|string|max:255',
            'dob' => 'required|date',
            'sex' => 'required|in:M,F',
            'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'region_id' => 'required|exists:regions,id',
            'district_id' => 'required|exists:districts,id',
            'street' => 'nullable|string|max:500',
            'work' => 'nullable|string|max:255',
            'workAddress' => 'nullable|string|max:500',
            'employment_status' => 'nullable|in:Employed,Self Employed,Unemployed,Student,Retired',
            'idType' => 'nullable|string|max:100',
            'idNumber' => 'nullable|string|max:100',
            'relation' => 'nullable|string|max:255',
            'number_of_spouse' => 'nullable|integer|min:0|max:10',
            'number_of_children' => 'nullable|integer|min:0|max:50',
            'monthly_income' => 'nullable|numeric|min:0',
            'monthly_expenses' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'category' => 'required|in:Member,Guarantor,Borrower',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|min:6',
            'loan_officer_ids' => 'nullable|array',
            'loan_officer_ids.*' => 'exists:users,id',
            'has_cash_collateral' => 'nullable|boolean',
            'collateral_type_id' => 'nullable|exists:cash_collateral_types,id',

            'filetypes' => 'nullable|array',
            'filetypes.*' => 'exists:filetypes,id',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $data = $request->except(['customerNo', 'loan_officer_ids', 'collateral_type_id']);
        // Format phone numbers
        $data["phone1"] = $this->formatPhoneNumber($data["phone1"]);
        if (!empty($data["phone2"])) {
            $data["phone2"] = $this->formatPhoneNumber($data["phone2"]);
        }
        $data['category'] = $request->category;

        // Set these from logged-in user
        $data['branch_id'] = auth()->user()->branch_id;
        $data['company_id'] = auth()->user()->company_id;
        $data['registrar'] = auth()->id();
        $data['has_cash_collateral'] = $request->has('has_cash_collateral') ? true : false; // Set boolean value

        // Hash password only if provided
        if (!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']); // Don't overwrite with null
        }

        // Photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }

        // Document upload
        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store('documents', 'public');
        }

        DB::beginTransaction();
        try {
            $customer->update($data);

            // Sync group membership
            DB::table('group_members')->where('customer_id', $customer->id)->delete();
            // Save group membership
            if ($request->filled('group_id')) {
                DB::table('group_members')->insert([
                    'group_id' => $request->group_id,
                    'customer_id' => $customer->id,
                    'status' => 'active',
                    'joined_date' => now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('group_members')->insert([
                    'group_id' => 1,
                    'customer_id' => $customer->id,
                    'status' => 'active',
                    'joined_date' => now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Sync loan officers
            if ($request->has('loan_officer_ids')) {
                // Delete previous ones
                DB::table('customer_officer')->where('customer_id', $customer->id)->delete();

                // Insert new ones
                if (!empty($request->loan_officer_ids)) {
                    foreach ($request->loan_officer_ids as $officerId) {
                        DB::table('customer_officer')->insert([
                            'customer_id' => $customer->id,
                            'officer_id' => $officerId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            } else {
                // If none selected, remove all previous
                DB::table('customer_officer')->where('customer_id', $customer->id)->delete();
            }

            // Handle cash collateral
            if ($request->has('has_cash_collateral') && $request->has('collateral_type_id') && $request->collateral_type_id) {
                // Check if collateral already exists
                $existingCollateral = \App\Models\CashCollateral::where('customer_id', $customer->id)->first();

                if ($existingCollateral) {
                    $existingCollateral->update([
                        'type_id' => $request->input('collateral_type_id'),
                    ]);
                } else {
                    \App\Models\CashCollateral::create([
                        'customer_id' => $customer->id,
                        'type_id' => $request->input('collateral_type_id'),
                        'amount' => 0,
                        'branch_id' => auth()->user()->branch_id,
                        'company_id' => auth()->user()->company_id,
                    ]);
                }
            } else {
                // If not checked, remove existing collateral
                \App\Models\CashCollateral::where('customer_id', $customer->id)->delete();
            }

            // Sync File Types and Uploaded Documents
            if ($request->has('filetypes') && $request->hasFile('documents')) {
                $filetypes = $request->filetypes;
                $documents = $request->file('documents');

                // Delete existing filetype entries to prevent duplicates
                DB::table('customer_file_types')->where('customer_id', $customer->id)->delete();

                foreach ($filetypes as $index => $filetypeId) {
                    if (isset($documents[$index])) {
                        $file = $documents[$index];
                        $path = $file->store('documents', 'public');

                        DB::table('customer_file_types')->insert([
                            'customer_id' => $customer->id,
                            'filetype_id' => $filetypeId,
                            'document_path' => $path,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update customer: ' . $e->getMessage());
        }
    }

    // Delete customer
    public function destroy($id)
    {
        $decodedArray = HashidsHelper::decode($id);
        $decoded = $decodedArray[0] ?? null;
        try {
            $customer = Customer::findOrFail($decoded);

            // Check for existing loans, cash collaterals, or GL transactions

            //check if member is in any group then he need to delete that mmeber from that group
            $existingMembership = DB::table('group_members')->where('customer_id', $customer->id)->where('group_id', '!=', 1)->first();
            if ($existingMembership) {
                return redirect()->route('customers.index')->with('error', 'Customer is a member of a group. Please remove them from the group first.');
            }
            $hasLoans = $customer->loans()->exists();
            $hasCollaterals = $customer->collaterals()->exists();
            $hasGLTransactions = \DB::table('gl_transactions')->where('customer_id', $customer->id)->exists();

            if ($hasLoans || $hasCollaterals || $hasGLTransactions) {
                $msg = 'Cannot delete customer: ';
                if ($hasLoans) {
                    $msg .= 'Customer has existing loans. ';
                }
                if ($hasCollaterals) {
                    $msg .= 'Customer has cash collaterals. ';
                }
                if ($hasGLTransactions) {
                    $msg .= 'Customer has transactions.';
                }
                return redirect()->route('customers.index')->with('error', $msg);
            }

            $customer->delete();

            return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete customer: ' . $e->getMessage());
        }
    }

    /**
     * Toggle customer status (block/unblock)
     */
    public function toggleStatus($encodedId)
    {
        $decoded = HashidsHelper::decode($encodedId);
        if (empty($decoded)) {
            return response()->json(['error' => 'Invalid customer ID'], 400);
        }

        $user = auth()->user();
        $branchId = $user->branch_id;

        $customer = Customer::where('id', $decoded[0])
            ->where('branch_id', $branchId)
            ->first();

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        try {
            $newStatus = ($customer->status ?? 'active') === 'active' ? 'inactive' : 'active';
            $customer->status = $newStatus;
            $customer->save();

            $statusText = $newStatus === 'active' ? 'unblocked' : 'blocked';

            return response()->json([
                'success' => true,
                'message' => "Customer {$statusText} successfully.",
                'status' => $newStatus
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update customer status: ' . $e->getMessage()], 500);
        }
    }

    // Show bulk upload form
    public function bulkUpload()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;
        
        // Get share products (share products don't have branch_id/company_id)
        $shareProducts = \App\Models\ShareProduct::where('is_active', true)
            ->orderBy('share_name')
            ->get();
        
        // Get contribution products for the current branch/company
        $contributionProducts = \App\Models\ContributionProduct::where('is_active', true)
            ->where('branch_id', $branchId)
            ->where('company_id', $companyId)
            ->orderBy('product_name')
            ->get();
        
        return view('customers.bulk-upload', compact('shareProducts', 'contributionProducts'));
    }

    // Process bulk upload
    public function bulkUploadStore(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240', // 10MB max, CSV and Excel
            'has_shares' => 'nullable|boolean',
            'share_product_id' => 'nullable|required_if:has_shares,1|exists:share_products,id',
            'has_contributions' => 'nullable|boolean',
            'contribution_product_id' => 'nullable|required_if:has_contributions,1|exists:contribution_products,id',
        ]);

        if ($request->has('has_shares') && !$request->share_product_id) {
            return back()->withErrors(['share_product_id' => 'Please select a share product when applying shares.']);
        }

        if ($request->has('has_contributions') && !$request->contribution_product_id) {
            return back()->withErrors(['contribution_product_id' => 'Please select a contribution product when applying contributions.']);
        }

        try {
            $file = $request->file('csv_file');
            $path = $file->getRealPath();
            $extension = strtolower($file->getClientOriginalExtension());

            // Read file based on extension
            if (in_array($extension, ['xlsx', 'xls'])) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray(null, true, true, false);
                $data = $rows;
            } else {
                $data = array_map('str_getcsv', file($path));
            }

            if (empty($data)) {
                return back()->withErrors(['csv_file' => 'The file is empty.']);
            }

            $header = array_shift($data); // Remove header row
            $header = array_map(function ($h) {
                return strtolower(trim((string) $h));
            }, $header);

            // Validate file structure
            $requiredColumns = ['name', 'phone1', 'dob', 'sex'];
            $missingColumns = array_diff($requiredColumns, $header);

            if (!empty($missingColumns)) {
                return back()->withErrors(['csv_file' => 'Missing required columns: ' . implode(', ', $missingColumns)]);
            }

            // Prepare data for job
            $csvData = [];
            foreach ($data as $row) {
                $rowData = array_combine($header, $row);
                $csvData[] = $rowData;
            }

            // Prepare validated data
            $validated = [
                'has_shares' => $request->has('has_shares'),
                'share_product_id' => $request->share_product_id,
                'has_contributions' => $request->has('has_contributions'),
                'contribution_product_id' => $request->contribution_product_id,
            ];

            // Dispatch job
            \App\Jobs\BulkCustomerImportJob::dispatch(
                $csvData,
                $validated,
                auth()->id(),
                auth()->user()->branch_id,
                auth()->user()->company_id
            );

            // Auto-start queue worker to process the job immediately
            try {
                \Illuminate\Support\Facades\Artisan::call('queue:work', [
                    '--once' => true,
                    '--timeout' => 300,
                    '--tries' => 1,
                ]);
            } catch (\Exception $e) {
                \Log::warning('Failed to auto-start queue worker: ' . $e->getMessage());
            }

            return redirect()->route('customers.index')
                ->with('success', 'Customer import job has been queued and processing has started. The import will be processed in the background.');
        } catch (\Exception $e) {
            return back()->withErrors(['csv_file' => 'Failed to process file: ' . $e->getMessage()]);
        }
    }

    // Download sample Excel template
    public function downloadSample()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CustomerImportTemplateExport(true),
            'customer_import_template_with_sample_data.xlsx'
        );
    }

    /**
     * Send SMS message to customer
     */
    public function sendMessage(Request $request, $customerId)
    {
        try {
            // Decode the customer ID
            $decodedId = HashidsHelper::decode($customerId);
            if (empty($decodedId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid customer ID'
                ], 400);
            }

            $customer = Customer::findOrFail($decodedId[0]);

            // Validate request
            $request->validate([
                'phone_number' => 'required|string',
                'message_content' => 'required|string|max:500',
            ]);

            $phoneNumber = $request->phone_number;
            $message = $request->message_content;

            // Use phone number as provided since it's already in clean format
            // Remove any spaces, dashes, or special characters except +
            $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);

            // Ensure phone number is not empty after cleaning
            if (empty($phoneNumber)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid phone number provided.'
                ], 400);
            }

            // Send SMS using SmsHelper
            $smsResponse = \App\Helpers\SmsHelper::send($phoneNumber, $message);

            // Check if SMS was sent successfully
            $smsSuccess = is_array($smsResponse) ? ($smsResponse['success'] ?? false) : true;
            $smsMessage = is_array($smsResponse) ? ($smsResponse['message'] ?? 'SMS sent') : 'SMS sent';

            // Log the SMS activity (optional)
            \DB::table('sms_logs')->insert([
                'customer_id' => $customer->id,
                'phone_number' => $phoneNumber,
                'message' => $message,
                'response' => is_array($smsResponse) ? json_encode($smsResponse) : $smsResponse,
                'sent_by' => auth()->id(),
                'sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Return response based on SMS result
            if ($smsSuccess) {
                return response()->json([
                    'success' => true,
                    'message' => 'Successfully sent SMS to ' . $customer->name
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send SMS: ' . ($smsResponse['error'] ?? $smsMessage)
                ], 500);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('SMS sending failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload multiple documents for a customer
     */
    public function uploadDocuments(Request $request, $encodedCustomerId)
    {
        try {
            $decoded = HashidsHelper::decode($encodedCustomerId);
            if (empty($decoded)) {
                return response()->json(['success' => false, 'message' => 'Invalid customer id'], 400);
            }

            $customer = Customer::findOrFail($decoded[0]);

            $request->validate([
                'filetypes' => 'required|array',
                'filetypes.*' => 'required|exists:filetypes,id',
                'documents' => 'required|array',
                'documents.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            ]);

            $filetypes = $request->input('filetypes', []);
            $documents = $request->file('documents', []);

            DB::beginTransaction();
            $uploadedCount = 0;
            $uploadedDocuments = [];
            $errors = [];

            foreach ($filetypes as $index => $filetypeId) {
                if (!isset($documents[$index])) {
                    continue;
                }

                try {
                    $file = $documents[$index];
                    $path = $file->store('documents', 'public');

                    // Check if this filetype already exists for this customer
                    $existing = DB::table('customer_file_types')
                        ->where('customer_id', $customer->id)
                        ->where('filetype_id', $filetypeId)
                        ->first();

                    if ($existing) {
                        // If filetype already exists, use "Multiple Documents" filetype instead
                        $filetypeId = 8; // Multiple Documents filetype
                    }

                    // Create new record
                    DB::table('customer_file_types')->insert([
                        'customer_id' => $customer->id,
                        'filetype_id' => $filetypeId,
                        'document_path' => $path,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $uploadedDocuments[] = [
                        'name' => $file->getClientOriginalName(),
                        'type' => \App\Models\Filetype::find($filetypeId)->name ?? 'Unknown',
                        'size' => $this->formatFileSize($file->getSize())
                    ];

                    $uploadedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to upload {$documents[$index]->getClientOriginalName()}: " . $e->getMessage();
                }
            }

            if ($uploadedCount === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No files were uploaded successfully',
                    'errors' => $errors
                ], 400);
            }

            DB::commit();

            $message = "Successfully uploaded {$uploadedCount} document(s)";
            if (!empty($errors)) {
                $message .= ". Some files failed: " . implode(', ', $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'uploaded_count' => $uploadedCount,
                'documents' => $uploadedDocuments,
                'errors' => $errors
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Document upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload documents: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format file size in human readable format
     */
    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Delete a single customer document (pivot row)
     */
    public function deleteDocument(Request $request, $encodedCustomerId, $pivotId)
    {
        try {
            $decoded = HashidsHelper::decode($encodedCustomerId);
            if (empty($decoded)) {
                return response()->json(['success' => false, 'message' => 'Invalid customer id'], 400);
            }

            $customerId = $decoded[0];
            $pivot = DB::table('customer_file_types')->where('id', $pivotId)->where('customer_id', $customerId)->first();
            if (!$pivot) {
                return response()->json(['success' => false, 'message' => 'Document not found'], 404);
            }

            // Delete file from storage if exists
            if (!empty($pivot->document_path)) {
                try {
                    \Storage::disk('public')->delete($pivot->document_path);
                } catch (\Exception $e) {
                    // ignore storage deletion errors
                }
            }

            DB::table('customer_file_types')->where('id', $pivotId)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stream a customer document for viewing in the browser
     */
    public function viewDocument($encodedCustomerId, $pivotId)
    {
        $decoded = HashidsHelper::decode($encodedCustomerId);
        if (empty($decoded)) {
            abort(404);
        }

        $customerId = $decoded[0];
        $pivot = \DB::table('customer_file_types')
            ->where('id', $pivotId)
            ->where('customer_id', $customerId)
            ->first();

        if (!$pivot || empty($pivot->document_path)) {
            abort(404);
        }

        $disk = \Storage::disk('public');
        if (!$disk->exists($pivot->document_path)) {
            abort(404);
        }

        $mimeType = $disk->mimeType($pivot->document_path) ?: 'application/octet-stream';
        $contents = $disk->get($pivot->document_path);
        return response($contents, 200)->header('Content-Type', $mimeType);
    }

    /**
     * Download a customer document as attachment
     */
    public function downloadDocument($encodedCustomerId, $pivotId)
    {
        $decoded = HashidsHelper::decode($encodedCustomerId);
        if (empty($decoded)) {
            abort(404);
        }

        $customerId = $decoded[0];
        $pivot = \DB::table('customer_file_types')
            ->where('id', $pivotId)
            ->where('customer_id', $customerId)
            ->first();

        if (!$pivot || empty($pivot->document_path)) {
            abort(404);
        }

        $disk = \Storage::disk('public');
        if (!$disk->exists($pivot->document_path)) {
            abort(404);
        }

        $filename = basename($pivot->document_path);
        return response()->streamDownload(function () use ($disk, $pivot) {
            echo $disk->get($pivot->document_path);
        }, $filename);
    }

    /**
     * Generate a unique share account number
     */
    private function generateShareAccountNumber()
    {
        do {
            $accountNumber = 'SA' . strtoupper(\Illuminate\Support\Str::random(8));
        } while (\App\Models\ShareAccount::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }

    /**
     * Generate a unique 16-character contribution account number
     */
    private function generateContributionAccountNumber()
    {
        do {
            $accountNumber = 'CA' . strtoupper(\Illuminate\Support\Str::random(14));
        } while (\App\Models\ContributionAccount::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }

    /**
     * Store a next of kin for a customer
     */
    public function storeNextOfKin(Request $request, $encodedCustomerId)
    {
        $decoded = HashidsHelper::decode($encodedCustomerId);
        $customerId = $decoded[0] ?? null;
        
        if (!$customerId) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'id_type' => 'nullable|string|max:255',
            'id_number' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:M,F',
            'notes' => 'nullable|string',
        ]);

        $nextOfKin = \App\Models\NextOfKin::create([
            'customer_id' => $customerId,
            'name' => $request->name,
            'relationship' => $request->relationship,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'id_type' => $request->id_type,
            'id_number' => $request->id_number,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Next of kin added successfully',
            'nextOfKin' => $nextOfKin
        ]);
    }

    /**
     * Update a next of kin
     */
    public function updateNextOfKin(Request $request, $encodedCustomerId, $encodedNextOfKinId)
    {
        $decodedCustomer = HashidsHelper::decode($encodedCustomerId);
        $decodedNextOfKin = HashidsHelper::decode($encodedNextOfKinId);
        $customerId = $decodedCustomer[0] ?? null;
        $nextOfKinId = $decodedNextOfKin[0] ?? null;
        
        if (!$customerId || !$nextOfKinId) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $nextOfKin = \App\Models\NextOfKin::where('id', $nextOfKinId)
            ->where('customer_id', $customerId)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'id_type' => 'nullable|string|max:255',
            'id_number' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:M,F',
            'notes' => 'nullable|string',
        ]);

        $nextOfKin->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Next of kin updated successfully',
            'nextOfKin' => $nextOfKin
        ]);
    }

    /**
     * Delete a next of kin
     */
    public function deleteNextOfKin($encodedCustomerId, $encodedNextOfKinId)
    {
        $decodedCustomer = HashidsHelper::decode($encodedCustomerId);
        $decodedNextOfKin = HashidsHelper::decode($encodedNextOfKinId);
        $customerId = $decodedCustomer[0] ?? null;
        $nextOfKinId = $decodedNextOfKin[0] ?? null;
        
        if (!$customerId || !$nextOfKinId) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $nextOfKin = \App\Models\NextOfKin::where('id', $nextOfKinId)
            ->where('customer_id', $customerId)
            ->firstOrFail();

        $nextOfKin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Next of kin deleted successfully'
        ]);
    }
}
