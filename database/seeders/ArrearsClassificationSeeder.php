<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ArrearsClassification;
use App\Models\Company;

class ArrearsClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all companies
        $companies = Company::all();

        // Default arrears classifications
        $classifications = [
            [
                'days_from' => 0,
                'days_to' => 0,
                'bucket_label' => '0 days',
                'status' => 'Current',
                'provision_percentage' => 0,
                'comments' => 'Loans that are current with no overdue payments',
                'sort_order' => 1,
            ],
            [
                'days_from' => 1,
                'days_to' => 30,
                'bucket_label' => '1-30 days',
                'status' => 'Past Due',
                'provision_percentage' => 1,
                'comments' => 'Loans with payments overdue by 1-30 days',
                'sort_order' => 2,
            ],
            [
                'days_from' => 31,
                'days_to' => 90,
                'bucket_label' => '31-90 days',
                'status' => 'Substandard',
                'provision_percentage' => 20,
                'comments' => 'Loans with payments overdue by 31-90 days',
                'sort_order' => 3,
            ],
            [
                'days_from' => 91,
                'days_to' => 180,
                'bucket_label' => '91-180 days',
                'status' => 'Doubtful',
                'provision_percentage' => 50,
                'comments' => 'Loans with payments overdue by 91-180 days',
                'sort_order' => 4,
            ],
            [
                'days_from' => 181,
                'days_to' => null,
                'bucket_label' => '181+ days',
                'status' => 'Loss/NPL',
                'provision_percentage' => 100,
                'comments' => 'Non-performing loans with payments overdue by 181+ days',
                'sort_order' => 5,
            ],
        ];

        foreach ($companies as $company) {
            foreach ($classifications as $classification) {
                ArrearsClassification::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'status' => $classification['status'],
                    ],
                    array_merge($classification, [
                        'company_id' => $company->id,
                        'is_active' => true,
                    ])
                );
            }
        }

        $this->command->info('Arrears classifications seeded for ' . $companies->count() . ' companies.');
    }
}
