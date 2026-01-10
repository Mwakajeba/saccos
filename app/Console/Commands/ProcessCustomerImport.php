<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ProcessCustomerImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:import {--queue=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process customer import jobs from the queue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queue = $this->option('queue');
        
        $this->info('Starting customer import queue worker...');
        $this->info('Press Ctrl+C to stop the worker.');
        $this->newLine();

        // Use passthru to run queue:work directly (better for interactive commands)
        passthru("php artisan queue:work --queue={$queue} --tries=3 --timeout=300");
    }
}
