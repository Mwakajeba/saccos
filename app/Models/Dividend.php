<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dividend extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'dividend_number',
        'profit_allocation_id',
        'share_product_id',
        'declaration_date',
        'payment_date',
        'financial_year',
        'total_dividend_amount',
        'dividend_rate',
        'calculation_method',
        'total_shares',
        'dividend_per_share',
        'description',
        'status',
        'branch_id',
        'company_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'declaration_date' => 'date',
        'payment_date' => 'date',
        'total_dividend_amount' => 'decimal:2',
        'dividend_rate' => 'decimal:4',
        'total_shares' => 'decimal:2',
        'dividend_per_share' => 'decimal:4',
    ];

    /**
     * Get the profit allocation
     */
    public function profitAllocation(): BelongsTo
    {
        return $this->belongsTo(ProfitAllocation::class);
    }

    /**
     * Get the share product
     */
    public function shareProduct(): BelongsTo
    {
        return $this->belongsTo(ShareProduct::class);
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
     * Get the user who created this
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated this
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all dividend payments
     */
    public function dividendPayments(): HasMany
    {
        return $this->hasMany(DividendPayment::class);
    }
}
