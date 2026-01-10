<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UTTCashFlow extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'utt_fund_id',
        'utt_transaction_id',
        'cash_flow_type',
        'transaction_date',
        'amount',
        'flow_direction',
        'reference_number',
        'description',
        'classification',
        'bank_account_id',
        'company_id',
        'branch_id',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function uttFund()
    {
        return $this->belongsTo(UTTFund::class, 'utt_fund_id');
    }

    public function uttTransaction()
    {
        return $this->belongsTo(UTTTransaction::class, 'utt_transaction_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
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

    // Scopes
    public function scopeInflow($query)
    {
        return $query->where('flow_direction', 'IN');
    }

    public function scopeOutflow($query)
    {
        return $query->where('flow_direction', 'OUT');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('cash_flow_type', $type);
    }

    public function scopeCapital($query)
    {
        return $query->where('classification', 'Capital');
    }

    public function scopeIncome($query)
    {
        return $query->where('classification', 'Income');
    }
}
