<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\Models\Fee;
use App\Models\JournalReference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareProduct extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'share_name',
        'required_share',
        'nominal_price',
        'minimum_purchase_amount',
        'maximum_purchase_amount',
        'maximum_shares_per_member',
        'minimum_shares_for_membership',
        'share_purchase_increment',
        'minimum_active_period',
        'minimum_active_period_type',
        'allow_dividends_for_inactive_member',
        'dividend_rate',
        'dividend_calculation_method',
        'dividend_payment_frequency',
        'dividend_payment_month',
        'dividend_payment_day',
        'minimum_balance_for_dividend',
        'lockin_period_frequency',
        'lockin_period_frequency_type',
        'description',
        'certificate_number_prefix',
        'certificate_number_format',
        'auto_generate_certificate',
        'opening_date',
        'closing_date',
        'allow_new_subscriptions',
        'allow_additional_purchases',
        'maximum_total_shares',
        'allow_share_transfers',
        'transfer_fee',
        'transfer_fee_type',
        'allow_share_withdrawals',
        'withdrawal_fee',
        'withdrawal_fee_type',
        'withdrawal_notice_period',
        'withdrawal_notice_period_type',
        'minimum_withdrawal_amount',
        'maximum_withdrawal_amount',
        'allow_partial_withdrawal',
        'has_charges',
        'charge_id',
        'charge_type',
        'charge_amount',
        'journal_reference_id',
        'hrms_code',
        'liability_account_id',
        'income_account_id',
        'share_capital_account_id',
        'is_active',
    ];

    protected $casts = [
        'required_share' => 'decimal:2',
        'nominal_price' => 'decimal:2',
        'minimum_purchase_amount' => 'decimal:2',
        'maximum_purchase_amount' => 'decimal:2',
        'maximum_shares_per_member' => 'decimal:2',
        'minimum_shares_for_membership' => 'decimal:2',
        'share_purchase_increment' => 'decimal:2',
        'dividend_rate' => 'decimal:4',
        'minimum_balance_for_dividend' => 'decimal:2',
        'maximum_total_shares' => 'decimal:2',
        'transfer_fee' => 'decimal:2',
        'withdrawal_fee' => 'decimal:2',
        'minimum_withdrawal_amount' => 'decimal:2',
        'maximum_withdrawal_amount' => 'decimal:2',
        'charge_amount' => 'decimal:2',
        'allow_dividends_for_inactive_member' => 'boolean',
        'auto_generate_certificate' => 'boolean',
        'allow_new_subscriptions' => 'boolean',
        'allow_additional_purchases' => 'boolean',
        'allow_share_transfers' => 'boolean',
        'allow_share_withdrawals' => 'boolean',
        'allow_partial_withdrawal' => 'boolean',
        'has_charges' => 'boolean',
        'is_active' => 'boolean',
        'opening_date' => 'date',
        'closing_date' => 'date',
    ];

    /**
     * Get the journal reference (for share transfer)
     */
    public function journalReference(): BelongsTo
    {
        return $this->belongsTo(JournalReference::class, 'journal_reference_id');
    }

    /**
     * Get the liability account
     */
    public function liabilityAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'liability_account_id');
    }

    /**
     * Get the income account
     */
    public function incomeAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'income_account_id');
    }

    /**
     * Get the charge/fee
     */
    public function charge(): BelongsTo
    {
        return $this->belongsTo(Fee::class, 'charge_id');
    }

    /**
     * Get the share capital account
     */
    public function shareCapitalAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'share_capital_account_id');
    }
}
