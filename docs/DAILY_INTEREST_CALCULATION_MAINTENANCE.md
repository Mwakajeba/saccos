# Daily Interest Calculation System - Maintenance Guide

**Version:** 1.0  
**Last Updated:** February 11, 2026  
**System:** SmartFinance SACCO Management System

---

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Components](#components)
4. [Database Schema](#database-schema)
5. [Performance Optimizations](#performance-optimizations)
6. [Idempotency](#idempotency)
7. [Error Handling](#error-handling)
8. [Maintenance Procedures](#maintenance-procedures)
9. [Troubleshooting](#troubleshooting)
10. [Testing](#testing)

---

## Overview

The Daily Interest Calculation System automatically calculates and accrues daily interest for all active loans. This system runs as a scheduled job and processes loans in batches to ensure optimal performance.

### Key Features

- ✅ **Chunked Processing**: Processes loans in batches of 200
- ✅ **Per-Loan Transactions**: Each loan processed in its own transaction
- ✅ **Idempotent**: Safe to run multiple times without duplicates
- ✅ **Service-Based Architecture**: Clean separation of concerns
- ✅ **Database Indexes**: Optimized for performance
- ✅ **Comprehensive Logging**: Full audit trail

---

## Architecture

### Service-Based Design

```
CalculateDailyInterestJob (Orchestrator)
    ↓
InterestCalculationService (Business Logic)
    ↓
LoanScheduleService (Schedule Updates)
    ↓
AccountingService (GL Transactions)
```

### Flow Diagram

```
1. Job fetches active loans (chunked by 200)
   ↓
2. For each loan (in separate transaction):
   ↓
3. InterestCalculationService:
   - Calculates daily interest
   - Creates/retrieves accrual record
   ↓
4. LoanScheduleService:
   - Updates accrued_interest in schedules
   ↓
5. AccountingService:
   - Creates journal entries
   - Posts GL transactions
   ↓
6. Job logs results and updates JobLog
```

---

## Components

### 1. CalculateDailyInterestJob

**Location:** `app/Jobs/CalculateDailyInterestJob.php`

**Responsibilities:**
- Orchestrates the interest calculation process
- Manages chunking (200 loans per batch)
- Handles per-loan transactions
- Tracks job progress and statistics
- Logs job execution

**Key Methods:**
- `handle()`: Main job execution
- `processLoanInterest()`: Processes a single loan using services

**Configuration:**
- Timeout: 600 seconds (10 minutes)
- Retries: 3 attempts
- Chunk Size: 200 loans

### 2. InterestCalculationService

**Location:** `app/Services/InterestCalculationService.php`

**Responsibilities:**
- Calculates daily interest amount
- Creates/retrieves accrual records (idempotent)
- Validates loan eligibility (principal > 0)

**Key Methods:**
- `calculateAndCreateAccrual(Loan $loan, Carbon $date): ?array`

**Calculation Formula:**
```
Annual Interest Rate (from product or loan) → e.g., 12%
Daily Interest Rate = (Annual Rate / 365) / 100
Daily Interest Amount = Principal Remaining × Daily Interest Rate
```

### 3. LoanScheduleService

**Location:** `app/Services/LoanScheduleService.php`

**Responsibilities:**
- Updates accrued interest in loan schedules
- Finds appropriate schedule (next unpaid or last schedule)

**Key Methods:**
- `updateAccruedInterest(Loan $loan, float $dailyInterestAmount, Carbon $date): void`

### 4. AccountingService

**Location:** `app/Services/AccountingService.php`

**Responsibilities:**
- Creates journal entries for interest accrual
- Posts GL transactions (debit: Interest Receivable, credit: Interest Revenue)

**Key Methods:**
- `postDailyInterestTransactions(Loan $loan, DailyInterestAccrual $accrual, Carbon $date): void`

**GL Accounts Used:**
- Interest Receivable Account (from loan product)
- Interest Revenue Account (from loan product)

---

## Database Schema

### Tables

#### daily_interest_accruals

```sql
- id (primary key)
- loan_id (foreign key, indexed)
- accrual_date (indexed)
- principal_balance
- interest_rate
- daily_interest_amount
- branch_id
- user_id
- created_at
- updated_at

UNIQUE CONSTRAINT: (loan_id, accrual_date)
```

#### loans

```sql
- id (primary key)
- status (indexed) ← Important for job query
- disbursed_on
- ... (other fields)
```

#### loan_schedules

```sql
- id (primary key)
- loan_id (indexed, foreign key)
- due_date
- accrued_interest
- ... (other fields)
```

### Indexes

**Migration:** `2026_02_11_180826_add_performance_indexes_for_interest_calculation.php`

Indexes added:
1. `daily_interest_accruals_loan_id_index` on `daily_interest_accruals.loan_id`
2. `daily_interest_accruals_accrual_date_index` on `daily_interest_accruals.accrual_date`
3. `loans_status_index` on `loans.status`
4. `loan_schedules_loan_id_index` on `loan_schedules.loan_id`

**To apply indexes:**
```bash
php artisan migrate
```

---

## Performance Optimizations

### 1. Chunking

**Implementation:**
```php
Loan::where('status', Loan::STATUS_ACTIVE)
    ->whereNotNull('disbursed_on')
    ->where('disbursed_on', '<', $this->date)
    ->with(['product', 'customer', 'branch', 'repayments'])
    ->chunk(200, function ($loans) {
        // Process chunk
    });
```

**Benefits:**
- Reduces memory usage
- Processes large datasets efficiently
- Allows progress tracking per chunk

### 2. Per-Loan Transactions

**Implementation:**
```php
DB::transaction(function () use ($loan) {
    return $this->processLoanInterest($loan);
});
```

**Benefits:**
- Isolated failures (one loan failure doesn't affect others)
- Shorter transaction times
- Better concurrency

### 3. Database Indexes

All critical query fields are indexed for fast lookups.

### 4. Eager Loading

Relationships are eager loaded to avoid N+1 queries:
```php
->with(['product', 'customer', 'branch', 'repayments'])
```

---

## Idempotency

### What is Idempotency?

Running the job multiple times produces the same result without creating duplicates.

### Implementation

**1. Application Level:**
```php
$accrual = DailyInterestAccrual::firstOrCreate(
    ['loan_id' => $loan->id, 'accrual_date' => $date],
    [/* attributes */]
);
```

**2. Database Level:**
```php
$table->unique(['loan_id', 'accrual_date']);
```

### Why Important?

- Queue worker may retry jobs
- Server restarts
- Timeouts
- Manual re-runs

**Without idempotency → Double interest accrual (CRITICAL BUG)**

---

## Error Handling

### Job-Level Errors

- Catches exceptions at job level
- Updates JobLog with error status
- Logs full error trace
- Continues processing other loans

### Loan-Level Errors

- Each loan processed in separate transaction
- Failed loans logged but don't stop job
- Error details stored in perLoanDetails array

### Service-Level Errors

- Services throw exceptions that bubble up
- Transaction automatically rolls back on error
- Next loan continues processing

---

## Maintenance Procedures

### Running the Job Manually

```bash
# Run for today
php artisan queue:work

# Or dispatch manually
php artisan tinker
>>> \App\Jobs\CalculateDailyInterestJob::dispatch();
>>> \App\Jobs\CalculateDailyInterestJob::dispatch('2026-02-10'); // For specific date
```

### Scheduling the Job

**Location:** `app/Providers/ScheduleServiceProvider.php`

```php
$schedule->job(new \App\Jobs\CalculateDailyInterestJob())
    ->daily()
    ->at('02:00'); // Run at 2 AM daily
```

### Monitoring Job Execution

**Check JobLog table:**
```sql
SELECT * FROM job_logs 
WHERE job_name = 'CalculateDailyInterestJob' 
ORDER BY created_at DESC 
LIMIT 10;
```

**View cached details:**
```php
Cache::get('daily_interest_job_details_{job_log_id}');
```

### Adjusting Chunk Size

**Location:** `app/Jobs/CalculateDailyInterestJob.php`

```php
->chunk(200, function ($loans) { // Change 200 to desired size
```

**Recommendations:**
- Small database (< 1000 loans): 500
- Medium database (1000-10000 loans): 200
- Large database (> 10000 loans): 100

---

## Troubleshooting

### Issue: Job Times Out

**Symptoms:**
- Job fails after 10 minutes
- Large number of loans

**Solutions:**
1. Reduce chunk size (200 → 100)
2. Increase timeout: `public $timeout = 1200;` (20 minutes)
3. Process loans in smaller date ranges

### Issue: Duplicate Accruals

**Symptoms:**
- Same loan has multiple accruals for same date

**Check:**
1. Verify unique constraint exists:
```sql
SHOW INDEXES FROM daily_interest_accruals;
```

2. Check if `firstOrCreate` is being used correctly

**Fix:**
```bash
# Remove duplicates manually if needed
# Then ensure unique constraint is in place
php artisan migrate
```

### Issue: Missing GL Transactions

**Symptoms:**
- Accruals created but no journal entries

**Check:**
1. Verify loan product has GL accounts configured
2. Check AccountingService logs
3. Verify product relationship is loaded

### Issue: Zero Interest Calculated

**Possible Causes:**
1. Principal remaining is 0
2. Interest rate is 0
3. Calculation results in 0 after rounding

**Check Logs:**
```bash
tail -f storage/logs/laravel.log | grep "calculated zero interest"
```

### Issue: Schedule Not Updated

**Symptoms:**
- Accrual created but schedule.accrued_interest not updated

**Check:**
1. Verify loan has schedules
2. Check LoanScheduleService logs
3. Verify loan_id relationship

---

## Testing

### Unit Testing Services

**Example Test Structure:**

```php
// tests/Unit/Services/InterestCalculationServiceTest.php

public function test_calculates_daily_interest_correctly()
{
    $loan = Loan::factory()->create([
        'interest' => 12, // 12% annual
        'amount' => 100000
    ]);
    
    $service = new InterestCalculationService();
    $result = $service->calculateAndCreateAccrual($loan, Carbon::today());
    
    $this->assertNotNull($result);
    $this->assertGreaterThan(0, $result['interest_amount']);
}

public function test_idempotent_behavior()
{
    // Run twice, should return existing record
    $result1 = $service->calculateAndCreateAccrual($loan, $date);
    $result2 = $service->calculateAndCreateAccrual($loan, $date);
    
    $this->assertEquals($result1['accrual']->id, $result2['accrual']->id);
}
```

### Integration Testing

```php
// tests/Feature/CalculateDailyInterestJobTest.php

public function test_job_processes_loans_successfully()
{
    Loan::factory()->count(10)->create([
        'status' => Loan::STATUS_ACTIVE,
        'disbursed_on' => Carbon::yesterday()
    ]);
    
    $job = new CalculateDailyInterestJob();
    $job->handle();
    
    $this->assertDatabaseCount('daily_interest_accruals', 10);
}
```

---

## Code Examples

### Modifying Interest Calculation Logic

**File:** `app/Services/InterestCalculationService.php`

```php
// Current: Annual rate / 365
$dailyInterestRate = ($annualInterestRate / 365) / 100;

// To change to monthly basis:
$dailyInterestRate = ($annualInterestRate / 12 / 30) / 100;
```

### Adding New Validation

**File:** `app/Services/InterestCalculationService.php`

```php
public function calculateAndCreateAccrual(Loan $loan, Carbon $date): ?array
{
    // Add custom validation
    if ($loan->is_on_hold) {
        Log::info("Loan {$loan->loanNo} is on hold. Skipping.");
        return null;
    }
    
    // ... rest of method
}
```

### Modifying GL Posting Logic

**File:** `app/Services/AccountingService.php`

```php
// Add additional GL accounts
if ($product->has_provision_account) {
    // Post to provision account
}
```

---

## Best Practices

### ✅ DO

- Always test changes in development first
- Monitor job logs after deployment
- Keep chunk size reasonable (100-500)
- Use transactions for data integrity
- Log important operations
- Handle exceptions gracefully

### ❌ DON'T

- Don't remove idempotency checks
- Don't process all loans in one transaction
- Don't skip error handling
- Don't modify calculation logic without testing
- Don't remove database indexes
- Don't run job manually during business hours (if possible)

---

## Support Contacts

**For Issues:**
- Check logs: `storage/logs/laravel.log`
- Check JobLog table for job execution history
- Review cached job details for per-loan information

**Emergency Procedures:**
1. Stop queue worker: `php artisan queue:restart`
2. Check for duplicate accruals
3. Review recent job logs
4. Contact system administrator

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-02-11 | Initial service-based architecture implementation |

---

**Document End**
