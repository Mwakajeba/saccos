<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Branch;
use App\Models\GlTransaction;
use App\Models\Hr\Employee;
use App\Models\Hr\PayrollChartAccount;
use App\Models\Hr\SalaryAdvance;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Traits\TransactionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class SalaryAdvanceController extends Controller
{
    use TransactionHelper;

    /**
     * Display a listing of salary advances
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($request->ajax()) {
            $salaryAdvances = SalaryAdvance::with(['employee', 'bankAccount', 'user'])
                ->where('company_id', $user->company_id)
                ->orderBy('created_at', 'desc');

            return DataTables::of($salaryAdvances)
                ->addIndexColumn()
                ->addColumn('reference_display', function ($advance) {
                    return '<strong class="text-primary">' . e($advance->reference) . '</strong>';
                })
                ->addColumn('date_display', function ($advance) {
                    return $advance->date ? $advance->date->format('M d, Y') : 'N/A';
                })
                ->addColumn('employee_display', function ($advance) {
                    if ($advance->employee) {
                        $html = '<div><strong>' . e($advance->employee->full_name) . '</strong>';
                        if ($advance->employee->employee_number) {
                            $html .= '<br><small class="text-muted">' . e($advance->employee->employee_number) . '</small>';
                        }
                        $html .= '</div>';
                        return $html;
                    }
                    return 'N/A';
                })
                ->addColumn('bank_account_display', function ($advance) {
                    return $advance->bankAccount ? e($advance->bankAccount->name) : 'N/A';
                })
                ->addColumn('amount_display', function ($advance) {
                    return '<strong class="text-success">TZS ' . number_format($advance->amount, 2) . '</strong>';
                })
                ->addColumn('monthly_deduction_display', function ($advance) {
                    return '<strong>TZS ' . number_format($advance->monthly_deduction, 2) . '</strong>';
                })
                ->addColumn('status_badge', function ($advance) {
                    if ($advance->is_active) {
                        return '<span class="badge bg-success">Active</span>';
                    }
                    return '<span class="badge bg-secondary">Inactive</span>';
                })
                ->addColumn('action', function ($advance) {
                    $viewBtn = '<a href="' . route('hr.salary-advances.show', $advance) . '" class="btn btn-sm btn-outline-info me-1" title="View Details"><i class="bx bx-show"></i></a>';
                    
                    if ($advance->is_active) {
                        $editBtn = '<a href="' . route('hr.salary-advances.edit', $advance) . '" class="btn btn-sm btn-outline-primary me-1" title="Edit"><i class="bx bx-edit"></i></a>';
                        $deleteBtn = '<button type="button" onclick="deleteAdvance(' . $advance->id . ', \'' . e($advance->reference) . '\')" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bx bx-trash"></i></button>';
                        return $viewBtn . $editBtn . $deleteBtn;
                    }
                    
                    return $viewBtn;
                })
                ->rawColumns(['reference_display', 'employee_display', 'amount_display', 'monthly_deduction_display', 'status_badge', 'action'])
                ->make(true);
        }

        // Get statistics for the view
        $totalAdvances = SalaryAdvance::where('company_id', $user->company_id)->count();
        $activeAdvances = SalaryAdvance::where('company_id', $user->company_id)->where('is_active', true)->count();
        $inactiveAdvances = SalaryAdvance::where('company_id', $user->company_id)->where('is_active', false)->count();
        $totalAmount = SalaryAdvance::where('company_id', $user->company_id)->sum('amount');

        $statistics = [
            'total' => $totalAdvances,
            'active' => $activeAdvances,
            'inactive' => $inactiveAdvances,
            'total_amount' => $totalAmount,
        ];

        return view('hr-payroll.salary-advances.index', compact('statistics'));
    }

    /**
     * Show the form for creating a new salary advance
     */
    public function create()
    {
        $user = Auth::user();

        // Get employees for the current company
        $employees = Employee::where('company_id', $user->company_id)
            ->orderBy('first_name')
            ->get();

        // Get bank accounts for the current company
        $bankAccounts = BankAccount::with('chartAccount')
            ->whereHas('chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->orderBy('name')
            ->get();

        // Get branches for the current company
        $branches = Branch::where('company_id', $user->company_id)
            ->orderBy('name')
            ->get();

        return view('hr-payroll.salary-advances.create', compact('employees', 'bankAccounts', 'branches'));
    }

    /**
     * Store a newly created salary advance
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:hr_employees,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'monthly_deduction' => 'required|numeric|min:0.01',
            'repayment_type' => 'required|in:payroll,manual,both',
            'reason' => 'required|string|max:1000',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        try {
            return $this->runTransaction(function () use ($request) {
                $user = Auth::user();
                $branchId = $request->branch_id ?? session('branch_id') ?? $user->branch_id;

                // Generate unique reference
                $reference = 'SA-' . strtoupper(uniqid());

                // Get payroll chart account for the salary advance receivable
                $chartAccounts = PayrollChartAccount::where('company_id', $user->company_id)->first();
                
                if (!$chartAccounts || !$chartAccounts->salary_advance_receivable_account_id) {
                    throw new \Exception('Salary advance receivable account not configured in payroll chart accounts.');
                }

                // Get bank account
                $bankAccount = BankAccount::find($request->bank_account_id);
                if (!$bankAccount) {
                    throw new \Exception('Bank account not found.');
                }

                // Create salary advance
                $salaryAdvance = SalaryAdvance::create([
                    'company_id' => $user->company_id,
                    'employee_id' => $request->employee_id,
                    'bank_account_id' => $request->bank_account_id,
                    'user_id' => $user->id,
                    'branch_id' => $branchId,
                    'reference' => $reference,
                    'date' => $request->date,
                    'amount' => $request->amount,
                    'monthly_deduction' => $request->monthly_deduction,
                    'repayment_type' => $request->repayment_type,
                    'reason' => $request->reason,
                    'is_active' => true,
                ]);

                $employee = Employee::find($request->employee_id);

                // Create payment record
                $payment = Payment::create([
                    'reference' => $reference,
                    'reference_type' => 'salary_advance',
                    'reference_number' => $salaryAdvance->id,
                    'amount' => $request->amount,
                    'wht_treatment' => 'NONE',
                    'wht_rate' => 0,
                    'wht_amount' => 0,
                    'net_payable' => $request->amount,
                    'total_cost' => $request->amount,
                    'vat_mode' => 'NONE',
                    'vat_amount' => 0,
                    'base_amount' => $request->amount,
                    'date' => $request->date,
                    'description' => "Salary advance for {$employee->full_name} - {$request->reason}",
                    'bank_account_id' => $request->bank_account_id,
                    'payee_type' => 'other',
                    'payee_name' => $employee->full_name,
                    'branch_id' => $branchId,
                    'user_id' => $user->id,
                    'approved' => true,
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);

                // Create payment item for salary advance receivable (Debit)
                PaymentItem::create([
                    'payment_id' => $payment->id,
                    'chart_account_id' => $chartAccounts->salary_advance_receivable_account_id,
                    'amount' => $request->amount,
                    'wht_treatment' => 'NONE',
                    'wht_rate' => 0,
                    'wht_amount' => 0,
                    'base_amount' => $request->amount,
                    'net_payable' => $request->amount,
                    'total_cost' => $request->amount,
                    'vat_mode' => 'NONE',
                    'vat_amount' => 0,
                    'description' => "Salary advance receivable - {$employee->full_name}",
                ]);

                // Create GL transactions via Payment model
                // This will automatically create:
                // - DR Salary Advance Receivable
                // - CR Bank Account
                $payment->createGlTransactions();

                // Store payment_id in salary_advance for reference
                $salaryAdvance->update(['payment_id' => $payment->id]);

                return redirect()->route('hr.salary-advances.show', $salaryAdvance)
                    ->with('success', 'Salary advance created successfully.');
            });
        } catch (\Exception $e) {
            Log::error('Failed to create salary advance: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create salary advance: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified salary advance
     */
    public function show(SalaryAdvance $salaryAdvance)
    {
        $salaryAdvance->load(['employee', 'bankAccount', 'user', 'branch', 'repayments.user', 'repayments.payroll']);

        $repayments = $salaryAdvance->repayments()->orderByDesc('date')->get();

        return view('hr-payroll.salary-advances.show', compact('salaryAdvance', 'repayments'));
    }

    /**
     * Show the form for editing the specified salary advance
     */
    public function edit(SalaryAdvance $salaryAdvance)
    {
        $user = Auth::user();

        // Check if user can edit this salary advance
        if ($salaryAdvance->company_id !== $user->company_id) {
            abort(403, 'Unauthorized access to salary advance.');
        }

        // Check if salary advance can be edited (only active advances)
        if (!$salaryAdvance->is_active) {
            return redirect()->route('hr.salary-advances.show', $salaryAdvance)
                ->with('error', 'Only active salary advances can be edited.');
        }

        // Get employees for the current company
        $employees = Employee::where('company_id', $user->company_id)
            ->orderBy('first_name')
            ->get();

        // Get bank accounts for the current company
        $bankAccounts = BankAccount::with('chartAccount')
            ->whereHas('chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->orderBy('name')
            ->get();

        // Get branches for the current company
        $branches = Branch::where('company_id', $user->company_id)
            ->orderBy('name')
            ->get();

        return view('hr-payroll.salary-advances.edit', compact('salaryAdvance', 'employees', 'bankAccounts', 'branches'));
    }

    /**
     * Update the specified salary advance
     */
    public function update(Request $request, SalaryAdvance $salaryAdvance)
    {
        $request->validate([
            'employee_id' => 'required|exists:hr_employees,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'monthly_deduction' => 'required|numeric|min:0.01',
            'repayment_type' => 'required|in:payroll,manual,both',
            'reason' => 'required|string|max:1000',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $user = Auth::user();

        // Check if user can update this salary advance
        if ($salaryAdvance->company_id !== $user->company_id) {
            Log::error('Unauthorized access to salary advance: ' . $salaryAdvance->id);
            abort(403, 'Unauthorized access to salary advance.');
        }

        // Check if salary advance can be updated (only active advances)
        if (!$salaryAdvance->is_active) {
            Log::error('Only active salary advances can be updated: ' . $salaryAdvance->id);
            return redirect()->route('hr.salary-advances.show', $salaryAdvance)
                ->with('error', 'Only active salary advances can be updated.');
        }

        try {
            return $this->runTransaction(function () use ($request, $salaryAdvance, $user) {
                $branchId = $request->branch_id ?? session('branch_id') ?? $user->branch_id;

                // Get payroll chart account for the salary advance receivable
                $chartAccounts = PayrollChartAccount::where('company_id', $user->company_id)->first();
                
                if (!$chartAccounts || !$chartAccounts->salary_advance_receivable_account_id) {
                    throw new \Exception('Salary advance receivable account not configured in payroll chart accounts.');
                }

                // Get bank account
                $bankAccount = BankAccount::find($request->bank_account_id);
                if (!$bankAccount) {
                    throw new \Exception('Bank account not found.');
                }

                // Get the related payment and delete old transactions
                $payment = Payment::where('reference', $salaryAdvance->reference)
                    ->where('reference_type', 'salary_advance')
                    ->first();

                if ($payment) {
                    // Delete existing GL transactions
                    GlTransaction::where('transaction_id', $payment->id)
                        ->where('transaction_type', 'payment')
                        ->delete();

                    // Delete existing payment items
                    PaymentItem::where('payment_id', $payment->id)->delete();

                    // Delete the payment
                    $payment->delete();
                }

                $employee = Employee::find($request->employee_id);

                // Update salary advance
                $salaryAdvance->update([
                    'employee_id' => $request->employee_id,
                    'bank_account_id' => $request->bank_account_id,
                    'date' => $request->date,
                    'amount' => $request->amount,
                    'monthly_deduction' => $request->monthly_deduction,
                    'repayment_type' => $request->repayment_type,
                    'reason' => $request->reason,
                    'branch_id' => $branchId,
                ]);

                // Create new payment
                $newPayment = Payment::create([
                    'reference' => $salaryAdvance->reference,
                    'reference_type' => 'salary_advance',
                    'reference_number' => $salaryAdvance->id,
                    'amount' => $request->amount,
                    'wht_treatment' => 'NONE',
                    'wht_rate' => 0,
                    'wht_amount' => 0,
                    'net_payable' => $request->amount,
                    'total_cost' => $request->amount,
                    'vat_mode' => 'NONE',
                    'vat_amount' => 0,
                    'base_amount' => $request->amount,
                    'date' => $request->date,
                    'description' => "Salary advance for {$employee->full_name} - {$request->reason}",
                    'bank_account_id' => $request->bank_account_id,
                    'payee_type' => 'other',
                    'payee_name' => $employee->full_name,
                    'branch_id' => $branchId,
                    'user_id' => $user->id,
                    'approved' => true,
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);

                // Create new payment item
                PaymentItem::create([
                    'payment_id' => $newPayment->id,
                    'chart_account_id' => $chartAccounts->salary_advance_receivable_account_id,
                    'amount' => $request->amount,
                    'wht_treatment' => 'NONE',
                    'wht_rate' => 0,
                    'wht_amount' => 0,
                    'base_amount' => $request->amount,
                    'net_payable' => $request->amount,
                    'total_cost' => $request->amount,
                    'vat_mode' => 'NONE',
                    'vat_amount' => 0,
                    'description' => "Salary advance receivable - {$employee->full_name}",
                ]);

                // Create GL transactions via Payment model
                // This will automatically create:
                // - DR Salary Advance Receivable
                // - CR Bank Account
                $newPayment->createGlTransactions();

                // Update payment_id reference
                $salaryAdvance->update(['payment_id' => $newPayment->id]);

                return redirect()->route('hr.salary-advances.show', $salaryAdvance)
                    ->with('success', 'Salary advance updated successfully.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update salary advance: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified salary advance
     */
    public function destroy(SalaryAdvance $salaryAdvance)
    {
        $user = Auth::user();

        // Check if user can delete this salary advance
        if ($salaryAdvance->company_id !== $user->company_id) {
            abort(403, 'Unauthorized access to salary advance.');
        }

        // Check if salary advance can be deleted (only active advances)
        if (!$salaryAdvance->is_active) {
            return redirect()->route('hr.salary-advances.show', $salaryAdvance)
                ->with('error', 'Only active salary advances can be deleted.');
        }

        try {
            return $this->runTransaction(function () use ($salaryAdvance) {
                // Get the related payment and transactions
                $payment = Payment::where('reference', $salaryAdvance->reference)
                    ->where('reference_type', 'salary_advance')
                    ->first();

                if ($payment) {
                    // Delete existing GL transactions
                    GlTransaction::where('transaction_id', $payment->id)
                        ->where('transaction_type', 'payment')
                        ->delete();

                    // Delete existing payment items
                    PaymentItem::where('payment_id', $payment->id)->delete();

                    // Delete the payment
                    $payment->delete();
                }

                // Delete the salary advance
                $salaryAdvance->delete();

                return redirect()->route('hr.salary-advances.index')
                    ->with('success', 'Salary advance deleted successfully.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete salary advance: ' . $e->getMessage()]);
        }
    }

    /**
     * Record a manual repayment for a salary advance
     */
    public function recordManualRepayment(Request $request, SalaryAdvance $salaryAdvance)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $salaryAdvance->remaining_balance,
            'date' => 'required|date',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            return $this->runTransaction(function () use ($request, $salaryAdvance) {
                // Record Repayment using model method
                $repayment = $salaryAdvance->recordRepayment(
                    $request->amount,
                    $request->date,
                    null,
                    'manual',
                    $request->bank_account_id,
                    $request->notes
                );

                if (!$repayment) {
                    throw new \Exception('Failed to record repayment.');
                }

                // Record Receipt for accounting
                $receiptReference = 'RCP-' . strtoupper(Str::random(8));
                $receipt = \App\Models\Receipt::create([
                    'company_id' => $salaryAdvance->company_id,
                    'branch_id' => $salaryAdvance->branch_id,
                    'user_id' => Auth::id(),
                    'bank_account_id' => $request->bank_account_id,
                    'date' => $request->date,
                    'amount' => $request->amount,
                    'reference' => $receiptReference,
                    'reference_type' => 'salary_advance_repayment',
                    'reference_number' => $repayment->reference,
                    'description' => "Manual repayment for salary advance {$salaryAdvance->reference} - {$salaryAdvance->employee->full_name}",
                    'payee_type' => 'employee',
                    'payee_id' => $salaryAdvance->employee_id,
                    'payee_name' => $salaryAdvance->employee->full_name,
                    'employee_id' => $salaryAdvance->employee_id,
                    'status' => 'approved',
                    'approved' => true,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);

                // Create Receipt Item for accounting (Credit Salary Advance Receivable)
                \App\Models\ReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'chart_account_id' => $chartAccounts->salary_advance_receivable_account_id,
                    'amount' => $request->amount,
                    'base_amount' => $request->amount,
                    'vat_amount' => 0,
                    'description' => "Salary advance repayment - {$salaryAdvance->employee->full_name}",
                ]);

                // Create GL Transactions (handled by Receipt model)
                if (method_exists($receipt, 'createGlTransactions')) {
                    $receipt->createGlTransactions();
                }

                return redirect()->route('hr.salary-advances.show', $salaryAdvance)
                    ->with('success', 'Manual repayment recorded successfully.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to record repayment: ' . $e->getMessage()])
                ->withInput();
        }
    }

}
