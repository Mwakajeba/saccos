<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckBcmathExtension extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:bcmath';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if bcmath or gmp extension is installed for Hashids support';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking PHP extensions required for Hashids...');
        $this->newLine();

        $bcmathLoaded = extension_loaded('bcmath');
        $gmpLoaded = extension_loaded('gmp');

        if ($bcmathLoaded) {
            $this->info('✓ bcmath extension is installed and loaded');
        } else {
            $this->error('✗ bcmath extension is NOT installed');
        }

        if ($gmpLoaded) {
            $this->info('✓ gmp extension is installed and loaded');
        } else {
            $this->error('✗ gmp extension is NOT installed');
        }

        $this->newLine();

        if ($bcmathLoaded || $gmpLoaded) {
            $this->info('✓ Hashids should work correctly!');
            return 0;
        }

        $this->error('✗ Neither bcmath nor gmp extension is installed!');
        $this->newLine();
        $this->warn('Hashids library requires either bcmath or gmp PHP extension to function.');
        $this->newLine();
        $this->info('To install bcmath extension, run:');
        $this->line('  sudo apt-get update');
        $this->line('  sudo apt-get install php8.3-bcmath');
        $this->line('  sudo systemctl restart php8.3-fpm  # or apache2/nginx');
        $this->newLine();
        $this->info('Or install gmp extension:');
        $this->line('  sudo apt-get install php8.3-gmp');
        $this->line('  sudo systemctl restart php8.3-fpm  # or apache2/nginx');
        $this->newLine();
        $this->info('See INSTALL_BCMATH.md for more detailed instructions.');

        return 1;
    }
}
