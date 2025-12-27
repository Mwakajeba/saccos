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
            'Accounting' => [
                'icon' => 'bx bx-calculator',
                'visibleRoutes' => [
                    ['name' => 'Charts of account - FSLI', 'route' => 'accounting.account-class-groups.index'],
                    ['name' => 'Charts of account', 'route' => 'accounting.chart-accounts.index'],
                    ['name' => 'Suppliers', 'route' => 'accounting.suppliers.index'],
                    ['name' => 'Manual journals', 'route' => 'accounting.journals.index'],
                    ['name' => 'Payment voucher', 'route' => 'accounting.payment-vouchers.index'],
                    ['name' => 'Receipt voucher', 'route' => 'accounting.receipt-vouchers.index'],
                    ['name' => 'Bank accounts', 'route' => 'accounting.bank-accounts'],
                    ['name' => 'Bank reconciliation', 'route' => 'accounting.bank-reconciliation.index'],
                    ['name' => 'Bill purchases', 'route' => 'accounting.bill-purchases'],
                    ['name' => 'Budget', 'route' => 'accounting.budgets.index'],
                    // ['name' => 'Fees', 'route' => 'accounting.fees.index'],
                    // ['name' => 'Penalties', 'route' => 'accounting.penalties.index'],
                ],
                'hiddenRoutes' => [
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

            'Reports' => [
                'icon' => 'bx bx-file',
                'visibleRoutes' => [
                    ['name' => 'Accounting Reports', 'route' => 'accounting.reports.index'],
                    ['name' => 'Loans Reports', 'route' => 'reports.loans'],
                    ['name' => 'Customer Reports', 'route' => 'reports.customers'],
                    ['name' => 'Share Reports', 'route' => 'reports.shares'],
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
