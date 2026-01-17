<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestResult extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'lab_test_id',
        'customer_id',
        'results',
        'findings',
        'interpretation',
        'recommendations',
        'result_file',
        'status',
        'submitted_by',
        'submitted_at',
        'sent_to_doctor_by',
        'sent_to_doctor_at',
        'viewed_by_doctor',
        'viewed_at',
        'branch_id',
        'company_id',
    ];

    protected $casts = [
        'results' => 'array', // If storing JSON
        'submitted_at' => 'datetime',
        'sent_to_doctor_at' => 'datetime',
        'viewed_at' => 'datetime',
    ];

    // Relationships
    public function labTest()
    {
        return $this->belongsTo(LabTest::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function sentToDoctorBy()
    {
        return $this->belongsTo(User::class, 'sent_to_doctor_by');
    }

    public function viewedByDoctor()
    {
        return $this->belongsTo(User::class, 'viewed_by_doctor');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeSentToDoctor($query)
    {
        return $query->where('status', 'sent_to_doctor');
    }

    public function scopeViewedByDoctor($query)
    {
        return $query->where('status', 'viewed_by_doctor');
    }

    // Methods
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isSubmitted()
    {
        return $this->status === 'submitted';
    }

    public function isSentToDoctor()
    {
        return $this->status === 'sent_to_doctor';
    }

    public function isViewedByDoctor()
    {
        return $this->status === 'viewed_by_doctor';
    }
}
