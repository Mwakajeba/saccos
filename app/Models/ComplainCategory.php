<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplainCategory extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['name', 'description', 'priority'];

    protected $casts = [
        'priority' => 'string',
    ];

    /**
     * Get all complains for this category
     */
    public function complains()
    {
        return $this->hasMany(Complain::class, 'complain_category_id');
    }

    /**
     * Get priority badge color
     */
    public function getPriorityBadgeAttribute()
    {
        return match($this->priority) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            default => 'secondary',
        };
    }
}
