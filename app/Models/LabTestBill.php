<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestBill extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'bill_number',
        'lab_test_id',
        'customer_id',
        'amount',
        'paid_amount',
        'bill_date',
        'due_date',
        'payment_status',
        'paid_by',
        'paid_at',
        'payment_notes',
        'branch_id',
        'company_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'bill_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function labTest()
    {
        return $this->belongsTo(LabTest::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePartial($query)
    {
        return $query->where('payment_status', 'partial');
    }

    // Accessors
    public function getBalanceAttribute()
    {
        return $this->amount - $this->paid_amount;
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedPaidAmountAttribute()
    {
        return number_format($this->paid_amount, 2);
    }

    public function getFormattedBalanceAttribute()
    {
        return number_format($this->balance, 2);
    }

    public function isPaid()
    {
        return $this->payment_status === 'paid' && $this->paid_amount >= $this->amount;
    }

    public function isPending()
    {
        return $this->payment_status === 'pending';
    }
}
