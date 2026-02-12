<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArrearsClassification extends Model
{
    protected $fillable = [
        'company_id',
        'days_from',
        'days_to',
        'bucket_label',
        'status',
        'provision_percentage',
        'comments',
        'is_active',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'days_from' => 'integer',
        'days_to' => 'integer',
        'provision_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the classification for a given number of days in arrears
     */
    public static function getClassificationForDays(int $daysInArrears, int $companyId): ?self
    {
        return self::where('company_id', $companyId)
            ->where('is_active', true)
            ->where('days_from', '<=', $daysInArrears)
            ->where(function ($query) use ($daysInArrears) {
                $query->where('days_to', '>=', $daysInArrears)
                    ->orWhereNull('days_to');
            })
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * Get provision percentage for a given number of days in arrears
     */
    public static function getProvisionPercentage(int $daysInArrears, int $companyId): float
    {
        $classification = self::getClassificationForDays($daysInArrears, $companyId);
        return $classification ? (float) $classification->provision_percentage : 0;
    }
}
