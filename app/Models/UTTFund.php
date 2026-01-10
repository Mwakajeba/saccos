<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UTTFund extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'fund_name',
        'fund_code',
        'currency',
        'investment_horizon',
        'expense_ratio',
        'status',
        'company_id',
        'branch_id',
        'created_by',
        'updated_by',
        'notes',
    ];

    protected $casts = [
        'expense_ratio' => 'decimal:4',
    ];

    // Relationships
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

    public function holdings()
    {
        return $this->hasMany(SaccoUTTHolding::class, 'utt_fund_id');
    }

    public function transactions()
    {
        return $this->hasMany(UTTTransaction::class, 'utt_fund_id');
    }

    public function navPrices()
    {
        return $this->hasMany(UTTNavPrice::class, 'utt_fund_id');
    }

    public function cashFlows()
    {
        return $this->hasMany(UTTCashFlow::class, 'utt_fund_id');
    }

    public function reconciliations()
    {
        return $this->hasMany(UTTReconciliation::class, 'utt_fund_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
