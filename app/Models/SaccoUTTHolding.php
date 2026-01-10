<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaccoUTTHolding extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'utt_fund_id',
        'company_id',
        'branch_id',
        'total_units',
        'average_acquisition_cost',
        'last_reconciliation_date',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'total_units' => 'decimal:4',
        'average_acquisition_cost' => 'decimal:4',
        'last_reconciliation_date' => 'date',
    ];

    // Relationships
    public function uttFund()
    {
        return $this->belongsTo(UTTFund::class, 'utt_fund_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function transactions()
    {
        return $this->hasMany(UTTTransaction::class, 'sacco_utt_holding_id');
    }

    public function reconciliations()
    {
        return $this->hasMany(UTTReconciliation::class, 'sacco_utt_holding_id');
    }

    // Helper methods
    public function getCurrentValue()
    {
        $latestNav = $this->uttFund->navPrices()
            ->orderBy('nav_date', 'desc')
            ->first();

        if (!$latestNav) {
            return 0;
        }

        return $this->total_units * $latestNav->nav_per_unit;
    }

    public function getUnrealizedGain()
    {
        $currentValue = $this->getCurrentValue();
        $costBasis = $this->total_units * $this->average_acquisition_cost;
        return $currentValue - $costBasis;
    }
}
