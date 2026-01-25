<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Company;

class BranchSeeder extends Seeder
{
    public function run()
    {
        $company = Company::where('name', 'SAFCO FINTECH LTD')->first() ?? Company::first();

        if (!$company) {
            $company = Company::create([
                'name' => 'SAFCO FINTECH LTD',
                'email' => 'info@safco.com',
                'phone' => '255754000000',
                'address' => 'Dar es Salaam, Tanzania',
                'status' => 'active',
            ]);
        }

        $branches = [
            [
                'name' => 'Main Branch',
                'email' => 'main@safco.com',
                'phone' => '255754111111',
                'address' => 'City Center, Dar es Salaam',
                'location' => 'Dar es Salaam',
                'manager_name' => 'Main Manager',
            ],
            [
                'name' => 'Mwanza Branch',
                'email' => 'mwanza@safco.com',
                'phone' => '255754222222',
                'address' => 'Rock City Mall, Mwanza',
                'location' => 'Mwanza',
                'manager_name' => 'Mwanza Manager',
            ],
        ];

        foreach ($branches as $branchData) {
            Branch::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'name' => $branchData['name'],
                ],
                array_merge($branchData, [
                    'branch_id' => (string) \Illuminate\Support\Str::uuid(),
                    'branch_name' => $branchData['name'],
                    'status' => 'active',
                ])
            );
        }
    }
}
