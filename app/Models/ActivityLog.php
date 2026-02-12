<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'model',
        'action',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'device',
        'activity_time',
    ];

    protected $casts = [
        // Store full date & time (including seconds) for activity timestamp
        'activity_time' => 'datetime',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a human-readable summary of what changed (for update actions).
     * Returns e.g. "name: John → Jane; status: pending → active"
     */
    public function getChangesSummaryAttribute(): ?string
    {
        if ($this->action !== 'update' || empty($this->old_values) || empty($this->new_values)) {
            return null;
        }
        $parts = [];
        foreach ($this->old_values as $key => $oldVal) {
            $newVal = $this->new_values[$key] ?? null;
            $oldStr = $this->formatValueForDisplay($oldVal);
            $newStr = $this->formatValueForDisplay($newVal);
            $label = str_replace('_', ' ', ucfirst($key));
            $parts[] = "{$label}: {$oldStr} → {$newStr}";
        }
        return implode('; ', $parts);
    }

    protected function formatValueForDisplay($value): string
    {
        if ($value === null) {
            return '—';
        }
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        if (is_object($value) && method_exists($value, 'format')) {
            return $value->format('Y-m-d H:i');
        }
        if (is_array($value)) {
            return json_encode($value);
        }
        return (string) $value;
    }
}
