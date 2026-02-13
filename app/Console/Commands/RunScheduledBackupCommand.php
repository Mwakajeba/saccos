<?php

namespace App\Console\Commands;

use App\Jobs\BackupJob;
use App\Models\Company;
use App\Models\User;
use App\Services\BackupService;
use App\Services\SystemSettingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RunScheduledBackupCommand extends Command
{
    const CACHE_KEY_LAST_RUN = 'scheduled_backup_last_run';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run automatic backups for all companies based on System Configuration (backup_enabled, backup_frequency, backup_include_files, backup_retention_days)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $config = SystemSettingService::getBackupConfig();

        if (empty($config['enabled'])) {
            $this->info('Automatic backups are disabled in System Configuration.');
            return 0;
        }

        $frequency = $config['frequency'] ?? 'daily';
        if (!$this->shouldRunNow($frequency)) {
            $this->info("Skipping: next run per frequency '{$frequency}' not due yet.");
            return 0;
        }

        $type = !empty($config['include_files']) ? 'full' : 'database';
        $retentionDays = (int) ($config['retention_days'] ?? 30);
        if ($retentionDays < 1) {
            $retentionDays = 30;
        }

        $companies = Company::query()->get();
        if ($companies->isEmpty()) {
            $this->warn('No companies found.');
            return 0;
        }

        $dispatched = 0;
        foreach ($companies as $company) {
            $user = User::where('company_id', $company->id)->first();
            $userId = $user ? $user->id : 1;

            BackupJob::dispatch(
                $type,
                $userId,
                $company->id,
                'Scheduled backup (System Configuration)',
                null
            );
            $dispatched++;
            $this->line("Queued {$type} backup for company: {$company->name} (ID: {$company->id})");
        }

        $this->info("Dispatched {$dispatched} backup job(s). They will run via the queue worker.");

        // Clean old backups per company using retention days from config
        $backupService = new BackupService();
        foreach ($companies as $company) {
            config(['app.current_company_id' => $company->id]);
            try {
                $deleted = $backupService->cleanOldBackups($retentionDays);
                if ($deleted > 0) {
                    $this->line("Cleaned {$deleted} old backup(s) for company: {$company->name}");
                }
            } catch (\Throwable $e) {
                $this->warn("Clean old backups failed for company {$company->name}: " . $e->getMessage());
            }
        }

        $this->markRunNow();
        return 0;
    }

    /**
     * Whether a run is due based on frequency and last run time.
     */
    protected function shouldRunNow(string $frequency): bool
    {
        $lastRun = Cache::get(self::CACHE_KEY_LAST_RUN);
        $now = now();

        if ($lastRun === null) {
            return true;
        }

        switch ($frequency) {
            case 'hourly':
                return $now->diffInMinutes($lastRun) >= 55;
            case 'daily':
                return $now->toDateString() !== $lastRun->toDateString();
            case 'weekly':
                return $now->diffInWeeks($lastRun) >= 1;
            case 'monthly':
                return $now->format('Y-m') !== $lastRun->format('Y-m');
            default:
                return $now->toDateString() !== $lastRun->toDateString();
        }
    }

    protected function markRunNow(): void
    {
        Cache::put(self::CACHE_KEY_LAST_RUN, now(), now()->addDays(35));
    }
}
