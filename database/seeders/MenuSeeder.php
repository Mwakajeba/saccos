<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Role;

class MenuSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::where('name', 'admin')->first();

        if (!$adminRole) {
            $this->command->warn('Admin role not found.');
            return;
        }

        $entities = [
                       
            'Dashboard' => [
                'icon' => 'bx bx-home',
                'visibleRoutes' => [
                    ['name' => 'Dashboard', 'route' => 'dashboard'],
                ],
                'hiddenRoutes' => [],
            ],

            'Customers' => [
                'icon' => 'bx bx-group',
                'visibleRoutes' => [
                    ['name' => 'Customer List', 'route' => 'customers.index'],
                    ['name' => 'Add New Customer', 'route' => 'customers.create'],
                ],
                'hiddenRoutes' => ['customers.edit', 'customers.destroy', 'customers.show'],
            ],

            'Complains' => [
                'icon' => 'bx bx-message-square-dots',
                'visibleRoutes' => [
                    ['name' => 'Complains', 'route' => 'complains.index'],
                ],
                'hiddenRoutes' => ['complains.data', 'complains.show', 'complains.edit', 'complains.update'],
            ],
            'Accounting' => [
                'icon' => 'bx bx-calculator',
                'visibleRoutes' => [
                    ['name' => 'Accounting', 'route' => 'accounting.index'],
                ],
                'hiddenRoutes' => [
                    'accounting.account-class-groups.index',
                    'accounting.chart-accounts.index',
                    'accounting.suppliers.index',
                    'accounting.journals.index',
                    'accounting.payment-vouchers.index',
                    'accounting.receipt-vouchers.index',
                    'accounting.bank-accounts',
                    'accounting.bank-reconciliation.index',
                    'accounting.bill-purchases',
                    'accounting.budgets.index',
                    'accounting.chart-accounts.create',
                    'accounting.chart-accounts.edit',
                    'accounting.chart-accounts.destroy',
                    'accounting.journals.edit',
                    'accounting.journals.destroy',
                    'accounting.journals.create',
                    'accounting.journals.show'
                ],
            ],
             'Contributions' => [
                            'icon' => 'bx bx-donate-heart',
                            'visibleRoutes' => [
                                ['name' => 'Contributions', 'route' => 'contributions.index'],
                            ],
                            'hiddenRoutes' => [
                                'contributions.products.index',
                                'contributions.accounts.index',
                                'contributions.deposits.index',
                                'contributions.withdrawals.index',
                                'contributions.transfers.index',
                                'contributions.transfers.pending',
                                'contributions.reports.balance',
                                'contributions.reports.transactions',
                            ],
            ],
            'Loan Management' => [
                'icon' => 'bx bx-credit-card',
                'visibleRoutes' => [
                    ['name' => 'Loan Products', 'route' => 'loan-products.index'],
                    ['name' => 'Groups', 'route' => 'groups.index'],
                    ['name' => 'Loans', 'route' => 'loans.index'],
                ],
                'hiddenRoutes' => ['loan-products.edit', 'loan-products.destroy', 'loan-products.show', 'groups.edit', 'groups.destroy', 'groups.show', 'groups.create', 'groups.payment', 'loans.edit', 'loans.destroy', 'loans.show', 'loans.create', 'loans.list'],
            ],

            'Shares Management' => [
                'icon' => 'bx bx-bar-chart-square',
                'visibleRoutes' => [
                    ['name' => 'Shares Management', 'route' => 'shares.management'],
                ],
                'hiddenRoutes' => ['shares.products.index', 'shares.accounts.index', 'shares.deposits.index', 'shares.withdrawals.index', 'shares.transfers.index'],
            ],

            'Inventory' => [
                'icon' => 'bx bx-package',
                'visibleRoutes' => [
                    ['name' => 'Inventory Management', 'route' => 'inventory.index'],
                ],
                'hiddenRoutes' => ['inventory.items.index', 'inventory.items.create', 'inventory.items.edit', 'inventory.items.destroy', 'inventory.items.show', 'inventory.categories.index', 'inventory.categories.create', 'inventory.categories.edit', 'inventory.categories.destroy', 'inventory.movements.index', 'inventory.movements.create', 'inventory.movements.edit', 'inventory.movements.destroy'],
            ],

            'Assets Management' => [
                'icon' => 'bx bx-building',
                'visibleRoutes' => [
                    ['name' => 'Assets Management', 'route' => 'assets.index'],
                ],
                'hiddenRoutes' => [
                    'assets.depreciation.index',
                    'assets.depreciation.process',
                    'assets.depreciation.history',
                    'assets.depreciation.history.data',
                    'assets.depreciation.forecast',
                    'assets.tax-depreciation.index',
                    'assets.tax-depreciation.process',
                    'assets.tax-depreciation.history',
                    'assets.tax-depreciation.history.data',
                    'assets.tax-depreciation.reports.tra-schedule',
                    'assets.tax-depreciation.reports.tra-schedule.data',
                    'assets.tax-depreciation.reports.book-tax-reconciliation',
                    'assets.tax-depreciation.reports.book-tax-reconciliation.data',
                    'assets.deferred-tax.index',
                    'assets.deferred-tax.process',
                    'assets.deferred-tax.schedule',
                    'assets.deferred-tax.schedule.data',
                ],
            ],

             'Purchases' => [
                'icon' => 'bx bx-shopping-bag',
                'visibleRoutes' => [
                    ['name' => 'Purchases Management', 'route' => 'purchases.index'],
                ],
                'hiddenRoutes' => [
                    'purchases.quotations.index',
                    'purchases.quotations.create',
                    'purchases.quotations.edit',
                    'purchases.quotations.destroy',
                    'purchases.quotations.show'
                ],
            ],

            'Investment' => [
                'icon' => 'bx bx-trending-up',
                'visibleRoutes' => [
                    ['name' => 'UTT Funds', 'route' => 'investments.funds.index'],
                    ['name' => 'Holdings Register', 'route' => 'investments.holdings.index'],
                    ['name' => 'Transactions', 'route' => 'investments.transactions.index'],
                    ['name' => 'NAV Prices', 'route' => 'investments.nav-prices.index'],
                    ['name' => 'Cash Flows', 'route' => 'investments.cash-flows.index'],
                    ['name' => 'Reconciliations', 'route' => 'investments.reconciliations.index'],
                ],
                'hiddenRoutes' => [
                    'investments.funds.create',
                    'investments.funds.edit',
                    'investments.funds.show',
                    'investments.transactions.create',
                    'investments.transactions.show',
                    'investments.transactions.approve',
                    'investments.transactions.settle',
                    'investments.transactions.cancel',
                    'investments.nav-prices.create',
                    'investments.reconciliations.create',
                    'investments.valuation',
                    'investments.member-view',
                ],
            ],

            'Reports' => [
                'icon' => 'bx bx-file',
                'visibleRoutes' => [
                    ['name' => 'Accounting Reports', 'route' => 'accounting.reports.index'],
                    ['name' => 'Loans Reports', 'route' => 'reports.loans'],
                    ['name' => 'Customer Reports', 'route' => 'reports.customers'],
                    ['name' => 'Share Reports', 'route' => 'reports.shares'],
                    ['name' => 'Contribution Reports', 'route' => 'reports.contributions'],
                    ['name' => 'Bot Reports', 'route' => 'reports.bot'],
                ],
                'hiddenRoutes' => [],
            ],
            // 'Chat' => [
            //     'icon' => 'bx bx-message',
            //     'visibleRoutes' => [
            //         ['name' => 'Chat', 'route' => 'chat.index'],
            //     ],
            //     'hiddenRoutes' => ['chat.messages', 'chat.send'],
            // ],

            // Add Change Branch menu under Dashboard
            'Change Branch' => [
                'icon' => 'bx bx-transfer',
                'visibleRoutes' => [
                    ['name' => 'Change Branch', 'route' => 'change-branch'],
                ],
                'hiddenRoutes' => [],
            ],

            'Settings' => [
                'icon' => 'bx bx-cog',
                'visibleRoutes' => [
                    ['name' => 'General Settings', 'route' => 'settings.index'],
                ],
                'hiddenRoutes' => ['settings.company', 'settings.branches', 'settings.user', 'settings.system', 'settings.backup', 'settings.branches.create', 'settings.branches.edit', 'settings.branches.destroy', 'settings.filetypes.index', 'settings.filetypes.create', 'settings.filetypes.edit', 'settings.filetypes.destroy'],
            ],
        ];

        foreach ($entities as $parentName => $data) {
            $parent = Menu::firstOrCreate([
                'name' => $parentName,
                'route' => null,
                'parent_id' => null,
                'icon' => $data['icon'],
            ]);

            $menuIds = [$parent->id];

            // Only visible menu entries
            foreach ($data['visibleRoutes'] as $child) {
                $childMenu = Menu::firstOrCreate([
                    'name' => $child['name'],
                    'route' => $child['route'],
                    'parent_id' => $parent->id,
                    'icon' => 'bx bx-right-arrow-alt',
                ]);

                $menuIds[] = $childMenu->id;
            }

            // Hidden permission-only routes (not shown in menu)
            // These routes are for permissions only and should not be created as menu entries
            // They are handled by the permission system directly
            $superAdminRole = Role::where('name', 'super-admin')->first();
            
            $superAdminRole->menus()->syncWithoutDetaching($menuIds);

            $adminRole->menus()->syncWithoutDetaching($menuIds);
        }
    }
}
