<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DividendPayment extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'payment_number',
        'dividend_id',
        'share_account_id',
        'customer_id',
        'member_shares',
        'dividend_amount',
        'payment_method',
        'savings_account_id',
        'share_product_id',
        'shares_converted',
        'bank_account_id',
        'payment_date',
        'status',
        'notes',
        'branch_id',
        'company_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'member_shares' => 'decimal:2',
        'dividend_amount' => 'decimal:2',
        'shares_converted' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Get the dividend
     */
    public function dividend(): BelongsTo
    {
        return $this->belongsTo(Dividend::class);
    }

    /**
     * Get the share account
     */
    public function shareAccount(): BelongsTo
    {
        return $this->belongsTo(ShareAccount::class);
    }

    /**
     * Get the customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the savings account (if payment method is savings_deposit)
     */
    public function savingsAccount(): BelongsTo
    {
        return $this->belongsTo(ShareAccount::class, 'savings_account_id');
    }

    /**
     * Get the share product (if converting to shares)
     */
    public function shareProduct(): BelongsTo
    {
        return $this->belongsTo(ShareProduct::class);
    }

    /**
     * Get the bank account (for cash payment)
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
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
}
