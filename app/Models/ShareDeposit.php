<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShareDeposit extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'share_account_id',
        'deposit_date',
        'deposit_amount',
        'number_of_shares',
        'charge_amount',
        'total_amount',
        'transaction_reference',
        'bank_account_id',
        'liability_account_id',
        'share_capital_account_id',
        'cheque_number',
        'notes',
        'status',
        'branch_id',
        'company_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'deposit_date' => 'date',
        'deposit_amount' => 'decimal:2',
        'number_of_shares' => 'decimal:4',
        'charge_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the share account
     */
    public function shareAccount(): BelongsTo
    {
        return $this->belongsTo(ShareAccount::class);
    }

    /**
     * Get the bank account
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Get the liability account
     */
    public function liabilityAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'liability_account_id');
    }

    /**
     * Get the share capital account
     */
    public function shareCapitalAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'share_capital_account_id');
    }

    /**
     * Get the branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created this deposit
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this deposit
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
