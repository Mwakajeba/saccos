<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\College\AssessmentType;
use App\Models\Company;

class AssessmentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        
        if (!$company) {
            $this->command->error('No company found! Please create a company first.');
            return;
        }

        $assessmentTypes = [
            [
                'name' => 'Assignment',
                'code' => 'ASG',
                'description' => 'Written or practical assignments',
                'default_weight' => 10,
                'max_score' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Test 1',
                'code' => 'TST1',
                'description' => 'First continuous assessment test',
                'default_weight' => 10,
                'max_score' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Test 2',
                'code' => 'TST2',
                'description' => 'Second continuous assessment test',
                'default_weight' => 10,
                'max_score' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Quiz',
                'code' => 'QUZ',
                'description' => 'Short quiz assessment',
                'default_weight' => 5,
                'max_score' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Presentation',
                'code' => 'PRES',
                'description' => 'Oral or group presentation',
                'default_weight' => 5,
                'max_score' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Practical',
                'code' => 'PRAC',
                'description' => 'Hands-on practical work',
                'default_weight' => 10,
                'max_score' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Class Participation',
                'code' => 'PART',
                'description' => 'Active participation in class',
                'default_weight' => 5,
                'max_score' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($assessmentTypes as $type) {
            AssessmentType::create([
                ...$type,
                'company_id' => $company->id,
            ]);
        }

        $this->command->info('Assessment types seeded successfully!');
        $this->command->info('Total: 7 assessment types (CA = 40%)');
    }
}
