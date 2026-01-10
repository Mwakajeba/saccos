<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpeningBalanceLog extends Model
{
    protected $fillable = [
        'type',
        'customer_id',
        'contribution_account_id',
        'contribution_product_id',
        'share_account_id',
        'share_product_id',
        'amount',
        'date',
        'description',
        'transaction_reference',
        'receipt_id',
        'journal_id',
        'share_deposit_id',
        'branch_id',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function contributionAccount(): BelongsTo
    {
        return $this->belongsTo(ContributionAccount::class);
    }

    public function contributionProduct(): BelongsTo
    {
        return $this->belongsTo(ContributionProduct::class);
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(Receipt::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shareAccount(): BelongsTo
    {
        return $this->belongsTo(ShareAccount::class);
    }

    public function shareProduct(): BelongsTo
    {
        return $this->belongsTo(ShareProduct::class);
    }

    public function shareDeposit(): BelongsTo
    {
        return $this->belongsTo(ShareDeposit::class);
    }
}
