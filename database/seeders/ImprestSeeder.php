<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChartAccount;
use App\Models\Company;
use App\Models\Branch;

class ImprestSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get the first company and branch for seeding
        $company = Company::first();
        $branch = Branch::first();

        if (!$company || !$branch) {
            $this->command->info('No company or branch found. Please create them first.');
            return;
        }

        // Create essential chart accounts for imprest management if they don't exist
        $accountsToCheck = [
            'Staff Imprest',
            'Cash in Hand',
        ];

        $this->command->info('Checking for imprest-related chart accounts...');

        foreach ($accountsToCheck as $accountName) {
            $exists = ChartAccount::where('account_name', 'LIKE', '%' . $accountName . '%')->exists();
            if ($exists) {
                $this->command->info("Account '{$accountName}' already exists.");
            } else {
                $this->command->warn("Account '{$accountName}' not found. You may need to create it manually.");
            }
        }

        $this->command->info('Imprest management seeder completed!');
        $this->command->info('Note: Please ensure you have the following accounts in your Chart of Accounts:');
        $this->command->info('  - Staff Imprest (Asset account for imprest advances)');
        $this->command->info('  - Cash in Hand (Asset account)');
        $this->command->info('  - Expense accounts for liquidation');
    }
}
