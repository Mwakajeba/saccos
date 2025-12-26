<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShareTransfer extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'from_account_id',
        'to_account_id',
        'transfer_date',
        'number_of_shares',
        'transfer_amount',
        'transfer_fee',
        'transaction_reference',
        'bank_account_id',
        'journal_reference_id',
        'fee_income_account_id',
        'notes',
        'status',
        'branch_id',
        'company_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'number_of_shares' => 'decimal:4',
        'transfer_amount' => 'decimal:2',
        'transfer_fee' => 'decimal:2',
    ];

    /**
     * Get the source account (from account)
     */
    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(ShareAccount::class, 'from_account_id');
    }

    /**
     * Get the destination account (to account)
     */
    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(ShareAccount::class, 'to_account_id');
    }

    /**
     * Get the bank account (for fee payment)
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Get the journal reference
     */
    public function journalReference(): BelongsTo
    {
        return $this->belongsTo(JournalReference::class);
    }

    /**
     * Get the fee income account
     */
    public function feeIncomeAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'fee_income_account_id');
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
     * Get the user who created this transfer
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this transfer
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
