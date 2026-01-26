<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sector;
use Illuminate\Support\Facades\DB;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sectors = [
            ['name' => 'Agriculture', 'description' => 'Farming, livestock, fishing, and agricultural production', 'status' => 'active'],
            ['name' => 'Manufacturing', 'description' => 'Production of goods and industrial manufacturing', 'status' => 'active'],
            ['name' => 'Retail & Wholesale', 'description' => 'Retail shops, wholesale trade, and distribution', 'status' => 'active'],
            ['name' => 'Construction', 'description' => 'Building construction, infrastructure, and real estate development', 'status' => 'active'],
            ['name' => 'Transportation', 'description' => 'Transport services, logistics, and delivery', 'status' => 'active'],
            ['name' => 'Healthcare', 'description' => 'Medical services, clinics, pharmacies, and health facilities', 'status' => 'active'],
            ['name' => 'Education', 'description' => 'Schools, colleges, training centers, and educational services', 'status' => 'active'],
            ['name' => 'Tourism & Hospitality', 'description' => 'Hotels, restaurants, tourism, and hospitality services', 'status' => 'active'],
            ['name' => 'Financial Services', 'description' => 'Banking, insurance, microfinance, and financial institutions', 'status' => 'active'],
            ['name' => 'Technology & ICT', 'description' => 'Information technology, telecommunications, and digital services', 'status' => 'active'],
            ['name' => 'Mining', 'description' => 'Mining operations, extraction, and mineral processing', 'status' => 'active'],
            ['name' => 'Energy', 'description' => 'Power generation, electricity distribution, and energy services', 'status' => 'active'],
            ['name' => 'Professional Services', 'description' => 'Legal, accounting, consulting, and professional services', 'status' => 'active'],
            ['name' => 'Arts & Entertainment', 'description' => 'Media, entertainment, arts, and creative industries', 'status' => 'active'],
            ['name' => 'Public Services', 'description' => 'Government services, public administration, and civil services', 'status' => 'active'],
            ['name' => 'Other', 'description' => 'Other business sectors not listed above', 'status' => 'active'],
        ];

        foreach ($sectors as $sector) {
            Sector::updateOrCreate(
                ['name' => $sector['name']],
                $sector
            );
        }

        $this->command->info('Sectors seeded successfully!');
    }
}
