<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UTTTransaction extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'utt_fund_id',
        'sacco_utt_holding_id',
        'transaction_type',
        'trade_date',
        'nav_date',
        'settlement_date',
        'units',
        'nav_per_unit',
        'total_cash_value',
        'status',
        'reference_number',
        'description',
        'rejection_reason',
        'maker_id',
        'checker_id',
        'approved_at',
        'settled_at',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'trade_date' => 'date',
        'nav_date' => 'date',
        'settlement_date' => 'date',
        'units' => 'decimal:4',
        'nav_per_unit' => 'decimal:4',
        'total_cash_value' => 'decimal:2',
        'approved_at' => 'datetime',
        'settled_at' => 'datetime',
    ];

    // Relationships
    public function uttFund()
    {
        return $this->belongsTo(UTTFund::class, 'utt_fund_id');
    }

    public function saccoUTTHolding()
    {
        return $this->belongsTo(SaccoUTTHolding::class, 'sacco_utt_holding_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function maker()
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checker_id');
    }

    public function cashFlows()
    {
        return $this->hasMany(UTTCashFlow::class, 'utt_transaction_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    public function scopeSettled($query)
    {
        return $query->where('status', 'SETTLED');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    // Helper methods
    public function canBeApproved()
    {
        return $this->status === 'PENDING';
    }

    public function canBeSettled()
    {
        return $this->status === 'APPROVED';
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['PENDING', 'APPROVED']);
    }
}
