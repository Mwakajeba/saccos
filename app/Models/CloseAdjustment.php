<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CloseAdjustment extends Model
{
    use HasFactory;

    protected $primaryKey = 'adjustment_id';
    protected $table = 'close_adjustments';

    protected $fillable = [
        'close_id',
        'account_id',
        'entry_type',
        'amount',
        'description',
        'created_by',
        'posted_journal_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function closeBatch(): BelongsTo
    {
        return $this->belongsTo(CloseBatch::class, 'close_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedJournal(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'posted_journal_id');
    }
}
