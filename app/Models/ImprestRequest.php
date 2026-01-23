<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ImprestRequest extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'request_number',
        'employee_id',
        'company_id',
        'branch_id',
        'purpose',
        'amount_requested',
        'date_required',
        'status',
        'description',
        'created_by',
        'checked_by',
        'checked_at',
        'check_comments',
        'approved_by',
        'approved_at',
        'approval_comments',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'payment_id',
        'disbursed_by',
        'disbursed_at',
        'disbursed_amount',
    ];

    protected $casts = [
        'amount_requested' => 'decimal:2',
        'disbursed_amount' => 'decimal:2',
        'date_required' => 'date',
        'checked_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'disbursed_at' => 'datetime',
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function disburser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function disbursement(): HasOne
    {
        return $this->hasOne(ImprestDisbursement::class);
    }

    public function liquidation(): HasOne
    {
        return $this->hasOne(ImprestLiquidation::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(ImprestJournalEntry::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ImprestDocument::class);
    }

    public function imprestItems(): HasMany
    {
        return $this->hasMany(ImprestItem::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ImprestApproval::class);
    }

    // Scopes
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    // Methods
    public function canBeChecked(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'checked';
    }

    public function canBeDisbursed(): bool
    {
        return $this->status === 'approved' && !$this->payment_id && !$this->disbursement;
    }

    public function canBeLiquidated(): bool
    {
        return $this->status === 'disbursed' && ($this->payment_id || $this->disbursement) && !$this->liquidation;
    }

    public function canBeClosed(): bool
    {
        return $this->status === 'liquidated' && $this->liquidation && $this->liquidation->status === 'approved';
    }

    public function canUserCheck(User $user): bool
    {
        if ($this->requiresApproval()) {
            return $this->canUserApproveAtLevel($user, 1);
        }
        return $this->canBeChecked();
    }

    public function canUserApprove(User $user): bool
    {
        if ($this->requiresApproval()) {
            $currentLevel = $this->getCurrentApprovalLevel();
            return $currentLevel ? $this->canUserApproveAtLevel($user, $currentLevel) : false;
        }
        return $this->canBeApproved();
    }

    public function canUserDisburse(User $user): bool
    {
        if ($this->requiresApproval()) {
            return $this->isFullyApproved() && $this->canBeDisbursed();
        }
        return $this->canBeDisbursed();
    }

    public function getRemainingBalance(): float
    {
        if (!$this->disbursement) {
            return 0;
        }

        if (!$this->liquidation) {
            return (float) ($this->disbursed_amount ?? 0);
        }

        return (float) $this->liquidation->balance_returned;
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'badge bg-warning',
            'checked' => 'badge bg-info',
            'approved' => 'badge bg-primary',
            'disbursed' => 'badge bg-secondary',
            'liquidated' => 'badge bg-success',
            'closed' => 'badge bg-dark',
            'rejected' => 'badge bg-danger',
            default => 'badge bg-light text-dark'
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Review',
            'checked' => 'Checked by Manager',
            'approved' => 'Approved by Finance',
            'disbursed' => 'Funds Disbursed',
            'liquidated' => 'Retirement Submitted',
            'closed' => 'Closed',
            'rejected' => 'Rejected',
            default => ucfirst($this->status)
        };
    }

    // Generate unique request number
    public static function generateRequestNumber(): string
    {
        $year = date('Y');
        $month = date('m');

        $lastRequest = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastRequest ? (int)substr($lastRequest->request_number, -4) + 1 : 1;

        return 'IMP-' . $year . $month . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // Multi-level approval methods
    public function getApprovalSettings()
    {
        return ImprestApprovalSettings::where('company_id', $this->company_id)
            ->where('branch_id', $this->branch_id)
            ->first();
    }

    public function requiresApproval(): bool
    {
        $settings = $this->getApprovalSettings();
        return $settings && $settings->approval_required;
    }

    public function getRequiredApprovalLevels(): array
    {
        $levelDetails = $this->getRequiredApprovalLevelDetails();
        return array_column($levelDetails, 'level');
    }

    public function getRequiredApprovalLevelDetails(): array
    {
        $settings = $this->getApprovalSettings();

        if (!$settings || !$settings->approval_required) {
            return [];
        }

        $result = [];
        $maxLevels = $settings->approval_levels;

        for ($level = 1; $level <= $maxLevels; $level++) {
            $thresholdField = "level{$level}_amount_threshold";
            $approversField = "level{$level}_approvers";

            $threshold = $settings->{$thresholdField};
            $approvers = $settings->{$approversField};

            if (is_null($approvers) || empty($approvers)) {
                continue;
            }

            $result[] = [
                'level' => $level,
                'threshold' => $threshold,
                'approvers' => is_array($approvers) ? $approvers : json_decode($approvers, true) ?? []
            ];
        }

        return $result;
    }

    public function getPendingApprovals()
    {
        return $this->approvals()->pending()->get();
    }

    public function getCompletedApprovals()
    {
        return $this->approvals()->where('status', '!=', ImprestApproval::STATUS_PENDING)->get();
    }

    public function isFullyApproved(): bool
    {
        $settings = $this->getApprovalSettings();

        if (!$settings || !$settings->approval_required) {
            return true;
        }

        $requiredLevels = $this->getRequiredApprovalLevels();

        if (empty($requiredLevels)) {
            return true;
        }

        foreach ($requiredLevels as $level) {
            $approvalExists = $this->approvals()
                ->where('approval_level', $level)
                ->where('status', ImprestApproval::STATUS_APPROVED)
                ->exists();

            if (!$approvalExists) {
                return false;
            }
        }

        return true;
    }

    public function hasRejectedApprovals(): bool
    {
        return $this->approvals()->rejected()->exists();
    }

    public function getCurrentApprovalLevel(): ?int
    {
        $requiredLevels = $this->getRequiredApprovalLevels();

        foreach ($requiredLevels as $level) {
            $approved = $this->approvals()
                ->where('approval_level', $level)
                ->where('status', ImprestApproval::STATUS_APPROVED)
                ->exists();

            if (!$approved) {
                return $level;
            }
        }

        return null;
    }

    public function canUserApproveAtLevel(User $user, int $level): bool
    {
        $settings = $this->getApprovalSettings();
        if (!$settings) {
            return false;
        }

        $approvers = $settings->getApproversForLevel($level);
        return in_array($user->id, $approvers);
    }

    public function createApprovalRequests()
    {
        $requiredLevelDetails = $this->getRequiredApprovalLevelDetails();

        foreach ($requiredLevelDetails as $levelData) {
            $level = $levelData['level'];
            $approvers = $levelData['approvers'];

            foreach ($approvers as $approverId) {
                ImprestApproval::create([
                    'imprest_request_id' => $this->id,
                    'approval_level' => $level,
                    'approver_id' => $approverId,
                    'status' => ImprestApproval::STATUS_PENDING
                ]);
            }
        }
    }
}
