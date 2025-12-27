<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AccountClassGroup;
use App\Models\ChartAccount;
use App\Models\Supplier;
use App\Models\Journal;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\Budget;

class AccountingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        $branchId = $user->branch_id;

        // Get counts for each accounting entity
        $stats = [
            'account_class_groups' => $this->getCount('account_class_groups', null, $companyId),
            'chart_accounts' => $this->getChartAccountsCount($companyId),
            'suppliers' => $this->getCount('suppliers', null, $companyId),
            'journals' => $this->getJournalsCount($branchId, $companyId),
            'payment_vouchers' => $this->getPaymentVouchersCount($branchId, $companyId),
            'receipt_vouchers' => $this->getReceiptVouchersCount($branchId, $companyId),
            'bank_accounts' => $this->getBankAccountsCount(),
            'bank_reconciliations' => $this->getBankReconciliationsCount($branchId, $companyId),
            'bill_purchases' => $this->getBillPurchasesCount($branchId, $companyId),
            'budgets' => $this->getBudgetsCount($branchId, $companyId),
        ];

        return view('accounting.index', compact('stats'));
    }

    /**
     * Get count from a table, handling cases where table might not exist
     */
    private function getCount($tableName, $branchId = null, $companyId = null)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable($tableName)) {
                return 0;
            }

            $query = DB::table($tableName);
            
            // Add branch filter if branch_id column exists
            if ($branchId && DB::getSchemaBuilder()->hasColumn($tableName, 'branch_id')) {
                $query->where('branch_id', $branchId);
            }
            
            // Add company filter if company_id column exists
            if ($companyId && DB::getSchemaBuilder()->hasColumn($tableName, 'company_id')) {
                $query->where('company_id', $companyId);
            }
            
            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get chart accounts count (requires join with account_class_groups)
     */
    private function getChartAccountsCount($companyId)
    {
        try {
            return ChartAccount::whereHas('accountClassGroup', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get journals count
     */
    private function getJournalsCount($branchId, $companyId)
    {
        try {
            $query = Journal::query();
            
            if ($branchId && DB::getSchemaBuilder()->hasColumn('journals', 'branch_id')) {
                $query->where('branch_id', $branchId);
            }
            
            if ($companyId && DB::getSchemaBuilder()->hasColumn('journals', 'company_id')) {
                $query->where('company_id', $companyId);
            }
            
            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get payment vouchers count
     */
    private function getPaymentVouchersCount($branchId, $companyId)
    {
        try {
            $query = Payment::query();
            
            if ($branchId && DB::getSchemaBuilder()->hasColumn('payments', 'branch_id')) {
                $query->where('branch_id', $branchId);
            }
            
            if ($companyId && DB::getSchemaBuilder()->hasColumn('payments', 'company_id')) {
                $query->where('company_id', $companyId);
            }
            
            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get receipt vouchers count
     */
    private function getReceiptVouchersCount($branchId, $companyId)
    {
        try {
            $query = Receipt::query();
            
            if ($branchId && DB::getSchemaBuilder()->hasColumn('receipts', 'branch_id')) {
                $query->where('branch_id', $branchId);
            }
            
            if ($companyId && DB::getSchemaBuilder()->hasColumn('receipts', 'company_id')) {
                $query->where('company_id', $companyId);
            }
            
            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get bank accounts count
     */
    private function getBankAccountsCount()
    {
        try {
            return BankAccount::count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get bank reconciliations count
     */
    private function getBankReconciliationsCount($branchId, $companyId)
    {
        try {
            $query = BankReconciliation::query();
            
            if ($branchId && DB::getSchemaBuilder()->hasColumn('bank_reconciliations', 'branch_id')) {
                $query->where('branch_id', $branchId);
            }
            
            if ($companyId && DB::getSchemaBuilder()->hasColumn('bank_reconciliations', 'company_id')) {
                $query->where('company_id', $companyId);
            }
            
            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get bill purchases count
     */
    private function getBillPurchasesCount($branchId, $companyId)
    {
        return $this->getCount('bill_purchases', $branchId, $companyId);
    }

    /**
     * Get budgets count
     */
    private function getBudgetsCount($branchId, $companyId)
    {
        try {
            $query = Budget::query();
            
            if ($branchId && DB::getSchemaBuilder()->hasColumn('budgets', 'branch_id')) {
                $query->where('branch_id', $branchId);
            }
            
            if ($companyId && DB::getSchemaBuilder()->hasColumn('budgets', 'company_id')) {
                $query->where('company_id', $companyId);
            }
            
            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}

