<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    public function run()
    {
        Company::updateOrCreate(
            ['name' => 'SAFCO FINTECH LTD'],
            [
                'email' => 'info@safco.com',
                'phone' => '255754000000',
                'address' => 'Dar es Salaam, Tanzania',
                'logo' => 'safco_logo.png', // e.g., stored in public/logos/
                'bg_color' => '#003366',
                'txt_color' => '#ffffff',
                'status' => 'active',
                'registration_date' => now(),
            ]
        );
    }
}
