<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NextOfKin extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'name',
        'relationship',
        'phone',
        'email',
        'address',
        'id_type',
        'id_number',
        'date_of_birth',
        'gender',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Get the customer that owns the next of kin.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
