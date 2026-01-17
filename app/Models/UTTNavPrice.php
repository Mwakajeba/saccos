<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UTTNavPrice extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'utt_nav_prices';

    protected $fillable = [
        'utt_fund_id',
        'nav_date',
        'nav_per_unit',
        'notes',
        'entered_by',
        'company_id',
    ];

    protected $casts = [
        'nav_date' => 'date',
        'nav_per_unit' => 'decimal:4',
    ];

    // Relationships
    public function uttFund()
    {
        return $this->belongsTo(UTTFund::class, 'utt_fund_id');
    }

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Scopes
    public function scopeByFund($query, $fundId)
    {
        return $query->where('utt_fund_id', $fundId);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('nav_date', 'desc');
    }

    public function scopeOnOrBefore($query, $date)
    {
        return $query->where('nav_date', '<=', $date);
    }
}
