<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'chart_account_id',
        'amount',
        'description',
        'base_amount',
        'net_payable',
        'total_cost',
        'vat_mode',
        'vat_amount',
        'wht_treatment',
        'wht_rate',
        'wht_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'base_amount' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'wht_rate' => 'decimal:2',
        'wht_amount' => 'decimal:2',
    ];

    /**
     * Get the payment that owns the item.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the chart account for this line item.
     */
    public function chartAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class);
    }
}
