<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ChartAccount;
use App\Models\AccountClassGroup;
use App\Models\GlTransaction;
use App\Models\BankReconciliation;
use App\Models\Journal;
use App\Models\Payment;
use App\Models\Penalty;
use App\Models\Receipt;
use App\Models\ContributionProduct;
use App\Models\ContributionAccount;
use App\Models\ShareProduct;
use App\Models\ShareAccount;
use App\Services\LoanPenaltyService;

class DashboardController extends Controller
{
    /**
     * Endpoint for monthly collections (expected, collected, arrears) for current year
     */
    public function monthlyCollections()
    {
        $year = now()->year;
        $months = [];
        $expected = [];
        $collected = [];
        $arrears = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthLabel = date('M', mktime(0, 0, 0, $m, 1));
            $months[] = $monthLabel;
            
            // Expected: sum of all schedules due in this month
            // Use accrued_interest instead of interest for accurate calculation
            $principal = \App\Models\LoanSchedule::whereYear('due_date', $year)
                ->whereMonth('due_date', $m)
                ->sum('principal');
            
            // Get schedules for this month to calculate interest (accrued_interest or interest)
            $schedules = \App\Models\LoanSchedule::whereYear('due_date', $year)
                ->whereMonth('due_date', $m)
                ->get();
            
            // Calculate expected interest using accrued_interest if available, otherwise use interest
            $expectedInterest = $schedules->sum(function ($schedule) {
                return $schedule->accrued_interest ?? $schedule->interest ?? 0;
            });
            
            $exp = $principal + $expectedInterest;
            $expected[] = $exp;
            
            // Collected: sum of repayments made for schedules due in this month
            $repayments = \DB::table('repayments')
                ->join('loan_schedules', 'repayments.loan_schedule_id', '=', 'loan_schedules.id')
                ->whereYear('loan_schedules.due_date', $year)
                ->whereMonth('loan_schedules.due_date', $m)
                ->sum(\DB::raw('repayments.principal + repayments.interest'));
            $collected[] = $repayments;
            
            // Arrears: Calculate actual arrears for overdue schedules in this month
            // Only count schedules that are overdue (due_date < today) and have remaining amount
            $today = now();
            $arrearsAmount = 0;
            foreach ($schedules as $schedule) {
                $dueDate = \Carbon\Carbon::parse($schedule->due_date);
                // Only count if schedule is overdue
                if ($dueDate->lt($today)) {
                    // Calculate remaining amount using accrued_interest
                    $scheduleInterest = $schedule->accrued_interest ?? $schedule->interest ?? 0;
                    $totalDue = $schedule->principal + $scheduleInterest + ($schedule->fee_amount ?? 0) + ($schedule->penalty_amount ?? 0);
                    
                    // Get paid amount for this schedule
                    $paidAmount = \DB::table('repayments')
                        ->where('loan_schedule_id', $schedule->id)
                        ->sum(\DB::raw('principal + interest + fee_amount + penalt_amount'));
                    
                    $remaining = max(0, $totalDue - $paidAmount);
                    $arrearsAmount += $remaining;
                }
            }
            $arrears[] = $arrearsAmount;
        }
        return response()->json([
            'months' => $months,
            'expected' => $expected,
            'collected' => $collected,
            'arrears' => $arrears
        ]);
    }
    /**
     * Endpoint for delinquency loan buckets (current year)
     */
    public function delinquencyLoanBuckets(Request $request)
    {
        $year = now()->year;
        $company = auth()->user()->company;
        $user = auth()->user();
        
        // Get branch filter from request
        $selectedBranchId = $request->get('branch_id');
        
        // Get user's assigned branches
        $userBranchIds = $user->branches()->where('company_id', $company->id)->pluck('branches.id')->toArray();
        
        // If no assigned branches, use all company branches
        if (empty($userBranchIds)) {
            $userBranchIds = \App\Models\Branch::where('company_id', $company->id)->pluck('id')->toArray();
        }
        
        // Define buckets (days overdue)
        $buckets = [
            '1-30 days' => [1, 30],
            '31-60 days' => [31, 60],
            '61-90 days' => [61, 90],
            '91-180 days' => [91, 180],
            '181-360 days' => [181, 360],
            '361+ days' => [361, 10000],
        ];
        $labels = [];
        $values = [];
        foreach ($buckets as $label => [$min, $max]) {
            $query = \App\Models\Loan::whereYear('disbursed_on', $year)
                ->whereHas('branch', function($q) use ($company) {
                    $q->where('company_id', $company->id);
                })
                ->where('status', 'active');
            
            // Apply branch filter
            if ($selectedBranchId) {
                $query->where('branch_id', $selectedBranchId);
            } else {
                // If no specific branch selected, filter by user's assigned branches
                if (!empty($userBranchIds)) {
                    $query->whereIn('branch_id', $userBranchIds);
                }
            }
            
            $count = $query->whereHas('schedule', function($q) use ($min, $max) {
                    $q->whereRaw('DATEDIFF(CURDATE(), due_date) BETWEEN ? AND ?', [$min, $max]);
                })
                ->count();
            $labels[] = $label;
            $values[] = $count;
        }
        return response()->json([
            'labels' => $labels,
            'values' => $values
        ]);
    }
    /**
     * Endpoint for loan product disbursement data (current year)
     */
    public function loanProductDisbursement(Request $request)
    {
        $year = now()->year;
        $company = auth()->user()->company;
        $user = auth()->user();
        
        // Get branch filter from request
        $selectedBranchId = $request->get('branch_id');
        
        // Get user's assigned branches
        $userBranchIds = $user->branches()->where('company_id', $company->id)->pluck('branches.id')->toArray();
        
        // If no assigned branches, use all company branches
        if (empty($userBranchIds)) {
            $userBranchIds = \App\Models\Branch::where('company_id', $company->id)->pluck('id')->toArray();
        }
        
        $products = \App\Models\LoanProduct::all();

        $productNames = [];
        $amounts = [];
        foreach ($products as $product) {
            $query = \App\Models\Loan::where('product_id', $product->id)
                ->whereYear('disbursed_on', $year)
                ->whereHas('branch', function($q) use ($company) {
                    $q->where('company_id', $company->id);
                });
            
            // Apply branch filter
            if ($selectedBranchId) {
                $query->where('branch_id', $selectedBranchId);
            } else {
                // If no specific branch selected, filter by user's assigned branches
                if (!empty($userBranchIds)) {
                    $query->whereIn('branch_id', $userBranchIds);
                }
            }
            
            $total = $query->sum('amount');
            $productNames[] = $product->name;
            $amounts[] = $total;
        }
        return response()->json([
            'products' => $productNames,
            'amounts' => $amounts
        ]);
    }
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            // Redirect to login or show an error
            return redirect()->route('login')->with('error', 'Please login to access the dashboard.');
        }
        $company = $user->company;
        
        // Get available branches for the filter - only user's assigned branches
        $branches = $user->branches()->where('company_id', $company->id)->get();
        
        // Get user's assigned branch IDs for filtering (fallback to user's branch_id if no branches assigned)
        $userBranchIds = $branches->pluck('id')->toArray();
        if (empty($userBranchIds) && $user->branch_id) {
            $userBranchIds = [$user->branch_id];
            $branches = $company->branches()->whereIn('id', $userBranchIds)->get();
        }
        
        // Branch filter: when user has multiple branches, default to "All Branches" to show all assigned branches' data
        $defaultBranchId = (count($userBranchIds) > 1) ? '' : ($user->branch_id ?? '');
        $selectedBranchId = $request->get('branch_id', $defaultBranchId);
        
        // Ensure user's primary branch is always included in permitted branches
        if ($user->branch_id && !in_array($user->branch_id, $userBranchIds)) {
            $userBranchIds[] = $user->branch_id;
        }

        // Get balance sheet data
        $balanceSheetData = $this->getBalanceSheetData($selectedBranchId, $userBranchIds);
        
        // Get comprehensive financial report data
        $financialReportData = $this->getFinancialReportData($selectedBranchId, $userBranchIds);
        
        // Get current month
        $currentMonth = now()->format('Y-m');

        // Get recent activities - filter by company through branch (last 90 days for journals)
        $recentJournals = Journal::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->when($selectedBranchId, function($query) use ($selectedBranchId) {
            return $query->where('branch_id', $selectedBranchId);
        }, function($query) use ($userBranchIds) {
            return $query->whereIn('branch_id', $userBranchIds);
        })
        ->where('date', '>=', now()->subDays(90))
        ->with(['user', 'branch'])
        ->latest('date')
        ->take(5)
        ->get();
        
        $recentPayments = Payment::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->when($selectedBranchId, function($query) use ($selectedBranchId) {
            return $query->where('branch_id', $selectedBranchId);
        }, function($query) use ($userBranchIds) {
            return $query->whereIn('branch_id', $userBranchIds);
        })
        ->whereYear('date', now()->year)
        ->with(['user', 'branch'])
        ->latest()
        ->get();
        
        // Receipts this year for total sum
        $receiptsThisYear = Receipt::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->when($selectedBranchId, function($query) use ($selectedBranchId) {
            return $query->where('branch_id', $selectedBranchId);
        }, function($query) use ($userBranchIds) {
            return $query->whereIn('branch_id', $userBranchIds);
        })
        ->whereYear('date', now()->year)
        ->get();
        
        // Recent receipts (last 5) for display
        $recentReceipts = Receipt::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->when($selectedBranchId, function($query) use ($selectedBranchId) {
            return $query->where('branch_id', $selectedBranchId);
        }, function($query) use ($userBranchIds) {
            return $query->whereIn('branch_id', $userBranchIds);
        })
        ->with(['user', 'branch', 'customer'])
        ->latest('date')
        ->take(5)
        ->get();
        
        $loans_status_stats = ['active', 'written_off', 'defaulted', 'completed','complete_topup'];
        // Loan statistics for Total Loan Amount (only active and completed)
        $loansForTotalAmount = \App\Models\Loan::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->when($selectedBranchId, function($query) use ($selectedBranchId) {
            return $query->where('branch_id', $selectedBranchId);
        }, function($query) use ($userBranchIds) {
            return $query->whereIn('branch_id', $userBranchIds);
        })->whereIn('status', ['active', 'completed'])->get();
        
        // All loans for other calculations
        $loans = \App\Models\Loan::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->when($selectedBranchId, function($query) use ($selectedBranchId) {
            return $query->where('branch_id', $selectedBranchId);
        }, function($query) use ($userBranchIds) {
            return $query->whereIn('branch_id', $userBranchIds);
        })->whereIn('status', $loans_status_stats)->get();
        
        // Loans for detailed interest calculations (same statuses as report)
        $loansForInterest = \App\Models\Loan::with(['customer', 'branch', 'loanOfficer', 'schedule.repayments'])
            ->whereHas('branch', function($query) use ($company) {
                $query->where('company_id', $company->id);
            })->when($selectedBranchId, function($query) use ($selectedBranchId) {
                return $query->where('branch_id', $selectedBranchId);
            }, function($query) use ($userBranchIds) {
                return $query->whereIn('branch_id', $userBranchIds);
            })->whereIn('status', ['active', 'written_off', 'defaulted'])->get();

        $totalLoanAmount = $loansForTotalAmount->sum('amount_total');
        $totalPrincipal = $loans->sum('amount');
        $totalInterest = $loans->sum('interest_amount');

        // Repaid principal and interest
        $repaidPrincipal = 0;
        $repaidInterest = 0;
        $outstandingPrincipal = 0;
        $outstandingInterest = 0;
        
        // Detailed interest breakdown
        $accruedInterest = 0;
        $notDueInterest = 0;
        $paidInterest = 0;
        $outstandingInterestDetailed = 0;
        
        $currentDate = \Carbon\Carbon::now();
        $currentMonth = $currentDate->format('Y-m');
        
        foreach ($loansForInterest as $loan) {
            $loanAccruedInterest = 0;
            $loanNotDueInterest = 0;
            $loanOutstandingInterest = 0;
            $loanPaidInterest = 0;
            
            if ($loan->schedule && $loan->schedule->count() > 0) {
                foreach ($loan->schedule as $schedule) {
                    $principalPaid = $schedule->repayments->sum('principal');
                    $interestPaid = $schedule->repayments->sum('interest');
                    $repaidPrincipal += $principalPaid;
                    $repaidInterest += $interestPaid;
                    $outstandingPrincipal += max(0, $schedule->principal - $principalPaid);
                    $outstandingInterest += max(0, $schedule->interest - $interestPaid);
                    
                    // Calculate detailed interest breakdown per schedule
                    $scheduleDate = \Carbon\Carbon::parse($schedule->due_date);
                    $scheduleMonth = $scheduleDate->format('Y-m');
                    $scheduleInterest = $schedule->interest ?? 0;
                    
                    if ($scheduleMonth <= $currentMonth) {
                        // Interest is due up to this month - what's not paid is outstanding
                        $loanOutstandingInterest += max(0, $scheduleInterest - $interestPaid);
                    } else {
                        // Interest is not yet due
                        $loanNotDueInterest += $scheduleInterest;
                    }
                    
                    $loanPaidInterest += $interestPaid;
                }
            } else {
                // Fallback to simple calculation if no schedule
                $loanOutstandingInterest = max(0, ($loan->interest_amount ?? 0) - $loanPaidInterest);
                $loanNotDueInterest = 0;
                $loanAccruedInterest = 0;
            }
            
            // Calculate accrued interest for this loan (interest earned but not yet due)
            $loanStartDate = \Carbon\Carbon::parse($loan->disbursed_on);
            $monthsElapsed = $loanStartDate->diffInMonths($currentDate);
            $totalLoanMonths = $loan->period ?? 1;
            
            if ($monthsElapsed > 0 && $monthsElapsed < $totalLoanMonths) {
                // Calculate proportional interest earned but not yet due for this loan
                $loanAccruedInterest = ($loanNotDueInterest * $monthsElapsed) / $totalLoanMonths;
            }
            
            // Add this loan's amounts to totals
            $accruedInterest += $loanAccruedInterest;
            $notDueInterest += $loanNotDueInterest;
            $outstandingInterestDetailed += $loanOutstandingInterest;
            $paidInterest += $loanPaidInterest;
        }

        // Calculate Accrued Interest This Month and This Year from loan_schedules
        $currentYear = $currentDate->year;
        $currentMonthNum = $currentDate->month;
        
        // Expected Interest This Year - sum of interest from all schedules with due_date in current year
        $expectedInterestThisYear = \App\Models\LoanSchedule::whereHas('loan', function($q) use ($company, $selectedBranchId, $userBranchIds) {
            $q->whereHas('branch', function($bq) use ($company) {
                $bq->where('company_id', $company->id);
            })->when($selectedBranchId, function($q2) use ($selectedBranchId) {
                return $q2->where('branch_id', $selectedBranchId);
            }, function($q2) use ($userBranchIds) {
                if (!empty($userBranchIds)) {
                    return $q2->whereIn('branch_id', $userBranchIds);
                }
                return $q2;
            })->whereIn('status', ['active', 'written_off', 'defaulted']);
        })->whereYear('due_date', $currentYear)
          ->sum('interest');
        
        // Accrued Interest This Year - sum of accrued_interest from all schedules with due_date in current year
        $accruedInterestThisYear = \App\Models\LoanSchedule::whereHas('loan', function($q) use ($company, $selectedBranchId, $userBranchIds) {
            $q->whereHas('branch', function($bq) use ($company) {
                $bq->where('company_id', $company->id);
            })->when($selectedBranchId, function($q2) use ($selectedBranchId) {
                return $q2->where('branch_id', $selectedBranchId);
            }, function($q2) use ($userBranchIds) {
                if (!empty($userBranchIds)) {
                    return $q2->whereIn('branch_id', $userBranchIds);
                }
                return $q2;
            })->whereIn('status', ['active', 'written_off', 'defaulted']);
        })->whereYear('due_date', $currentYear)
          ->sum('accrued_interest');
        
        // Accrued Interest This Month - sum of accrued_interest from schedules with due_date in current month
        $accruedInterestThisMonth = \App\Models\LoanSchedule::whereHas('loan', function($q) use ($company, $selectedBranchId, $userBranchIds) {
            $q->whereHas('branch', function($bq) use ($company) {
                $bq->where('company_id', $company->id);
            })->when($selectedBranchId, function($q2) use ($selectedBranchId) {
                return $q2->where('branch_id', $selectedBranchId);
            }, function($q2) use ($userBranchIds) {
                if (!empty($userBranchIds)) {
                    return $q2->whereIn('branch_id', $userBranchIds);
                }
                return $q2;
            })->whereIn('status', ['active', 'written_off', 'defaulted']);
        })->whereYear('due_date', $currentYear)
          ->whereMonth('due_date', $currentMonthNum)
          ->sum('accrued_interest');

        // Collected amounts this year from repayments table
        $collectedPrincipalThisYear = \App\Models\Repayment::whereHas('loan', function($q) use ($company, $selectedBranchId, $userBranchIds) {
            $q->whereHas('branch', function($bq) use ($company) {
                $bq->where('company_id', $company->id);
            })->when($selectedBranchId, function($q2) use ($selectedBranchId) {
                return $q2->where('branch_id', $selectedBranchId);
            }, function($q2) use ($userBranchIds) {
                if (!empty($userBranchIds)) {
                    return $q2->whereIn('branch_id', $userBranchIds);
                }
                return $q2;
            });
        })->whereYear('payment_date', $currentYear)
          ->sum('principal');
        
        $collectedInterestThisYear = \App\Models\Repayment::whereHas('loan', function($q) use ($company, $selectedBranchId, $userBranchIds) {
            $q->whereHas('branch', function($bq) use ($company) {
                $bq->where('company_id', $company->id);
            })->when($selectedBranchId, function($q2) use ($selectedBranchId) {
                return $q2->where('branch_id', $selectedBranchId);
            }, function($q2) use ($userBranchIds) {
                if (!empty($userBranchIds)) {
                    return $q2->whereIn('branch_id', $userBranchIds);
                }
                return $q2;
            });
        })->whereYear('payment_date', $currentYear)
          ->sum('interest');
        
        $collectedFeeThisYear = \App\Models\Repayment::whereHas('loan', function($q) use ($company, $selectedBranchId, $userBranchIds) {
            $q->whereHas('branch', function($bq) use ($company) {
                $bq->where('company_id', $company->id);
            })->when($selectedBranchId, function($q2) use ($selectedBranchId) {
                return $q2->where('branch_id', $selectedBranchId);
            }, function($q2) use ($userBranchIds) {
                if (!empty($userBranchIds)) {
                    return $q2->whereIn('branch_id', $userBranchIds);
                }
                return $q2;
            });
        })->whereYear('payment_date', $currentYear)
          ->sum('fee_amount');
        
        $collectedPenaltyThisYear = \App\Models\Repayment::whereHas('loan', function($q) use ($company, $selectedBranchId, $userBranchIds) {
            $q->whereHas('branch', function($bq) use ($company) {
                $bq->where('company_id', $company->id);
            })->when($selectedBranchId, function($q2) use ($selectedBranchId) {
                return $q2->where('branch_id', $selectedBranchId);
            }, function($q2) use ($userBranchIds) {
                if (!empty($userBranchIds)) {
                    return $q2->whereIn('branch_id', $userBranchIds);
                }
                return $q2;
            });
        })->whereYear('payment_date', $currentYear)
          ->sum('penalt_amount');

        // Daily Accrued Interest for the past 7 days
        $dailyAccruedInterest = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayLabel = $date->format('D, M d');
            
            // Sum accrued_interest from schedules with due_date on this day
            $dayAccrued = \App\Models\LoanSchedule::whereHas('loan', function($q) use ($company, $selectedBranchId, $userBranchIds) {
                $q->whereHas('branch', function($bq) use ($company) {
                    $bq->where('company_id', $company->id);
                })->when($selectedBranchId, function($q2) use ($selectedBranchId) {
                    return $q2->where('branch_id', $selectedBranchId);
                }, function($q2) use ($userBranchIds) {
                    if (!empty($userBranchIds)) {
                        return $q2->whereIn('branch_id', $userBranchIds);
                    }
                    return $q2;
                })->whereIn('status', ['active', 'written_off', 'defaulted']);
            })->whereDate('due_date', $date->toDateString())
              ->sum('accrued_interest');
            
            $dailyAccruedInterest[] = [
                'date' => $dayLabel,
                'amount' => (float) $dayAccrued,
            ];
        }

        $penaltyBalance = LoanPenaltyService::getTotalPenaltyBalance($selectedBranchId);
        info('penaltyBalance'.$penaltyBalance);

        // Get Contributions data - products with their total balances
        $contributions = ContributionProduct::where('company_id', $company->id)
        ->when($selectedBranchId, function($query) use ($selectedBranchId) {
            return $query->where('branch_id', $selectedBranchId);
        }, function($query) use ($userBranchIds) {
            if (!empty($userBranchIds)) {
                return $query->whereIn('branch_id', $userBranchIds);
            }
            return $query;
        })
        ->where('is_active', true)
        ->get()
        ->map(function($product) use ($selectedBranchId, $userBranchIds) {
            // Get balance from accounts
            $accountBalance = ContributionAccount::where('contribution_product_id', $product->id)
                ->when($selectedBranchId, function($query) use ($selectedBranchId) {
                    return $query->where('branch_id', $selectedBranchId);
                }, function($query) use ($userBranchIds) {
                    if (!empty($userBranchIds)) {
                        return $query->whereIn('branch_id', $userBranchIds);
                    }
                    return $query;
                })
                ->sum('balance');
            
            // Get balance from GL transactions (including journals) for the liability account
            if ($product->liability_account_id) {
                $glCredits = \App\Models\GlTransaction::whereIn('transaction_type', ['contribution_deposit', 'journal'])
                    ->where('nature', 'credit')
                    ->where('chart_account_id', $product->liability_account_id)
                    ->when($selectedBranchId, function($query) use ($selectedBranchId) {
                        return $query->where('branch_id', $selectedBranchId);
                    }, function($query) use ($userBranchIds) {
                        if (!empty($userBranchIds)) {
                            return $query->whereIn('branch_id', $userBranchIds);
                        }
                        return $query;
                    })
                    ->sum('amount');
                
                $glDebits = \App\Models\GlTransaction::whereIn('transaction_type', ['contribution_withdrawal', 'contribution_transfer', 'journal'])
                    ->where('nature', 'debit')
                    ->where('chart_account_id', $product->liability_account_id)
                    ->when($selectedBranchId, function($query) use ($selectedBranchId) {
                        return $query->where('branch_id', $selectedBranchId);
                    }, function($query) use ($userBranchIds) {
                        if (!empty($userBranchIds)) {
                            return $query->whereIn('branch_id', $userBranchIds);
                        }
                        return $query;
                    })
                    ->sum('amount');
                
                $glBalance = $glCredits - $glDebits;
                // Use GL balance if available, otherwise fall back to account balance
                $totalBalance = $glBalance;
            } else {
                $totalBalance = $accountBalance;
            }
            
            return [
                'id' => $product->id,
                'product_name' => $product->product_name,
                'balance' => $totalBalance ?? 0,
            ];
        });

        // Get Arrears Classifications with loan amounts for each bucket
        $arrearsClassifications = \App\Models\ArrearsClassification::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function($classification) use ($company, $selectedBranchId, $userBranchIds) {
                // Get loans in this classification bucket
                $loansQuery = \App\Models\Loan::with(['schedule.repayments'])
                    ->whereHas('branch', function($q) use ($company) {
                        $q->where('company_id', $company->id);
                    })
                    ->when($selectedBranchId, function($q) use ($selectedBranchId) {
                        return $q->where('branch_id', $selectedBranchId);
                    }, function($q) use ($userBranchIds) {
                        if (!empty($userBranchIds)) {
                            return $q->whereIn('branch_id', $userBranchIds);
                        }
                        return $q;
                    })
                    ->whereIn('status', ['active', 'disbursed', 'defaulted'])
                    ->get();
                
                $totalAmount = 0;
                $loanCount = 0;
                
                foreach ($loansQuery as $loan) {
                    // Calculate days in arrears for this loan
                    $oldestUnpaidSchedule = $loan->schedule
                        ->filter(function($s) {
                            $paidAmount = $s->repayments->sum(fn($r) => $r->principal + $r->interest);
                            $totalDue = $s->principal + ($s->accrued_interest ?? $s->interest);
                            return $paidAmount < $totalDue;
                        })
                        ->filter(function($s) {
                            return \Carbon\Carbon::parse($s->due_date)->lt(now());
                        })
                        ->sortBy('due_date')
                        ->first();
                    
                    $daysInArrears = 0;
                    if ($oldestUnpaidSchedule) {
                        $daysInArrears = \Carbon\Carbon::parse($oldestUnpaidSchedule->due_date)->diffInDays(now());
                    }
                    
                    // Check if loan falls in this bucket
                    $daysFrom = $classification->days_from;
                    $daysTo = $classification->days_to;
                    
                    $inBucket = false;
                    if ($daysTo === null) {
                        // For buckets like "181+ days" where days_to is null
                        $inBucket = $daysInArrears >= $daysFrom;
                    } else {
                        $inBucket = $daysInArrears >= $daysFrom && $daysInArrears <= $daysTo;
                    }
                    
                    if ($inBucket) {
                        // Calculate outstanding balance
                        $totalPaid = $loan->repayments?->sum(fn($r) => $r->principal + $r->interest) ?? 0;
                        $outstandingBalance = $loan->amount_total - $totalPaid;
                        $totalAmount += max(0, $outstandingBalance);
                        $loanCount++;
                    }
                }
                
                return [
                    'id' => $classification->id,
                    'bucket_label' => $classification->bucket_label,
                    'status' => $classification->status,
                    'days_from' => $classification->days_from,
                    'days_to' => $classification->days_to,
                    'provision_percentage' => $classification->provision_percentage,
                    'total_amount' => $totalAmount,
                    'loan_count' => $loanCount,
                ];
            });

        // Get Shares data - products with their total balances (amount in TZS)
        // Note: ShareProduct doesn't have branch_id, so we filter by accounts' branch_id
        $shareProducts = ShareProduct::where('is_active', true)->get();
        
        $shares = $shareProducts->map(function($product) use ($selectedBranchId, $userBranchIds) {
            $accountsQuery = ShareAccount::where('share_product_id', $product->id);
            
            if ($selectedBranchId) {
                $accountsQuery->where('branch_id', $selectedBranchId);
            } elseif (!empty($userBranchIds)) {
                $accountsQuery->whereIn('branch_id', $userBranchIds);
            }
            
            // Calculate total number of shares
            $totalShares = $accountsQuery->sum('share_balance');
            
            // Convert to amount by multiplying by nominal price
            $nominalPrice = $product->nominal_price ?? 1;
            $totalBalance = $totalShares * $nominalPrice;
            
            return [
                'id' => $product->id,
                'share_name' => $product->share_name,
                'balance' => $totalBalance ?? 0,
            ];
        })->values();

        // Calculate cumulative profit/loss (YTD) for balance sheet equity section
        $cumulativeProfitLoss = $this->getCumulativeProfitLoss($selectedBranchId, $userBranchIds, now()->toDateString());

        // Get previous year comparative data
        $previousYearData = $this->getPreviousYearData($selectedBranchId, $userBranchIds);

        return view('dashboard', compact(
            'balanceSheetData',
            'financialReportData',
            'recentJournals',
            'recentPayments', 
            'recentReceipts',
            'receiptsThisYear',
            'penaltyBalance',
            'previousYearData',
            'cumulativeProfitLoss',
            'totalLoanAmount',
            'totalPrincipal',
            'totalInterest',
            'repaidPrincipal',
            'repaidInterest',
            'outstandingPrincipal',
            'outstandingInterest',
            'accruedInterest',
            'expectedInterestThisYear',
            'accruedInterestThisMonth',
            'accruedInterestThisYear',
            'collectedPrincipalThisYear',
            'collectedInterestThisYear',
            'collectedFeeThisYear',
            'collectedPenaltyThisYear',
            'notDueInterest',
            'paidInterest',
            'outstandingInterestDetailed',
            'branches',
            'selectedBranchId',
            'contributions',
            'shares',
            'arrearsClassifications',
            'dailyAccruedInterest'
        ));
    }
    
    
    private function getBalanceSheetData($branchId = null, array $permittedBranchIds = [])
    {
        $company = auth()->user()->company;
        
        if (!$company) {
            return [];
        }
        
        // Get balance sheet data directly from gl_transactions
        // Balance sheet shows cumulative balances up to today (no date filter)
        // This ensures all historical balances are included, including retained earnings from previous years
        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            // Filter to only include transactions up to today
            ->whereDate('gl_transactions.date', '<=', now()->toDateString())
            ->select(
                'account_class.name as class_name',
                'account_class_groups.group_code as class_code',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as total_debit'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as total_credit'),
                DB::raw('COUNT(DISTINCT chart_accounts.id) as account_count')
            )
            ->groupBy('account_class.id', 'account_class.name', 'account_class_groups.group_code');

        if (!empty($permittedBranchIds)) {
            $query->whereIn('gl_transactions.branch_id', $permittedBranchIds);
        }
        if ($branchId) {
            $query->where('gl_transactions.branch_id', $branchId);
        }
        if (empty($permittedBranchIds) && $company) {
            $branchIds = \App\Models\Branch::where('company_id', $company->id)->pluck('id')->toArray();
            if (!empty($branchIds)) {
                $query->whereIn('gl_transactions.branch_id', $branchIds);
            }
        }

        $balanceSheetData = $query->get()
            ->map(function ($item) {
                // Calculate balance based on account class
                $balance = 0;
                switch (strtolower($item->class_name)) {
                    case 'assets':
                        $balance = $item->total_debit - $item->total_credit; // Assets: debit increases
                        break;
                    case 'liabilities':
                        $balance = $item->total_credit - $item->total_debit; // Liabilities: credit increases
                        break;
                    case 'equity':
                        $balance = $item->total_credit - $item->total_debit; // Equity: credit increases
                        break;
                    case 'income':
                    case 'revenue':
                        $balance = $item->total_credit - $item->total_debit; // Revenue: credit increases
                        break;
                    case 'expenses':
                    case 'expense':
                        $balance = $item->total_debit - $item->total_credit; // Expenses: debit increases
                        break;
                    default:
                        $balance = $item->total_debit - $item->total_credit;
                }
                
                return [
                    'class_name' => $item->class_name,
                    'class_code' => $item->class_code,
                    'balance' => $balance,
                    'account_count' => $item->account_count
                ];
            })
            ->sortByDesc(function ($item) {
                return abs($item['balance']);
            })
            ->values()
            ->toArray();
            
        return $balanceSheetData;
    }
    
    private function getFinancialReportData($branchId = null, array $permittedBranchIds = [], $balanceSheetEndDate = null, $incomeStatementEndDate = null, $incomeStatementStartDate = null)
    {
        $company = auth()->user()->company;
        
        // Balance Sheet Query: Cumulative balances up to end date (Assets, Liabilities, Equity)
        // Balance Sheet accounts carry forward, so we need ALL transactions up to the end date
        $balanceSheetQuery = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->leftJoin('main_groups', 'account_class_groups.main_group_id', '=', 'main_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->whereIn('account_class.name', ['assets', 'liabilities', 'equity']);
        
        // Balance Sheet: Filter by end date only (cumulative up to that date)
        if ($balanceSheetEndDate) {
            $balanceSheetQuery->where(DB::raw('DATE(gl_transactions.date)'), '<=', $balanceSheetEndDate);
        }
        
        // Income Statement Query: Period-based (Revenue and Expenses)
        // Income Statement accounts reset each year, so we need transactions from start to end date
        $incomeStatementQuery = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->leftJoin('main_groups', 'account_class_groups.main_group_id', '=', 'main_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->leftJoin('journals', function($join) {
                $join->on('gl_transactions.transaction_id', '=', 'journals.id')
                     ->where('gl_transactions.transaction_type', '=', 'journal');
            })
            ->where('account_class_groups.company_id', $company->id)
            ->whereIn('account_class.name', ['income', 'revenue', 'expenses', 'expense']);
        
        // Income Statement: Filter by date range (YTD: from year start to end date)
        if ($incomeStatementStartDate && $incomeStatementEndDate) {
            $incomeStatementQuery->whereBetween(DB::raw('DATE(gl_transactions.date)'), [$incomeStatementStartDate, $incomeStatementEndDate])
                  // Exclude year-end closing entries from income statement calculations
                  ->where(function($q) {
                      $q->whereNull('journals.reference_type')
                        ->orWhere('journals.reference_type', '!=', 'Year-End Close');
                  });
        } elseif ($incomeStatementEndDate) {
            $incomeStatementQuery->where(DB::raw('DATE(gl_transactions.date)'), '<=', $incomeStatementEndDate);
        }
        
        // Common select and group by
        $selectFields = [
                'chart_accounts.id as account_id',
                'chart_accounts.account_name as account',
                'chart_accounts.account_code',
                'chart_accounts.parent_id',
                'account_class.name as class_name',
                'account_class_groups.id as fsli_id',
                'account_class_groups.name as fsli_name',
                'main_groups.id as main_group_id',
                'main_groups.name as main_group_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as debit_total'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as credit_total')
        ];
        
        $groupByFields = [
            'chart_accounts.id', 'chart_accounts.account_name', 'chart_accounts.account_code', 'chart_accounts.parent_id',
                     'account_class.name', 'account_class_groups.id', 'account_class_groups.name',
            'main_groups.id', 'main_groups.name'
        ];

        // Apply branch filters (fallback to all company branches when user has none assigned)
        $effectiveBranchIds = $permittedBranchIds;
        if (empty($effectiveBranchIds) && $company) {
            $effectiveBranchIds = \App\Models\Branch::where('company_id', $company->id)->pluck('id')->toArray();
        }
        if (!empty($effectiveBranchIds)) {
            $balanceSheetQuery->whereIn('gl_transactions.branch_id', $effectiveBranchIds);
            $incomeStatementQuery->whereIn('gl_transactions.branch_id', $effectiveBranchIds);
        }
        if ($branchId) {
            $balanceSheetQuery->where('gl_transactions.branch_id', $branchId);
            $incomeStatementQuery->where('gl_transactions.branch_id', $branchId);
        }

        // Execute queries
        $balanceSheetData = (clone $balanceSheetQuery)
            ->select($selectFields)
            ->groupBy($groupByFields)
            ->get();
        
        $incomeStatementData = (clone $incomeStatementQuery)
            ->select($selectFields)
            ->groupBy($groupByFields)
            ->get();
        
        // Fetch ALL accounts for this company to ensure parent accounts are included even if they have no transactions
        $allCompanyAccounts = DB::table('chart_accounts')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->leftJoin('main_groups', 'account_class_groups.main_group_id', '=', 'main_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->select([
                'chart_accounts.id as account_id',
                'chart_accounts.account_name as account',
                'chart_accounts.account_code',
                'chart_accounts.parent_id',
                'account_class.name as class_name',
                'account_class_groups.id as fsli_id',
                'account_class_groups.name as fsli_name',
                'main_groups.id as main_group_id',
                'main_groups.name as main_group_name',
            ])
            ->get()
            ->keyBy('account_id');

        // Combine transaction data
        $transactionBalances = $balanceSheetData->merge($incomeStatementData)->keyBy('account_id');
        
        // Map balances to the full account list
        $chartAccountsData = $allCompanyAccounts->map(function ($account) use ($transactionBalances) {
            $balanceData = $transactionBalances->get($account->account_id);
            $account->debit_total = $balanceData->debit_total ?? 0;
            $account->credit_total = $balanceData->credit_total ?? 0;
            return $account;
        });

        // Group by account class using hierarchical structure: main_groups -> fslis -> accounts
        $chartAccountsAssets = [];
        $chartAccountsLiabilities = [];
        $chartAccountsEquitys = [];
        $chartAccountsRevenues = [];
        $chartAccountsExpense = [];
        
        foreach ($chartAccountsData as $account) {
            // Calculate balance based on account class
            $balance = 0;
            
            // Get main group name (fallback to 'Uncategorized' if null)
            $mainGroupName = $account->main_group_name ?? 'Uncategorized';
            $fsliName = $account->fsli_name ?? 'Uncategorized';
            
            // Categorize based on account class
            switch (strtolower($account->class_name)) {
                case 'assets':
                    $balance = $account->debit_total - $account->credit_total; // Assets: debit increases
                    $chartAccountsAssets[$mainGroupName]['fslis'][$fsliName]['accounts'][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'account_code' => $account->account_code ?? '',
                        'parent_id' => $account->parent_id,
                        'sum' => $balance
                    ];
                    // Calculate totals
                    if (!isset($chartAccountsAssets[$mainGroupName]['fslis'][$fsliName]['total'])) {
                        $chartAccountsAssets[$mainGroupName]['fslis'][$fsliName]['total'] = 0;
                    }
                    $chartAccountsAssets[$mainGroupName]['fslis'][$fsliName]['total'] += $balance;
                    if (!isset($chartAccountsAssets[$mainGroupName]['total'])) {
                        $chartAccountsAssets[$mainGroupName]['total'] = 0;
                    }
                    $chartAccountsAssets[$mainGroupName]['total'] += $balance;
                    break;
                case 'liabilities':
                    $balance = $account->credit_total - $account->debit_total; // Liabilities: credit increases
                    $chartAccountsLiabilities[$mainGroupName]['fslis'][$fsliName]['accounts'][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'account_code' => $account->account_code ?? '',
                        'parent_id' => $account->parent_id,
                        'sum' => $balance
                    ];
                    // Calculate totals
                    if (!isset($chartAccountsLiabilities[$mainGroupName]['fslis'][$fsliName]['total'])) {
                        $chartAccountsLiabilities[$mainGroupName]['fslis'][$fsliName]['total'] = 0;
                    }
                    $chartAccountsLiabilities[$mainGroupName]['fslis'][$fsliName]['total'] += $balance;
                    if (!isset($chartAccountsLiabilities[$mainGroupName]['total'])) {
                        $chartAccountsLiabilities[$mainGroupName]['total'] = 0;
                    }
                    $chartAccountsLiabilities[$mainGroupName]['total'] += $balance;
                    break;
                case 'equity':
                    $balance = $account->credit_total - $account->debit_total; // Equity: credit increases
                    $chartAccountsEquitys[$mainGroupName]['fslis'][$fsliName]['accounts'][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'account_code' => $account->account_code ?? '',
                        'parent_id' => $account->parent_id,
                        'sum' => $balance
                    ];
                    // Calculate totals
                    if (!isset($chartAccountsEquitys[$mainGroupName]['fslis'][$fsliName]['total'])) {
                        $chartAccountsEquitys[$mainGroupName]['fslis'][$fsliName]['total'] = 0;
                    }
                    $chartAccountsEquitys[$mainGroupName]['fslis'][$fsliName]['total'] += $balance;
                    if (!isset($chartAccountsEquitys[$mainGroupName]['total'])) {
                        $chartAccountsEquitys[$mainGroupName]['total'] = 0;
                    }
                    $chartAccountsEquitys[$mainGroupName]['total'] += $balance;
                    break;
                case 'income':
                case 'revenue':
                    $balance = $account->credit_total - $account->debit_total; // Revenue: credit increases
                    $chartAccountsRevenues[$mainGroupName]['fslis'][$fsliName]['accounts'][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'account_code' => $account->account_code ?? '',
                        'parent_id' => $account->parent_id,
                        'sum' => $balance
                    ];
                    // Calculate totals
                    if (!isset($chartAccountsRevenues[$mainGroupName]['fslis'][$fsliName]['total'])) {
                        $chartAccountsRevenues[$mainGroupName]['fslis'][$fsliName]['total'] = 0;
                    }
                    $chartAccountsRevenues[$mainGroupName]['fslis'][$fsliName]['total'] += $balance;
                    if (!isset($chartAccountsRevenues[$mainGroupName]['total'])) {
                        $chartAccountsRevenues[$mainGroupName]['total'] = 0;
                    }
                    $chartAccountsRevenues[$mainGroupName]['total'] += $balance;
                    break;
                case 'expenses':
                case 'expense':
                    $balance = $account->debit_total - $account->credit_total; // Expenses: debit increases
                    $chartAccountsExpense[$mainGroupName]['fslis'][$fsliName]['accounts'][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'account_code' => $account->account_code ?? '',
                        'parent_id' => $account->parent_id,
                        'sum' => $balance
                    ];
                    // Calculate totals
                    if (!isset($chartAccountsExpense[$mainGroupName]['fslis'][$fsliName]['total'])) {
                        $chartAccountsExpense[$mainGroupName]['fslis'][$fsliName]['total'] = 0;
                    }
                    $chartAccountsExpense[$mainGroupName]['fslis'][$fsliName]['total'] += $balance;
                    if (!isset($chartAccountsExpense[$mainGroupName]['total'])) {
                        $chartAccountsExpense[$mainGroupName]['total'] = 0;
                    }
                    $chartAccountsExpense[$mainGroupName]['total'] += $balance;
                    break;
            }
        }
        
        // Calculate profit/loss (sum all main group totals)
        $sumRevenue = collect($chartAccountsRevenues)->sum(function($mainGroup) {
            return $mainGroup['total'] ?? 0;
        });
        $sumExpense = collect($chartAccountsExpense)->sum(function($mainGroup) {
            return $mainGroup['total'] ?? 0;
        });
        $profitLoss = $sumRevenue - $sumExpense;

        // Apply nesting to all categories
        $categories = [
            'chartAccountsAssets' => &$chartAccountsAssets,
            'chartAccountsLiabilities' => &$chartAccountsLiabilities,
            'chartAccountsEquitys' => &$chartAccountsEquitys,
            'chartAccountsRevenues' => &$chartAccountsRevenues,
            'chartAccountsExpense' => &$chartAccountsExpense,
        ];

        foreach ($categories as $key => &$category) {
            foreach ($category as $mgName => &$mg) {
                if (isset($mg['fslis'])) {
                    foreach ($mg['fslis'] as $fsliName => &$fsli) {
                        if (isset($fsli['accounts'])) {
                            $fsli['accounts'] = $this->nestAccounts($fsli['accounts']);
                        }
                    }
                }
            }
        }
        
        return [
            'chartAccountsAssets' => $chartAccountsAssets,
            'chartAccountsLiabilities' => $chartAccountsLiabilities,
            'chartAccountsEquitys' => $chartAccountsEquitys,
            'chartAccountsRevenues' => $chartAccountsRevenues,
            'chartAccountsExpense' => $chartAccountsExpense,
            'profitLoss' => $profitLoss
        ];
    }
    
    /**
     * Calculate cumulative profit/loss from ALL income statement transactions up to end date
     * This is used for Balance Sheet to show accumulated profits from all years
     * Excludes year-end closing entries
     */
    private function getCumulativeProfitLoss($branchId = null, array $permittedBranchIds = [], $endDate = null)
    {
        $company = auth()->user()->company;
        
        // Query ALL income statement transactions up to end date (cumulative)
        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->leftJoin('journals', function($join) {
                $join->on('gl_transactions.transaction_id', '=', 'journals.id')
                     ->where('gl_transactions.transaction_type', '=', 'journal');
            })
            ->where('account_class_groups.company_id', $company->id)
            ->whereIn('account_class.name', ['income', 'revenue', 'expenses', 'expense']);
        
        // Filter by end date (cumulative up to that date)
        if ($endDate) {
            $query->where(DB::raw('DATE(gl_transactions.date)'), '<=', $endDate);
        }
        
        // Exclude year-end closing entries (these zero out accounts, so we don't want them in cumulative calculation)
        $query->where(function($q) {
            $q->whereNull('journals.reference_type')
              ->orWhere('journals.reference_type', '!=', 'Year-End Close');
        });
        
        // Apply branch filters (fallback to all company branches when user has none assigned)
        $effectiveBranchIds = $permittedBranchIds;
        if (empty($effectiveBranchIds) && $company) {
            $effectiveBranchIds = \App\Models\Branch::where('company_id', $company->id)->pluck('id')->toArray();
        }
        if (!empty($effectiveBranchIds)) {
            $query->whereIn('gl_transactions.branch_id', $effectiveBranchIds);
        }
        if ($branchId) {
            $query->where('gl_transactions.branch_id', $branchId);
        }
        
        // Calculate revenue and expenses
        $revenueRow = (clone $query)
            ->whereIn('account_class.name', ['income', 'revenue'])
            ->selectRaw('COALESCE(SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE -gl_transactions.amount END), 0) as revenue')
            ->first();
        
        $expenseRow = (clone $query)
            ->whereIn('account_class.name', ['expenses', 'expense'])
            ->selectRaw('COALESCE(SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE -gl_transactions.amount END), 0) as expense')
            ->first();
        
        $revenue = (float)($revenueRow->revenue ?? 0);
        $expense = (float)($expenseRow->expense ?? 0);
        
        return $revenue - $expense;
    }
    
    private function getPreviousYearData($branchId = null, array $permittedBranchIds = [], $balanceSheetEndDate = null, $incomeStatementStartDate = null, $incomeStatementEndDate = null)
    {
        $company = auth()->user()->company;
        $currentYear = date('Y');
        $previousYear = $currentYear - 1;
        
        // Balance Sheet Query: Cumulative balances up to Dec 31 of previous year
        // Balance Sheet accounts carry forward, so we need ALL transactions up to Dec 31
        $balanceSheetQuery = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->leftJoin('main_groups', 'account_class_groups.main_group_id', '=', 'main_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->whereIn('account_class.name', ['assets', 'liabilities', 'equity']);
        
        // Balance Sheet: Cumulative up to Dec 31 of previous year
        if ($balanceSheetEndDate) {
            $balanceSheetQuery->where(DB::raw('DATE(gl_transactions.date)'), '<=', $balanceSheetEndDate);
        } else {
            // Fallback: use Dec 31 of previous year
            $balanceSheetQuery->where(DB::raw('DATE(gl_transactions.date)'), '<=', \Carbon\Carbon::create($previousYear, 12, 31)->toDateString());
        }
        
        // Income Statement Query: Period-based from Jan 1 to Dec 31 of previous year
        // Income Statement accounts reset each year, so we need transactions for the full previous year
        $incomeStatementQuery = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->leftJoin('main_groups', 'account_class_groups.main_group_id', '=', 'main_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->leftJoin('journals', function($join) {
                $join->on('gl_transactions.transaction_id', '=', 'journals.id')
                     ->where('gl_transactions.transaction_type', '=', 'journal');
            })
            ->where('account_class_groups.company_id', $company->id)
            ->whereIn('account_class.name', ['income', 'revenue', 'expenses', 'expense']);
        
        // Income Statement: Full year (Jan 1 to Dec 31 of previous year)
        if ($incomeStatementStartDate && $incomeStatementEndDate) {
            $incomeStatementQuery->whereBetween(DB::raw('DATE(gl_transactions.date)'), [$incomeStatementStartDate, $incomeStatementEndDate])
                  // Exclude year-end closing entries
                  ->where(function($q) {
                      $q->whereNull('journals.reference_type')
                        ->orWhere('journals.reference_type', '!=', 'Year-End Close');
                  });
        } else {
            // Fallback: use full previous year
            $prevYearStart = \Carbon\Carbon::create($previousYear, 1, 1)->toDateString();
            $prevYearEnd = \Carbon\Carbon::create($previousYear, 12, 31)->toDateString();
            $incomeStatementQuery->whereBetween(DB::raw('DATE(gl_transactions.date)'), [$prevYearStart, $prevYearEnd])
                  ->where(function($q) {
                      $q->whereNull('journals.reference_type')
                        ->orWhere('journals.reference_type', '!=', 'Year-End Close');
                  });
        }
        
        // Common select and group by
        $selectFields = [
                'chart_accounts.id as account_id',
                'chart_accounts.account_name as account',
                'chart_accounts.account_code',
                'chart_accounts.parent_id',
                'account_class.name as class_name',
                'account_class_groups.id as fsli_id',
                'account_class_groups.name as fsli_name',
                'main_groups.id as main_group_id',
                'main_groups.name as main_group_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as debit_total'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as credit_total')
        ];
        
        $groupByFields = [
            'chart_accounts.id', 'chart_accounts.account_name', 'chart_accounts.account_code', 'chart_accounts.parent_id',
                     'account_class.name', 'account_class_groups.id', 'account_class_groups.name',
            'main_groups.id', 'main_groups.name'
        ];

        // Apply branch filters (fallback to all company branches when user has none assigned)
        $effectiveBranchIds = $permittedBranchIds;
        if (empty($effectiveBranchIds) && $company) {
            $effectiveBranchIds = \App\Models\Branch::where('company_id', $company->id)->pluck('id')->toArray();
        }
        if (!empty($effectiveBranchIds)) {
            $balanceSheetQuery->whereIn('gl_transactions.branch_id', $effectiveBranchIds);
            $incomeStatementQuery->whereIn('gl_transactions.branch_id', $effectiveBranchIds);
        }
        if ($branchId) {
            $balanceSheetQuery->where('gl_transactions.branch_id', $branchId);
            $incomeStatementQuery->where('gl_transactions.branch_id', $branchId);
        }

        // Execute queries
        $balanceSheetData = (clone $balanceSheetQuery)
            ->select($selectFields)
            ->groupBy($groupByFields)
            ->get();
        
        $incomeStatementData = (clone $incomeStatementQuery)
            ->select($selectFields)
            ->groupBy($groupByFields)
            ->get();
        
        // Fetch ALL accounts for this company to ensure parent accounts are included even if they have no transactions
        $allCompanyAccounts = DB::table('chart_accounts')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->leftJoin('main_groups', 'account_class_groups.main_group_id', '=', 'main_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->select([
                'chart_accounts.id as account_id',
                'chart_accounts.account_name as account',
                'chart_accounts.account_code',
                'chart_accounts.parent_id',
                'account_class.name as class_name',
                'account_class_groups.id as fsli_id',
                'account_class_groups.name as fsli_name',
                'main_groups.id as main_group_id',
                'main_groups.name as main_group_name',
            ])
            ->get()
            ->keyBy('account_id');

        // Combine transaction data
        $transactionBalances = $balanceSheetData->merge($incomeStatementData)->keyBy('account_id');
        
        // Map balances to the full account list
        $previousYearDataFlat = $allCompanyAccounts->map(function ($account) use ($transactionBalances) {
            $balanceData = $transactionBalances->get($account->account_id);
            $account->debit_total = $balanceData->debit_total ?? 0;
            $account->credit_total = $balanceData->credit_total ?? 0;
            return $account;
        });
            
        // Group by account class using hierarchical structure: main_groups -> fslis -> accounts
        $previousYearAssets = [];
        $previousYearLiabilities = [];
        $previousYearEquitys = [];
        $previousYearRevenues = [];
        $previousYearExpense = [];
        
        foreach ($previousYearDataFlat as $account) {
            // Calculate balance based on account class
            $balance = 0;
            
            // Get main group name (fallback to 'Uncategorized' if null)
            $mainGroupName = $account->main_group_name ?? 'Uncategorized';
            $fsliName = $account->fsli_name ?? 'Uncategorized';
            
            // Categorize based on account class
            switch (strtolower($account->class_name)) {
                case 'assets':
                    $balance = $account->debit_total - $account->credit_total; // Assets: debit increases
                    $previousYearAssets[$mainGroupName]['fslis'][$fsliName]['accounts'][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'account_code' => $account->account_code ?? '',
                        'parent_id' => $account->parent_id,
                        'sum' => $balance
                    ];
                    // Calculate totals
                    if (!isset($previousYearAssets[$mainGroupName]['fslis'][$fsliName]['total'])) {
                        $previousYearAssets[$mainGroupName]['fslis'][$fsliName]['total'] = 0;
                    }
                    $previousYearAssets[$mainGroupName]['fslis'][$fsliName]['total'] += $balance;
                    if (!isset($previousYearAssets[$mainGroupName]['total'])) {
                        $previousYearAssets[$mainGroupName]['total'] = 0;
                    }
                    $previousYearAssets[$mainGroupName]['total'] += $balance;
                    break;
                case 'liabilities':
                    $balance = $account->credit_total - $account->debit_total; // Liabilities: credit increases
                    $previousYearLiabilities[$mainGroupName]['fslis'][$fsliName]['accounts'][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'account_code' => $account->account_code ?? '',
                        'parent_id' => $account->parent_id,
                        'sum' => $balance
                    ];
                    // Calculate totals
                    if (!isset($previousYearLiabilities[$mainGroupName]['fslis'][$fsliName]['total'])) {
                        $previousYearLiabilities[$mainGroupName]['fslis'][$fsliName]['total'] = 0;
                    }
                    $previousYearLiabilities[$mainGroupName]['fslis'][$fsliName]['total'] += $balance;
                    if (!isset($previousYearLiabilities[$mainGroupName]['total'])) {
                        $previousYearLiabilities[$mainGroupName]['total'] = 0;
                    }
                    $previousYearLiabilities[$mainGroupName]['total'] += $balance;
                    break;
                case 'equity':
                    $balance = $account->credit_total - $account->debit_total; // Equity: credit increases
                    $previousYearEquitys[$mainGroupName]['fslis'][$fsliName]['accounts'][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'account_code' => $account->account_code ?? '',
                        'parent_id' => $account->parent_id,
                        'sum' => $balance
                    ];
                    // Calculate totals
                    if (!isset($previousYearEquitys[$mainGroupName]['fslis'][$fsliName]['total'])) {
                        $previousYearEquitys[$mainGroupName]['fslis'][$fsliName]['total'] = 0;
                    }
                    $previousYearEquitys[$mainGroupName]['fslis'][$fsliName]['total'] += $balance;
                    if (!isset($previousYearEquitys[$mainGroupName]['total'])) {
                        $previousYearEquitys[$mainGroupName]['total'] = 0;
                    }
                    $previousYearEquitys[$mainGroupName]['total'] += $balance;
                    break;
                case 'income':
                case 'revenue':
                    $balance = $account->credit_total - $account->debit_total; // Revenue: credit increases
                    $previousYearRevenues[$mainGroupName]['fslis'][$fsliName]['accounts'][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'account_code' => $account->account_code ?? '',
                        'parent_id' => $account->parent_id,
                        'sum' => $balance
                    ];
                    // Calculate totals
                    if (!isset($previousYearRevenues[$mainGroupName]['fslis'][$fsliName]['total'])) {
                        $previousYearRevenues[$mainGroupName]['fslis'][$fsliName]['total'] = 0;
                    }
                    $previousYearRevenues[$mainGroupName]['fslis'][$fsliName]['total'] += $balance;
                    if (!isset($previousYearRevenues[$mainGroupName]['total'])) {
                        $previousYearRevenues[$mainGroupName]['total'] = 0;
                    }
                    $previousYearRevenues[$mainGroupName]['total'] += $balance;
                    break;
                case 'expenses':
                case 'expense':
                    $balance = $account->debit_total - $account->credit_total; // Expenses: debit increases
                    $previousYearExpense[$mainGroupName]['fslis'][$fsliName]['accounts'][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'account_code' => $account->account_code ?? '',
                        'parent_id' => $account->parent_id,
                        'sum' => $balance
                    ];
                    // Calculate totals
                    if (!isset($previousYearExpense[$mainGroupName]['fslis'][$fsliName]['total'])) {
                        $previousYearExpense[$mainGroupName]['fslis'][$fsliName]['total'] = 0;
                    }
                    $previousYearExpense[$mainGroupName]['fslis'][$fsliName]['total'] += $balance;
                    if (!isset($previousYearExpense[$mainGroupName]['total'])) {
                        $previousYearExpense[$mainGroupName]['total'] = 0;
                    }
                    $previousYearExpense[$mainGroupName]['total'] += $balance;
                    break;
            }
        }
        
        // Calculate previous year profit/loss (sum all main group totals)
        $sumRevenue = collect($previousYearRevenues)->sum(function($mainGroup) {
            return $mainGroup['total'] ?? 0;
        });
        $sumExpense = collect($previousYearExpense)->sum(function($mainGroup) {
            return $mainGroup['total'] ?? 0;
        });
        $previousYearProfitLoss = $sumRevenue - $sumExpense;

        // Apply nesting to all categories
        $categories = [
            'chartAccountsAssets' => &$previousYearAssets,
            'chartAccountsLiabilities' => &$previousYearLiabilities,
            'chartAccountsEquitys' => &$previousYearEquitys,
            'chartAccountsRevenues' => &$previousYearRevenues,
            'chartAccountsExpense' => &$previousYearExpense,
        ];

        foreach ($categories as $key => &$category) {
            foreach ($category as $mgName => &$mg) {
                if (isset($mg['fslis'])) {
                    foreach ($mg['fslis'] as $fsliName => &$fsli) {
                        if (isset($fsli['accounts'])) {
                            $fsli['accounts'] = $this->nestAccounts($fsli['accounts']);
                        }
                    }
                }
            }
        }
        
        return [
            'year' => $previousYear,
            'chartAccountsAssets' => $previousYearAssets,
            'chartAccountsLiabilities' => $previousYearLiabilities,
            'chartAccountsEquitys' => $previousYearEquitys,
            'chartAccountsRevenues' => $previousYearRevenues,
            'chartAccountsExpense' => $previousYearExpense,
            'profitLoss' => $previousYearProfitLoss
        ];
    }


    /**
     * Handle bulk SMS sending from dashboard
     */
    public function sendBulkSms(Request $request)
    {
        $request->validate([
            'branch_id' => 'required',
            'message_title' => 'required|string|max:100',
            'bulk_message_content' => 'required|string|max:500',
            'custom_title' => 'nullable|string|max:100',
        ]);

        $branchId = $request->branch_id;
        $title = $request->message_title;
        $customTitle = $request->custom_title;
        $messageContent = $request->bulk_message_content;

        // If 'Custom' is selected, use the custom title
        if ($title === 'Custom' && $customTitle) {
            $title = $customTitle;
        }

        // Get customers for the selected branch or all branches
        $customersQuery = \App\Models\Customer::query();
        if ($branchId !== 'all') {
            $customersQuery->where('branch_id', $branchId);
        }
        $customers = $customersQuery->whereNotNull('phone1')->get();

        $valid = 0;
        $invalid = 0;
        $duplicates = 0;
        $sentNumbers = [];
        $responses = [];

        foreach ($customers as $customer) {
            $phone = preg_replace('/[^0-9+]/', '', $customer->phone1);
            if (empty($phone) || in_array($phone, $sentNumbers)) {
                $invalid++;
                if (in_array($phone, $sentNumbers)) $duplicates++;
                continue;
            }
            $sentNumbers[] = $phone;
            $fullMessage = $title . ": " . $messageContent;
            //$smsResponse = \App\Helpers\SmsHelper::send($phone, $fullMessage);
            $responses[] = $smsResponse;
            $valid++;
            // Log SMS
            \DB::table('sms_logs')->insert([
                'customer_id' => $customer->id,
                'phone_number' => $phone,
                'message' => $fullMessage,
                'response' => $smsResponse,
                'sent_by' => auth()->id(),
                'sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Bulk SMS sent successfully!",
            'response' => [
                'message' => 'Message Submitted Successfully',
                'valid' => $valid,
                'invalid' => $invalid,
                'duplicates' => $duplicates,
                'details' => $responses
            ]
        ]);
    }

        /**
     * Nest accounts within an FSLI based on parent_id
     */
    private function nestAccounts(array $accounts)
    {
        $tree = [];
        $lookup = [];

        // First pass: create lookup and initialize children
        foreach ($accounts as $account) {
            $id = $account['account_id'];
            $lookup[$id] = $account;
            $lookup[$id]['children'] = [];
        }

        // Second pass: build tree
        foreach ($lookup as $id => &$account) {
            $parentId = $account['parent_id'] ?? null;
            if ($parentId && isset($lookup[$parentId])) {
                $lookup[$parentId]['children'][] = &$account;
            } else {
                $tree[] = &$account;
            }
        }

        // Third pass: Return the tree without filtering empty branches
        // This allows the view to decide which accounts to show (e.g. if they have balance in previous year)
        return $this->rollupBalances($tree);
    }

    /**
     * Roll up child balances to parents without filtering
     */
    private function rollupBalances(array $tree)
    {
        foreach ($tree as &$account) {
            if (!empty($account['children'])) {
                $account['children'] = $this->rollupBalances($account['children']);
                
                // Roll up children balances to the parent sum
                $childrenSum = 0;
                foreach ($account['children'] as $child) {
                    $childrenSum += ($child['sum'] ?? 0);
                }
                $account['sum'] = ($account['sum'] ?? 0) + $childrenSum;
            }
            }
        return $tree;
    }
}