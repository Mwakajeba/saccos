<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get account group IDs by group_code
        $hisaGroupId = DB::table('account_class_groups')
            ->where('group_code', '2200')
            ->where('company_id', 1)
            ->value('id');
        
        $amanaGroupId = DB::table('account_class_groups')
            ->where('group_code', '2300')
            ->where('company_id', 1)
            ->value('id');
        
        $akibaGroupId = DB::table('account_class_groups')
            ->where('group_code', '2400')
            ->where('company_id', 1)
            ->value('id');
        
        $interestExpensesGroupId = DB::table('account_class_groups')
            ->where('group_code', '5300')
            ->where('company_id', 1)
            ->value('id');
        
        $otherPayablesGroupId = DB::table('account_class_groups')
            ->where('group_code', '2100')
            ->where('company_id', 1)
            ->value('id');
        
        $retainedEarningsGroupId = DB::table('account_class_groups')
            ->where('group_code', '3000')
            ->where('company_id', 1)
            ->value('id');
        
        $interestIncomeGroupId = DB::table('account_class_groups')
            ->where('group_code', '4000')
            ->where('company_id', 1)
            ->value('id');
        
        $otherIncomeGroupId = DB::table('account_class_groups')
            ->where('group_code', '4100')
            ->where('company_id', 1)
            ->value('id');
        
        $operatingExpensesGroupId = DB::table('account_class_groups')
            ->where('group_code', '5100')
            ->where('company_id', 1)
            ->value('id');
        
        $otherReceivablesGroupId = DB::table('account_class_groups')
            ->where('group_code', '1300')
            ->where('company_id', 1)
            ->value('id');
        
        $retainedEarningsEquityCategoryId = DB::table('equity_categories')
            ->where('name', 'Retained Earnings')
            ->value('id');

        $accounts = [
            // Cash and Bank (Group ID: 1)
            [
                'account_code' => '1001',
                'account_name' => 'CRDB Bank',
                'account_class_group_id' => 1,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 4,
                'equity_category_id' => null,
            ],
            [
                'account_code' => '1022',
                'account_name' => 'NMB Bank',
                'account_class_group_id' => 1,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 4,
                'equity_category_id' => null,
            ],
            [
                'account_code' => '1021',
                'account_name' => 'Equity Bank',
                'account_class_group_id' => 1,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 4,
                'equity_category_id' => null,
            ],
            [
                'account_code' => '1023',
                'account_name' => 'Cash account',
                'account_class_group_id' => 1,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 4,
                'equity_category_id' => null,
            ],

            // Loan Receivables (Group ID: 2)
            [
                'account_code' => '1500',
                'account_name' => 'Principal Receivable',
                'account_class_group_id' => 2,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Interest Receivables (Group ID: 3)
            [
                'account_code' => '1002',
                'account_name' => 'interest receivable',
                'account_class_group_id' => 3,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Other Receivables (Group ID: 4) - Penalty Receivable
            [
                'account_code' => '1003',
                'account_name' => 'Penalty Receivable',
                'account_class_group_id' => 4,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Business Capital (Group ID: 8)
            [
                'account_code' => '3003',
                'account_name' => 'Share Capital',
                'account_class_group_id' => 8,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Retained Earnings (Group: 3000)
            [
                'account_code' => '3000',
                'account_name' => 'Retained Earnings',
                'account_class_group_id' => $retainedEarningsGroupId,
                'has_cash_flow' => 0,
                'has_equity' => 1,
                'cash_flow_category_id' => null,
                'equity_category_id' => $retainedEarningsEquityCategoryId,
            ],

            // Interest Income (Group: 4000)
            [
                'account_code' => '4570',
                'account_name' => 'Interest income',
                'account_class_group_id' => $interestIncomeGroupId,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Other Income (Group ID: 10) - Penalty Income
            [
                'account_code' => '4002',
                'account_name' => 'Penalty Income',
                'account_class_group_id' => 10,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Cash Deposit (Group ID: 5) - Customer operation account
            [
                'account_code' => '2010',
                'account_name' => 'Customer operation account',
                'account_class_group_id' => 5,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Hisa za Wanachama (Member Shares) - Group: 2200
            [
                'account_code' => '2200',
                'account_name' => 'Hisa za Wanachama',
                'account_class_group_id' => $hisaGroupId,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Amana za Wanachama (Member Contributions) - Group: 2300
            [
                'account_code' => '2300',
                'account_name' => 'Amana za Wanachama',
                'account_class_group_id' => $amanaGroupId,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Akiba za Wanachama (Member Savings) - Group: 2400
            [
                'account_code' => '2400',
                'account_name' => 'Akiba za Wanachama',
                'account_class_group_id' => $akibaGroupId,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Interest Expenses (Group: 5300) - Riba Juu ya Akiba
            [
                'account_code' => '5301',
                'account_name' => 'Riba Juu ya Akiba',
                'account_class_group_id' => $interestExpensesGroupId,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Interest Expenses (Group: 5300) - Riba Juu ya Amana
            [
                'account_code' => '5302',
                'account_name' => 'Riba Juu ya Amana',
                'account_class_group_id' => $interestExpensesGroupId,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Other payables (Group: 2100) - Riba Payables
            [
                'account_code' => '2101',
                'account_name' => 'Riba Payables',
                'account_class_group_id' => $otherPayablesGroupId,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Other payables (Group: 2100) - Withholding Tax
            [
                'account_code' => '2102',
                'account_name' => 'Withholding Tax',
                'account_class_group_id' => $otherPayablesGroupId,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Direct Write Off Account (Expense) - Operating Expenses (Group: 5100)
            [
                'account_code' => '5101',
                'account_name' => 'Direct Write Off Account',
                'account_class_group_id' => $operatingExpensesGroupId,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Using Provision Account (Asset) - Other Receivables (Group: 1300)
            [
                'account_code' => '1301',
                'account_name' => 'Using Provision Account',
                'account_class_group_id' => $otherReceivablesGroupId,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Income Provision Account (Income) - Other Income (Group: 4100)
            [
                'account_code' => '4101',
                'account_name' => 'Income Provision Account',
                'account_class_group_id' => $otherIncomeGroupId,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],
        ];

        foreach ($accounts as $account) {
            DB::table('chart_accounts')->updateOrInsert(
                ['account_code' => $account['account_code']],
                array_merge($account, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
