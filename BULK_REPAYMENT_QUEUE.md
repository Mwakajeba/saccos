# Bulk Repayment Import - Queue Management

## Queue Configuration

The bulk repayment import uses Laravel's queue system for large datasets (>50 rows).

### Queue Connection
- **Driver**: Database (configured in `.env` as `QUEUE_CONNECTION=database`)
- **Tables**: `jobs` (pending), `failed_jobs` (failed)

## Starting the Queue Worker

### Option 1: Using the provided script
```bash
./start-queue.sh
```

### Option 2: Manual command
```bash
php artisan queue:work --queue=default --tries=3 --timeout=300
```

### Option 3: Background process (Linux/Mac)
```bash
nohup php artisan queue:work --queue=default --tries=3 --timeout=300 > storage/logs/queue.log 2>&1 &
```

## Monitoring

### Check queue status
```bash
php artisan queue:monitor default
```

### Check pending jobs
```bash
php artisan tinker
>>> DB::table('jobs')->count()
```

### Check failed jobs
```bash
php artisan queue:failed
```

### View logs
```bash
tail -f storage/logs/laravel.log
```

## Troubleshooting

### Issue: Jobs queued but not processing
**Solution**: Make sure queue worker is running
```bash
ps aux | grep "queue:work"
```

If not running, start it:
```bash
php artisan queue:work --daemon
```

### Issue: Jobs failing silently
**Solution**: Check failed jobs table
```bash
php artisan queue:failed
```

Retry failed jobs:
```bash
php artisan queue:retry all
```

### Issue: No data imported but shows success
**Possible causes**:
1. Queue worker not running
2. Model relationships not loading
3. GL account IDs missing in loan products

**Debug steps**:
```bash
# Check logs
tail -100 storage/logs/laravel.log | grep "BulkRepayment"

# Check if loan products have GL accounts
php artisan tinker
>>> App\Models\LoanProduct::whereNull('principal_receivables_account_id')->count()
>>> App\Models\LoanProduct::whereNull('interest_receivables_account_id')->count()
```

## Log Messages

The import logs the following events:
- Job creation
- Job start
- Each row processing
- GL transaction creation
- Job completion
- Errors

Search logs with:
```bash
grep "BulkRepayment" storage/logs/laravel.log
```

## Performance

- **Small datasets (<50 rows)**: Processed immediately (synchronous)
- **Large datasets (>50 rows)**: Queued for background processing
- **Timeout**: 5 minutes per job
- **Retries**: 3 attempts on failure
