<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vinkla\Hashids\Facades\Hashids;

class JournalReference extends Model
{
    protected $fillable = [
        'name',
        'reference',
        'company_id',
        'branch_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the hash ID for the model.
     */
    public function getHashIdAttribute()
    {
        return Hashids::encode($this->id);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'hash_id';
    }

    /**
     * Resolve the model binding using hash ID.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $decoded = Hashids::decode($value);
        if (!empty($decoded)) {
            return static::where('id', $decoded[0])->first();
        }
        return null;
    }

    /**
     * Get the route key value.
     */
    public function getRouteKey()
    {
        return $this->hash_id;
    }
}
