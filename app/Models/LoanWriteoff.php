<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanWriteoff extends Model
{
    use LogsActivity;

    protected $table = 'loan_writeoffs';

    protected $fillable = [
        'loan_id',
        'customer_id',
        'outstanding',
        'reason',
        'policy_reference',
        'external_reference',
        'document_path',
        'writeoff_type',
        'writeoff_date',
        'status',
        'reversal_of_id',
        'reversed_by_id',
        'previous_loan_status',
        'createdby',
    ];

    protected $casts = [
        'writeoff_date' => 'date',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'createdby');
    }

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(LoanWriteoff::class, 'reversal_of_id');
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(LoanWriteoff::class, 'reversed_by_id');
    }

    public function isReversal(): bool
    {
        return $this->reversal_of_id !== null;
    }

    public function isReversed(): bool
    {
        return $this->reversed_by_id !== null;
    }

    public function isReversible(): bool
    {
        return $this->status === 'posted' && !$this->isReversed();
    }

    public function approvals()
    {
        return $this->hasMany(LoanWriteoffApproval::class, 'loan_writeoff_id');
    }

    public function currentApproval()
    {
        return $this->approvals()
            ->pending()
            ->orderBy('approval_level')
            ->first();
    }

    public function isFullyApproved(): bool
    {
        $creator = $this->createdBy;
        if (!$creator) {
            return false;
        }
        $settings = LoanWriteoffApprovalSetting::where('company_id', $creator->company_id)->first();
        if (!$settings) {
            return $this->status === 'posted';
        }
        $requiredLevel = $settings->getRequiredApprovalLevel((float) $this->outstanding);
        if ($requiredLevel === 0) {
            return $this->status === 'posted';
        }
        return $this->approvals()
            ->where('approval_level', $requiredLevel)
            ->where('status', 'approved')
            ->exists();
    }

    public function isRejected(): bool
    {
        return $this->approvals()->rejected()->exists();
    }

    /**
     * Initialize approval workflow. Creates approval records for each level.
     */
    public function initializeApprovalWorkflow(): void
    {
        $creator = $this->createdBy;
        if (!$creator) {
            return;
        }
        $settings = LoanWriteoffApprovalSetting::where('company_id', $creator->company_id)->first();
        if (!$settings) {
            return;
        }
        $requiredLevel = $settings->getRequiredApprovalLevel((float) $this->outstanding);
        if ($requiredLevel === 0) {
            return;
        }
        for ($level = 1; $level <= $requiredLevel; $level++) {
            $approvalType = $settings->{"level{$level}_approval_type"};
            $approvers = $settings->{"level{$level}_approvers"} ?? [];
            if ($approvalType === 'role') {
                foreach ($approvers as $roleName) {
                    $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
                    if ($role) {
                        LoanWriteoffApproval::create([
                            'loan_writeoff_id' => $this->id,
                            'approval_level' => $level,
                            'approver_type' => 'role',
                            'approver_name' => $role->name,
                            'status' => 'pending',
                        ]);
                    }
                }
            } elseif ($approvalType === 'user') {
                foreach ($approvers as $userId) {
                    $userId = (int) $userId;
                    $user = User::find($userId);
                    if ($user) {
                        LoanWriteoffApproval::create([
                            'loan_writeoff_id' => $this->id,
                            'approval_level' => $level,
                            'approver_id' => $user->id,
                            'approver_type' => 'user',
                            'approver_name' => $user->name,
                            'status' => 'pending',
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Post GL transactions and mark loan as written off. Called when fully approved.
     */
    public function postGlAndCompleteWriteoff(): void
    {
        $loan = $this->loan()->with(['product', 'schedule.repayments'])->first();
        if (!$loan || !$loan->product) {
            throw new \Exception('Loan or product not found for write-off.');
        }
        $product = $loan->product;
        $breakdown = $loan->getOutstandingBreakdown();
        $branchId = $loan->branch_id ?? $this->createdBy->branch_id ?? null;
        $userId = auth()->id();

        if ($this->writeoff_type === 'direct') {
            $debitAccount = $product->direct_writeoff_account_id;
        } else {
            $debitAccount = $product->provision_writeoff_account_id;
        }
        $chartAccounts = $product->getWriteoffChartAccounts();
        $principalAccountId = $chartAccounts['principal'];

        $glBase = [
            'customer_id' => $loan->customer_id,
            'transaction_id' => $this->id,
            'transaction_type' => 'Loan Writeoff',
            'date' => $this->writeoff_date,
            'branch_id' => $branchId,
            'user_id' => $userId,
        ];

        \App\Models\GlTransaction::create(array_merge($glBase, [
            'chart_account_id' => $debitAccount,
            'amount' => $this->outstanding,
            'nature' => 'debit',
            'description' => 'Loan write-off',
        ]));

        $components = [
            'principal' => $breakdown['principal'],
            'interest' => $breakdown['interest'],
            'fee_amount' => $breakdown['fee_amount'],
            'penalty_amount' => $breakdown['penalty_amount'],
        ];
        foreach ($components as $component => $amount) {
            if ($amount <= 0) {
                continue;
            }
            $accountId = $chartAccounts[$component] ?? $principalAccountId;
            if (!$accountId) {
                $accountId = $principalAccountId;
            }
            \App\Models\GlTransaction::create(array_merge($glBase, [
                'chart_account_id' => $accountId,
                'amount' => $amount,
                'nature' => 'credit',
                'description' => 'Loan write-off (' . str_replace('_', ' ', $component) . ')',
            ]));
        }

        $previousStatus = $loan->status;
        $this->update(['status' => 'posted', 'previous_loan_status' => $previousStatus]);
        $loan->update(['status' => 'written_off']);

        // Mark all remaining schedule items as written off so they no longer show pending
        if ($loan->relationLoaded('schedule')) {
            foreach ($loan->schedule as $schedule) {
                // Use accessors to determine remaining amount
                $remaining = $schedule->remaining_amount ?? 0;
                if ($remaining > 0) {
                    $schedule->update([
                        'written_off' => true,
                        'written_off_at' => $this->writeoff_date ?? now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse this write-off. Creates reversing GL entries and restores loan status.
     */
    public function reverseWriteoff(string $reason): LoanWriteoff
    {
        if (!$this->isReversible()) {
            throw new \Exception('This write-off cannot be reversed.');
        }

        $loan = $this->loan()->with(['product', 'schedule.repayments'])->first();
        if (!$loan || !$loan->product) {
            throw new \Exception('Loan or product not found.');
        }

        $branchId = $loan->branch_id ?? $this->createdBy->branch_id ?? null;
        $userId = auth()->id();
        $reversalDate = now();

        return \Illuminate\Support\Facades\DB::transaction(function () use ($loan, $branchId, $userId, $reversalDate, $reason) {
            $reversal = new LoanWriteoff([
                'loan_id' => $this->loan_id,
                'customer_id' => $this->customer_id,
                'outstanding' => $this->outstanding,
                'reason' => $reason,
                'writeoff_type' => $this->writeoff_type,
                'writeoff_date' => $reversalDate,
                'status' => 'reversal',
                'reversal_of_id' => $this->id,
                'reversed_by_id' => null,
                'previous_loan_status' => null,
                'createdby' => $userId,
            ]);
            $reversal->save();

            $originalGl = \App\Models\GlTransaction::where('transaction_id', $this->id)
                ->where('transaction_type', 'Loan Writeoff')
                ->get();

            $glBase = [
                'customer_id' => $loan->customer_id,
                'transaction_id' => $reversal->id,
                'transaction_type' => 'Loan Writeoff Reversal',
                'date' => $reversalDate,
                'branch_id' => $branchId,
                'user_id' => $userId,
            ];

            foreach ($originalGl as $gl) {
                \App\Models\GlTransaction::create(array_merge($glBase, [
                    'chart_account_id' => $gl->chart_account_id,
                    'amount' => $gl->amount,
                    'nature' => $gl->nature === 'debit' ? 'credit' : 'debit',
                    'description' => 'Loan write-off reversal (ref: #' . $this->id . ')',
                ]));
            }

            $this->update(['reversed_by_id' => $reversal->id]);
            $loan->update(['status' => $this->previous_loan_status ?? 'active']);

            // Clear written-off flags on schedules when reversing the write-off
            $loan->schedule()
                ->where('written_off', true)
                ->update(['written_off' => false, 'written_off_at' => null]);

            return $reversal;
        });
    }
}
