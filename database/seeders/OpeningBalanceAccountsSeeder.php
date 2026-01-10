<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemSetting;
use App\Models\ChartAccount;

class OpeningBalanceAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get Retained Earnings chart account
        $retainedEarningsAccount = ChartAccount::where('account_code', '3000')
            ->where('account_name', 'Retained Earnings')
            ->first();

        if (!$retainedEarningsAccount) {
            $this->command->warn('Retained Earnings chart account not found. Please run ChartAccountSeeder first.');
            return;
        }

        $retainedEarningsAccountId = $retainedEarningsAccount->id;

        // Set opening balance accounts for Shares, Contributions, and Loans
        SystemSetting::setValue(
            'shares_opening_balance_account_id',
            $retainedEarningsAccountId,
            'integer',
            'accounting',
            'SHARES Opening Balance Account',
            'Chart account for SHARES opening balances'
        );

        SystemSetting::setValue(
            'savings_opening_balance_account_id',
            $retainedEarningsAccountId,
            'integer',
            'accounting',
            'SAVINGS Opening Balance Account',
            'Chart account for SAVINGS/Contributions opening balances'
        );

        SystemSetting::setValue(
            'deposits_opening_balance_account_id',
            $retainedEarningsAccountId,
            'integer',
            'accounting',
            'DEPOSITS Opening Balance Account',
            'Chart account for DEPOSITS/Loans opening balances'
        );

        // Also create the lines array in JSON format for the new form structure
        $lines = [
            [
                'category' => 'Shares',
                'chart_account_id' => $retainedEarningsAccountId,
            ],
            [
                'category' => 'Contributions',
                'chart_account_id' => $retainedEarningsAccountId,
            ],
            [
                'category' => 'Loans',
                'chart_account_id' => $retainedEarningsAccountId,
            ],
        ];

        SystemSetting::setValue(
            'opening_balance_accounts_lines',
            json_encode($lines),
            'json',
            'accounting',
            'Opening Balance Accounts Lines',
            'Full configuration of opening balance accounts by category'
        );

        $this->command->info('Opening balance accounts settings seeded successfully!');
        $this->command->info("All categories (Shares, Contributions, Loans) set to use Retained Earnings (ID: {$retainedEarningsAccountId})");
    }
}
