<?php

namespace Database\Seeders;

use App\Models\Assets\Department;
use App\Models\Company;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class AssetDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();
        $branch = Branch::first();

        if (!$company || !$branch) {
            $this->command->warn('No company or branch found. Please create them first.');
            return;
        }

        $departments = [
            ['name' => 'Administration', 'code' => 'ADM', 'description' => 'Administrative department'],
            ['name' => 'Finance', 'code' => 'FIN', 'description' => 'Finance and accounting department'],
            ['name' => 'Operations', 'code' => 'OPS', 'description' => 'Operations department'],
            ['name' => 'IT', 'code' => 'IT', 'description' => 'Information Technology department'],
            ['name' => 'Human Resources', 'code' => 'HR', 'description' => 'Human resources department'],
            ['name' => 'Marketing', 'code' => 'MKT', 'description' => 'Marketing and sales department'],
            ['name' => 'Branch Operations', 'code' => 'BRN', 'description' => 'Branch operations department'],
            ['name' => 'Customer Service', 'code' => 'CS', 'description' => 'Customer service department'],
            ['name' => 'Security', 'code' => 'SEC', 'description' => 'Security department'],
            ['name' => 'Facilities', 'code' => 'FAC', 'description' => 'Facilities management department'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'code' => $dept['code']
                ],
                [
                    'branch_id' => $branch->id,
                    'name' => $dept['name'],
                    'description' => $dept['description'],
                    'created_by' => 1,
                    'updated_by' => 1,
                ]
            );
        }

        $this->command->info('Asset departments seeded successfully!');
    }
}
