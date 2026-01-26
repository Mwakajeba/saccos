<?php

namespace App\Models\Hr;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiometricLog extends Model
{
    use LogsActivity;

    protected $table = 'hr_biometric_logs';

    protected $fillable = [
        'device_id',
        'device_user_id',
        'employee_id',
        'punch_time',
        'punch_type',
        'punch_mode',
        'status',
        'attendance_id',
        'raw_data',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'punch_time' => 'datetime',
        'raw_data' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSED = 'processed';
    const STATUS_FAILED = 'failed';
    const STATUS_DUPLICATE = 'duplicate';

    /**
     * Punch type constants
     */
    const PUNCH_CHECK_IN = 'check_in';
    const PUNCH_CHECK_OUT = 'check_out';
    const PUNCH_BREAK_IN = 'break_in';
    const PUNCH_BREAK_OUT = 'break_out';

    /**
     * Relationships
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(BiometricDevice::class, 'device_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', self::STATUS_PROCESSED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('punch_time', [$startDate, $endDate]);
    }

    /**
     * Mark as processed
     */
    public function markAsProcessed($attendanceId = null)
    {
        $this->update([
            'status' => self::STATUS_PROCESSED,
            'attendance_id' => $attendanceId,
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark as duplicate
     */
    public function markAsDuplicate()
    {
        $this->update([
            'status' => self::STATUS_DUPLICATE,
            'processed_at' => now(),
        ]);
    }
}

