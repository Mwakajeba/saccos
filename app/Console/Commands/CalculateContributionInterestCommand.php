<?php

namespace App\Console\Commands;

use App\Jobs\CalculateContributionInterestJob;
use Illuminate\Console\Command;

class CalculateContributionInterestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contributions:calculate-interest {--sync : Run synchronously instead of queuing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate interest on saving for all contribution accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting contribution interest calculation...');

        try {
            if ($this->option('sync')) {
                // Run synchronously for immediate testing
                $this->info('Running synchronously (not queued)...');
                $job = new CalculateContributionInterestJob();
                $job->handle();
                $this->info('Interest calculation completed successfully.');
            } else {
                // Dispatch to queue
                CalculateContributionInterestJob::dispatch();
                $this->info('Interest calculation job has been dispatched to the queue.');
                $this->info('Make sure your queue worker is running: php artisan queue:work');
            }

            $this->info('Check the logs for detailed information:');
            $this->line('  - storage/logs/laravel.log');
            $this->line('  - storage/logs/contribution-interest-calculation.log');

            return 0;
        } catch (\Exception $e) {
            $this->error('Error calculating contribution interest: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}
