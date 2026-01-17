<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTest extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'test_number',
        'consultation_id',
        'customer_id',
        'doctor_id',
        'test_name',
        'test_description',
        'clinical_notes',
        'instructions',
        'status',
        'reviewed_by',
        'reviewed_at',
        'test_taken_by',
        'test_taken_at',
        'results_submitted_by',
        'results_submitted_at',
        'branch_id',
        'company_id',
        'created_by',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'test_taken_at' => 'datetime',
        'results_submitted_at' => 'datetime',
    ];

    // Relationships
    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function testTakenBy()
    {
        return $this->belongsTo(User::class, 'test_taken_by');
    }

    public function resultsSubmittedBy()
    {
        return $this->belongsTo(User::class, 'results_submitted_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bill()
    {
        return $this->hasOne(LabTestBill::class);
    }

    public function result()
    {
        return $this->hasOne(LabTestResult::class);
    }

    // Scopes
    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    public function scopePendingPayment($query)
    {
        return $query->where('status', 'pending_payment');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeTestTaken($query)
    {
        return $query->where('status', 'test_taken');
    }

    public function scopeResultsSubmitted($query)
    {
        return $query->where('status', 'results_submitted');
    }

    // Methods
    public function canBeReviewed()
    {
        return $this->status === 'pending_review';
    }

    public function canCreateBill()
    {
        return in_array($this->status, ['pending_review', 'bill_created']);
    }

    public function canTakeTest()
    {
        return $this->status === 'paid';
    }

    public function canSubmitResults()
    {
        return $this->status === 'test_taken';
    }

    public function canSendToDoctor()
    {
        return $this->status === 'results_submitted';
    }
}
