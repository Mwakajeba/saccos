<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfitAllocation extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'reference_number',
        'allocation_date',
        'financial_year',
        'total_profit',
        'statutory_reserve_percentage',
        'statutory_reserve_amount',
        'education_fund_percentage',
        'education_fund_amount',
        'community_fund_percentage',
        'community_fund_amount',
        'dividend_percentage',
        'dividend_amount',
        'other_allocation_percentage',
        'other_allocation_amount',
        'other_allocation_description',
        'other_allocation_account_id',
        'statutory_reserve_account_id',
        'education_fund_account_id',
        'community_fund_account_id',
        'dividend_payable_account_id',
        'notes',
        'status',
        'branch_id',
        'company_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'allocation_date' => 'date',
        'total_profit' => 'decimal:2',
        'statutory_reserve_percentage' => 'decimal:2',
        'statutory_reserve_amount' => 'decimal:2',
        'education_fund_percentage' => 'decimal:2',
        'education_fund_amount' => 'decimal:2',
        'community_fund_percentage' => 'decimal:2',
        'community_fund_amount' => 'decimal:2',
        'dividend_percentage' => 'decimal:2',
        'dividend_amount' => 'decimal:2',
        'other_allocation_percentage' => 'decimal:2',
        'other_allocation_amount' => 'decimal:2',
    ];

    /**
     * Get the statutory reserve account
     */
    public function statutoryReserveAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'statutory_reserve_account_id');
    }

    /**
     * Get the education fund account
     */
    public function educationFundAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'education_fund_account_id');
    }

    /**
     * Get the community fund account
     */
    public function communityFundAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'community_fund_account_id');
    }

    /**
     * Get the dividend payable account
     */
    public function dividendPayableAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'dividend_payable_account_id');
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
     * Get all dividends for this profit allocation
     */
    public function dividends()
    {
        return $this->hasMany(Dividend::class);
    }
}
