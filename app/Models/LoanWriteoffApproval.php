<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanWriteoffApproval extends Model
{
    protected $table = 'loan_writeoff_approvals';

    protected $fillable = [
        'loan_writeoff_id',
        'approval_level',
        'approver_type',
        'approver_id',
        'approver_name',
        'status',
        'notes',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function loanWriteoff(): BelongsTo
    {
        return $this->belongsTo(LoanWriteoff::class, 'loan_writeoff_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
