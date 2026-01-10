<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JournalReference;
use App\Models\Company;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class JournalReferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing records
        $company = Company::first();
        $companyId = $company->id ?? 1;
        $branch = Branch::first();
        $branchId = $branch->id ?? null;

        $journalReferences = [
            [
                'name' => 'Riba Payables Journals',
                'reference' => 'JR0001',
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Riba Juu ya Akiba',
                'reference' => 'JR0002',
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Riba Juu ya Amana',
                'reference' => 'JR0003',
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Contribution To Pay Loan',
                'reference' => 'JR0004',
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Share To Pay Loan',
                'reference' => 'JR0005',
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Accrued Interest Calculation Job',
                'reference' => 'JR0006',
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert or update each journal reference (prevents duplicate entry errors)
        foreach ($journalReferences as $journalRef) {
            DB::table('journal_references')->updateOrInsert(
                [
                    'reference' => $journalRef['reference'],
                    'company_id' => $journalRef['company_id']
                ],
                $journalRef
            );
        }

        $this->command->info('Journal references seeded successfully!');
    }
}
