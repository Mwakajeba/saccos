<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            ['name' => 'Arusha'],
            ['name' => 'Dar es Salaam'],
            ['name' => 'Dodoma'],
            ['name' => 'Geita'],
            ['name' => 'Iringa'],
            ['name' => 'Kagera'],
            ['name' => 'Katavi'],
            ['name' => 'Kigoma'],
            ['name' => 'Kilimanjaro'],
            ['name' => 'Lindi'],
            ['name' => 'Manyara'],
            ['name' => 'Mara'],
            ['name' => 'Mbeya'],
            ['name' => 'Morogoro'],
            ['name' => 'Mtwara'],
            ['name' => 'Mwanza'],
            ['name' => 'Njombe'],
            ['name' => 'Pemba North'],
            ['name' => 'Pemba South'],
            ['name' => 'Pwani'],
            ['name' => 'Rukwa'],
            ['name' => 'Ruvuma'],
            ['name' => 'Shinyanga'],
            ['name' => 'Simiyu'],
            ['name' => 'Singida'],
            ['name' => 'Songwe'],
            ['name' => 'Tabora'],
            ['name' => 'Tanga'],
            ['name' => 'Unguja North'],
            ['name' => 'Unguja South'],
            ['name' => 'Unguja Urban/West'],
        ];

        // Insert or update each region (prevents duplicate entry errors)
        foreach ($regions as $region) {
            DB::table('regions')->updateOrInsert(
                ['name' => $region['name']],
                array_merge($region, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
