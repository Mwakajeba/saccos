<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complain extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'customer_id',
        'complain_category_id',
        'description',
        'status',
        'response',
        'responded_by',
        'responded_at',
        'branch_id',
        'company_id',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    /**
     * Get the customer that made the complaint
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the complain category
     */
    public function category()
    {
        return $this->belongsTo(ComplainCategory::class, 'complain_category_id');
    }

    /**
     * Get the user who responded
     */
    public function respondedBy()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    /**
     * Get the branch
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'in_progress' => 'info',
            'resolved' => 'success',
            'closed' => 'secondary',
            default => 'secondary',
        };
    }
}
