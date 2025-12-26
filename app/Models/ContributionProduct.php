<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributionProduct extends Model
{
    protected $fillable = [
        'product_name',
        'interest',
        'category',
        'auto_create',
        'compound_period',
        'interest_posting_period',
        'interest_calculation_type',
        'lockin_period_frequency',
        'lockin_period_frequency_type',
        'automatic_opening_balance',
        'minimum_balance_for_interest_calculations',
        'description',
        'can_withdraw',
        'has_charge',
        'charge_id',
        'charge_type',
        'charge_amount',
        'bank_account_id',
        'journal_reference_id',
        'riba_journal_id',
        'pay_loan_journal_id',
        'liability_account_id',
        'expense_account_id',
        'riba_payable_account_id',
        'withholding_account_id',
        'withholding_percentage',
        'riba_payable_journal_id',
        'company_id',
        'branch_id',
        'is_active',
    ];

    protected $casts = [
        'interest' => 'decimal:2',
        'automatic_opening_balance' => 'decimal:2',
        'minimum_balance_for_interest_calculations' => 'decimal:2',
        'charge_amount' => 'decimal:2',
        'withholding_percentage' => 'decimal:2',
        'can_withdraw' => 'boolean',
        'has_charge' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'bank_account_id');
    }

    public function journalReference(): BelongsTo
    {
        return $this->belongsTo(JournalReference::class, 'journal_reference_id');
    }

    public function ribaJournal(): BelongsTo
    {
        return $this->belongsTo(JournalReference::class, 'riba_journal_id');
    }

    public function payLoanJournal(): BelongsTo
    {
        return $this->belongsTo(JournalReference::class, 'pay_loan_journal_id');
    }

    public function liabilityAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'liability_account_id');
    }

    public function expenseAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'expense_account_id');
    }

    public function ribaPayableAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'riba_payable_account_id');
    }

    public function withholdingAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'withholding_account_id');
    }

    public function ribaPayableJournal(): BelongsTo
    {
        return $this->belongsTo(JournalReference::class, 'riba_payable_journal_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
