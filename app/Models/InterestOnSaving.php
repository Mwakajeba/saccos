<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterestOnSaving extends Model
{
    protected $table = 'interest_on_saving';

    protected $fillable = [
        'contribution_account_id',
        'contribution_product_id',
        'customer_id',
        'calculation_date',
        'date_of_calculation',
        'interest_rate',
        'interest_amount_gained',
        'account_balance_at_interest_calculation',
        'withholding_percentage',
        'withholding_amount',
        'net_amount',
        'description',
        'posted',
        'reason',
        'branch_id',
        'company_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'calculation_date' => 'date',
        'date_of_calculation' => 'date',
        'interest_rate' => 'decimal:2',
        'interest_amount_gained' => 'decimal:2',
        'account_balance_at_interest_calculation' => 'decimal:2',
        'withholding_percentage' => 'decimal:2',
        'withholding_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'posted' => 'boolean',
    ];

    // Relationships
    public function contributionAccount(): BelongsTo
    {
        return $this->belongsTo(ContributionAccount::class);
    }

    public function contributionProduct(): BelongsTo
    {
        return $this->belongsTo(ContributionProduct::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
