<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributionAccount extends Model
{
    protected $fillable = [
        'customer_id',
        'contribution_product_id',
        'account_number',
        'opening_date',
        'notes',
        'balance',
        'status',
        'branch_id',
        'company_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'opening_date' => 'date',
        'balance' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function contributionProduct(): BelongsTo
    {
        return $this->belongsTo(ContributionProduct::class);
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
