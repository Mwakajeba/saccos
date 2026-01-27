<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterestSavingSummary extends Model
{
    protected $table = 'interest_saving_summary';

    protected $fillable = [
        'calculation_date',
        'day_of_calculation',
        'total_accounts',
        'total_customers',
        'total_interest_amount',
        'total_withholding_amount',
        'total_net_amount',
        'total_balance',
        'processed_count',
        'skipped_count',
        'error_count',
        'branch_id',
        'company_id',
    ];

    protected $casts = [
        'calculation_date' => 'date',
        'total_accounts' => 'integer',
        'total_customers' => 'integer',
        'total_interest_amount' => 'decimal:2',
        'total_withholding_amount' => 'decimal:2',
        'total_net_amount' => 'decimal:2',
        'total_balance' => 'decimal:2',
        'processed_count' => 'integer',
        'skipped_count' => 'integer',
        'error_count' => 'integer',
    ];

    // Relationships
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
