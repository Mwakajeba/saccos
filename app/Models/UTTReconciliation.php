<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UTTReconciliation extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'utt_fund_id',
        'sacco_utt_holding_id',
        'reconciliation_date',
        'statement_units',
        'system_units',
        'variance',
        'status',
        'variance_notes',
        'reconciliation_notes',
        'reconciled_by',
        'reconciled_at',
        'approved_by',
        'approved_at',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'reconciliation_date' => 'date',
        'statement_units' => 'decimal:4',
        'system_units' => 'decimal:4',
        'variance' => 'decimal:4',
        'reconciled_at' => 'datetime',
        'approved_at' => 'datetime',
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

    public function reconciledBy()
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'Completed');
    }

    public function scopeWithVariance($query)
    {
        return $query->where('status', 'Variance Identified');
    }

    // Helper methods
    public function hasVariance()
    {
        return abs($this->variance) > 0.0001; // Account for floating point precision
    }

    public function calculateVariance()
    {
        $this->variance = $this->statement_units - $this->system_units;
        return $this->variance;
    }
}
