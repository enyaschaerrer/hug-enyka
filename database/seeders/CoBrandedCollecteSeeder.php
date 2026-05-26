<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CoBrandedCollecteSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::updateOrCreate([
            'slug' => 'rolex',
        ], [
            'name' => 'Rolex',
            'logo' => '/img/companies/rolex-logo.png',
            'short_description' => 'Collecte de sang co-brandee pour les collaborateurs Rolex.',
            'address' => 'Rue Francois-Dussaud 3-5-7, 1227 Geneve',
            'email' => 'collecte-demo@rolex.test',
            'telephone' => '+41 22 000 00 00',
            'employee_count' => 9000,
            'allowed_email_domains' => 'rolex.com',
            'source' => 'seed',
            'primaryColor' => '#006039',
            'secondaryColor' => '#A37E2C',
            'thirdColor' => '#F6F1E7',
        ]);

        Collection::updateOrCreate([
            'access_token' => '7f4c9b8e2a6d4f01',
        ], [
            'company_id' => $company->id,
            'start' => '2026-06-03 09:00:00',
            'end' => '2026-06-03 16:30:00',
            'month' => 6,
            'year' => 2026,
            'linkOneDoc' => 'https://example.com/prise-rendez-vous/rolex-demo',
        ]);
    }
}
