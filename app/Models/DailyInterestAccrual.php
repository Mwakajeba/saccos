<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyInterestAccrual extends Model
{
    use HasFactory;

    protected $table = 'daily_interest_accruals';

    protected $fillable = [
        'loan_id',
        'accrual_date',
        'principal_balance',
        'interest_rate',
        'daily_interest_amount',
        'branch_id',
        'user_id',
    ];

    protected $casts = [
        'accrual_date' => 'date',
        'principal_balance' => 'decimal:2',
        'interest_rate' => 'decimal:8',
        'daily_interest_amount' => 'decimal:2',
    ];

    /**
     * Get the loan associated with the accrual
     */
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Get the branch associated with the accrual
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who created the accrual
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
