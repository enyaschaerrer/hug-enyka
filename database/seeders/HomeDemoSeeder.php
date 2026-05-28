<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HomeDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ----- companies -----
        $companiesData = [
            [
                'name' => 'BCGE',
                'slug' => 'bcge',
                'logo' => '/img/companies/bcge_logo.svg',
                'short_description' => 'Banque cantonale du canton de Genève, partenaire bancaire de référence depuis 1816.',
                'address' => 'Quai de l\'Île 17, 1204 Genève',
                'email' => 'demo@bcge.test',
                'telephone' => '+41 22 317 27 27',
                'employee_count' => 860,
                'allowed_email_domains' => 'bcge.ch',
                'source' => 'seed-home',
                'primaryColor' => '#009B3A',
                'secondaryColor' => '#003A22',
                'thirdColor' => '#F2F8F4',
            ],
            [
                'name' => 'Pictet',
                'slug' => 'pictet',
                'logo' => '/img/companies/pictet_logo.png',
                'short_description' => 'Groupe bancaire privé suisse fondé en 1805, dédié à la gestion de fortune.',
                'address' => 'Route des Acacias 60, 1211 Genève',
                'email' => 'demo@pictet.test',
                'telephone' => '+41 58 323 23 23',
                'employee_count' => 5500,
                'allowed_email_domains' => 'pictet.com',
                'source' => 'seed-home',
                'primaryColor' => '#002F6C',
                'secondaryColor' => '#C8102E',
                'thirdColor' => '#F0F2F7',
            ],
            [
                'name' => 'Patek Philippe',
                'slug' => 'patek',
                'logo' => '/img/companies/patekphilippe_logo.png',
                'short_description' => 'Manufacture horlogère genevoise indépendante, fondée en 1839.',
                'address' => 'Rue du Rhône 41, 1204 Genève',
                'email' => 'demo@patek.test',
                'telephone' => '+41 22 884 20 20',
                'employee_count' => 2500,
                'allowed_email_domains' => 'patek.com',
                'source' => 'seed-home',
                'primaryColor' => '#002A5C',
                'secondaryColor' => '#C9A14C',
                'thirdColor' => '#F4F1EA',
            ],
            [
                'name' => 'Caran d\'Ache',
                'slug' => 'caran-dache',
                'logo' => '/img/companies/carandache_logo.jpg',
                'short_description' => 'Maison genevoise d\'instruments d\'écriture et de beaux-arts depuis 1915.',
                'address' => 'Chemin du Foron 19, 1226 Thônex',
                'email' => 'demo@carandache.test',
                'telephone' => '+41 22 869 01 01',
                'employee_count' => 280,
                'allowed_email_domains' => 'carandache.com',
                'source' => 'seed-home',
                'primaryColor' => '#C8102E',
                'secondaryColor' => '#2A2A2A',
                'thirdColor' => '#F8E8EB',
            ],
            [
                'name' => 'OMS',
                'slug' => 'oms',
                'logo' => '/img/companies/oms_logo.png',
                'short_description' => 'Organisation mondiale de la Santé, agence onusienne pour la santé publique.',
                'address' => 'Avenue Appia 20, 1211 Genève 27',
                'email' => 'demo@oms.test',
                'telephone' => '+41 22 791 21 11',
                'employee_count' => 9000,
                'allowed_email_domains' => 'who.int',
                'source' => 'seed-home',
                'primaryColor' => '#008DC9',
                'secondaryColor' => '#003D7A',
                'thirdColor' => '#E6F4FB',
            ],
            [
                'name' => 'OMC',
                'slug' => 'omc',
                'logo' => '/img/companies/omc_logo.svg',
                'short_description' => 'Organisation mondiale du Commerce, instance régulant le commerce international.',
                'address' => 'Rue de Lausanne 154, 1211 Genève 21',
                'email' => 'demo@omc.test',
                'telephone' => '+41 22 739 51 11',
                'employee_count' => 650,
                'allowed_email_domains' => 'wto.org',
                'source' => 'seed-home',
                'primaryColor' => '#00558C',
                'secondaryColor' => '#6B7D8C',
                'thirdColor' => '#E8EFF5',
            ],
            [
                'name' => 'Richemont',
                'slug' => 'richemont',
                'logo' => '/img/companies/richemont_logo.png',
                'short_description' => 'Groupe suisse de luxe regroupant Cartier, Van Cleef & Arpels, Montblanc.',
                'address' => 'Route des Biches 10, 1752 Villars-sur-Glâne',
                'email' => 'demo@richemont.test',
                'telephone' => '+41 22 721 35 00',
                'employee_count' => 36000,
                'allowed_email_domains' => 'richemont.com',
                'source' => 'seed-home',
                'primaryColor' => '#1A1A1A',
                'secondaryColor' => '#B8923B',
                'thirdColor' => '#F5F2EC',
            ],
            [
                'name' => 'Rolex',
                'slug' => 'rolex',
                'logo' => '/img/companies/rolex_logo.png',
                'short_description' => 'Manufacture horlogère genevoise, leader mondial des montres de luxe.',
                'address' => 'Rue François-Dussaud 3-5-7, 1227 Genève',
                'email' => 'demo@rolex.test',
                'telephone' => '+41 22 302 22 00',
                'employee_count' => 9000,
                'allowed_email_domains' => 'rolex.com',
                'source' => 'seed-home',
                'primaryColor' => '#006039',
                'secondaryColor' => '#A37E2C',
                'thirdColor' => '#F6F1E7',
            ],
            [
                'name' => 'Firmenich',
                'slug' => 'firmenich',
                'logo' => '/img/companies/firmenich_logo.svg',
                'short_description' => 'Groupe genevois leader des arômes et parfums depuis 1895.',
                'address' => 'Route des Jeunes 1, 1211 Genève',
                'email' => 'demo@firmenich.test',
                'telephone' => '+41 22 780 22 11',
                'employee_count' => 10000,
                'allowed_email_domains' => 'firmenich.com',
                'source' => 'seed-home',
                'primaryColor' => '#6A1F7A',
                'secondaryColor' => '#FFB81C',
                'thirdColor' => '#F5EBF7',
            ],
            [
                'name' => 'Logitech',
                'slug' => 'logitech',
                'logo' => '/img/companies/logitech_logo.png',
                'short_description' => 'Entreprise suisse d\'accessoires informatiques et audio depuis 1981.',
                'address' => 'EPFL Innovation Park, 1015 Lausanne',
                'email' => 'demo@logitech.test',
                'telephone' => '+41 21 863 51 11',
                'employee_count' => 10000,
                'allowed_email_domains' => 'logitech.com',
                'source' => 'seed-home',
                'primaryColor' => '#00B8FC',
                'secondaryColor' => '#1A1A1A',
                'thirdColor' => '#E0F4FE',
            ],
        ];

        foreach ($companiesData as $row) {
            Company::updateOrCreate(['email' => $row['email']], $row);
        }

        $bcge       = Company::where('email', 'demo@bcge.test')->value('id');
        $pictet     = Company::where('email', 'demo@pictet.test')->value('id');
        $patek      = Company::where('email', 'demo@patek.test')->value('id');
        $carandache = Company::where('email', 'demo@carandache.test')->value('id');
        $oms        = Company::where('email', 'demo@oms.test')->value('id');
        $omc        = Company::where('email', 'demo@omc.test')->value('id');
        $richemont  = Company::where('email', 'demo@richemont.test')->value('id');
        $rolex      = Company::where('email', 'demo@rolex.test')->value('id');
        $firmenich  = Company::where('email', 'demo@firmenich.test')->value('id');
        $logitech   = Company::where('email', 'demo@logitech.test')->value('id');

        // ----- collections -----
        $collectionsData = [
            // BCGE (3)
            ['company_id' => $bcge,       'start' => '2019-06-15 09:00:00', 'end' => '2019-06-15 16:30:00', 'access_token' => 'a3f8c1b2e7d9f041', 'linkOneDoc' => 'https://example.com/onedoc/a3f8c1b2e7d9f041'],
            ['company_id' => $bcge,       'start' => '2022-06-15 09:00:00', 'end' => '2022-06-15 16:30:00', 'access_token' => 'b5e2d8a1c4f9e370', 'linkOneDoc' => 'https://example.com/onedoc/b5e2d8a1c4f9e370'],
            ['company_id' => $bcge,       'start' => '2025-06-15 09:00:00', 'end' => '2025-06-15 16:30:00', 'access_token' => 'd9c1f4a3e8b6d520', 'linkOneDoc' => 'https://example.com/onedoc/d9c1f4a3e8b6d520'],

            // Pictet (4)
            ['company_id' => $pictet,     'start' => '2018-06-15 09:00:00', 'end' => '2018-06-15 16:30:00', 'access_token' => 'e4a7d2b8c1f5e930', 'linkOneDoc' => 'https://example.com/onedoc/e4a7d2b8c1f5e930'],
            ['company_id' => $pictet,     'start' => '2020-06-15 09:00:00', 'end' => '2020-06-15 16:30:00', 'access_token' => 'f2b6c8d4a1e9f870', 'linkOneDoc' => 'https://example.com/onedoc/f2b6c8d4a1e9f870'],
            ['company_id' => $pictet,     'start' => '2022-06-15 09:00:00', 'end' => '2022-06-15 16:30:00', 'access_token' => 'c8d3a5f1b7e4c291', 'linkOneDoc' => 'https://example.com/onedoc/c8d3a5f1b7e4c291'],
            ['company_id' => $pictet,     'start' => '2024-06-15 09:00:00', 'end' => '2024-06-15 16:30:00', 'access_token' => 'a1e5d8b3c4f7a691', 'linkOneDoc' => 'https://example.com/onedoc/a1e5d8b3c4f7a691'],

            // Patek Philippe (2)
            ['company_id' => $patek,      'start' => '2020-06-15 09:00:00', 'end' => '2020-06-15 16:30:00', 'access_token' => 'b7c2f4a8d1e6c351', 'linkOneDoc' => 'https://example.com/onedoc/b7c2f4a8d1e6c351'],
            ['company_id' => $patek,      'start' => '2024-06-15 09:00:00', 'end' => '2024-06-15 16:30:00', 'access_token' => 'd3a8f5c1b4e9d721', 'linkOneDoc' => 'https://example.com/onedoc/d3a8f5c1b4e9d721'],

            // Caran d'Ache (2)
            ['company_id' => $carandache, 'start' => '2021-06-15 09:00:00', 'end' => '2021-06-15 16:30:00', 'access_token' => 'e6b1c4f8a3d5e971', 'linkOneDoc' => 'https://example.com/onedoc/e6b1c4f8a3d5e971'],
            ['company_id' => $carandache, 'start' => '2025-06-15 09:00:00', 'end' => '2025-06-15 16:30:00', 'access_token' => 'f4d2a8c5b1e7f391', 'linkOneDoc' => 'https://example.com/onedoc/f4d2a8c5b1e7f391'],

            // OMS (3)
            ['company_id' => $oms,        'start' => '2019-06-15 09:00:00', 'end' => '2019-06-15 16:30:00', 'access_token' => 'c1e7b4f2a8d3c561', 'linkOneDoc' => 'https://example.com/onedoc/c1e7b4f2a8d3c561'],
            ['company_id' => $oms,        'start' => '2023-06-15 09:00:00', 'end' => '2023-06-15 16:30:00', 'access_token' => 'a8b3d5c1f4e2a781', 'linkOneDoc' => 'https://example.com/onedoc/a8b3d5c1f4e2a781'],
            ['company_id' => $oms,        'start' => '2026-06-15 09:00:00', 'end' => '2026-06-15 16:30:00', 'access_token' => 'b6f4c8a1d3e5b291', 'linkOneDoc' => 'https://example.com/onedoc/b6f4c8a1d3e5b291'],

            // OMC (1)
            ['company_id' => $omc,        'start' => '2024-06-15 09:00:00', 'end' => '2024-06-15 16:30:00', 'access_token' => 'd2c8f4b1a5e7d961', 'linkOneDoc' => 'https://example.com/onedoc/d2c8f4b1a5e7d961'],

            // Richemont (4)
            ['company_id' => $richemont,  'start' => '2018-06-15 09:00:00', 'end' => '2018-06-15 16:30:00', 'access_token' => 'e9a5d3f1b8c4e271', 'linkOneDoc' => 'https://example.com/onedoc/e9a5d3f1b8c4e271'],
            ['company_id' => $richemont,  'start' => '2021-06-15 09:00:00', 'end' => '2021-06-15 16:30:00', 'access_token' => 'f7c4a8b2d5e1f681', 'linkOneDoc' => 'https://example.com/onedoc/f7c4a8b2d5e1f681'],
            ['company_id' => $richemont,  'start' => '2023-06-15 09:00:00', 'end' => '2023-06-15 16:30:00', 'access_token' => 'c5d1b4a8f3e6c292', 'linkOneDoc' => 'https://example.com/onedoc/c5d1b4a8f3e6c292'],
            ['company_id' => $richemont,  'start' => '2026-06-15 09:00:00', 'end' => '2026-06-15 16:30:00', 'access_token' => 'a4f8d3b1c7e5a961', 'linkOneDoc' => 'https://example.com/onedoc/a4f8d3b1c7e5a961'],

            // Rolex (4)
            ['company_id' => $rolex,      'start' => '2020-06-15 09:00:00', 'end' => '2020-06-15 16:30:00', 'access_token' => 'b9c5d2f4a8e1b371', 'linkOneDoc' => 'https://example.com/onedoc/b9c5d2f4a8e1b371'],
            ['company_id' => $rolex,      'start' => '2022-06-15 09:00:00', 'end' => '2022-06-15 16:30:00', 'access_token' => 'd7a3c8b1f5e2d461', 'linkOneDoc' => 'https://example.com/onedoc/d7a3c8b1f5e2d461'],
            ['company_id' => $rolex,      'start' => '2024-06-15 09:00:00', 'end' => '2024-06-15 16:30:00', 'access_token' => 'e1b8f4c5a3d6e971', 'linkOneDoc' => 'https://example.com/onedoc/e1b8f4c5a3d6e971'],
            ['company_id' => $rolex,      'start' => '2026-06-15 09:00:00', 'end' => '2026-06-15 16:30:00', 'access_token' => 'f5c2a8b3d1e4f871', 'linkOneDoc' => 'https://example.com/onedoc/f5c2a8b3d1e4f871'],

            // Firmenich (2)
            ['company_id' => $firmenich,  'start' => '2022-06-15 09:00:00', 'end' => '2022-06-15 16:30:00', 'access_token' => 'c8a4f1d5b2e7c361', 'linkOneDoc' => 'https://example.com/onedoc/c8a4f1d5b2e7c361'],
            ['company_id' => $firmenich,  'start' => '2026-06-15 09:00:00', 'end' => '2026-06-15 16:30:00', 'access_token' => 'a7d3c1b5f8e4a291', 'linkOneDoc' => 'https://example.com/onedoc/a7d3c1b5f8e4a291'],

            // Logitech (3)
            ['company_id' => $logitech,   'start' => '2021-06-15 09:00:00', 'end' => '2021-06-15 16:30:00', 'access_token' => 'b4f1c8a3d5e2b761', 'linkOneDoc' => 'https://example.com/onedoc/b4f1c8a3d5e2b761'],
            ['company_id' => $logitech,   'start' => '2024-06-15 09:00:00', 'end' => '2024-06-15 16:30:00', 'access_token' => 'd8a5c3f1b4e7d291', 'linkOneDoc' => 'https://example.com/onedoc/d8a5c3f1b4e7d291'],
            ['company_id' => $logitech,   'start' => '2025-06-15 09:00:00', 'end' => '2025-06-15 16:30:00', 'access_token' => 'e3b7d2f5a8c4e162', 'linkOneDoc' => 'https://example.com/onedoc/e3b7d2f5a8c4e162'],
        ];

        foreach ($collectionsData as $row) {
            Collection::updateOrCreate(['access_token' => $row['access_token']], $row);
        }

        // ----- trophy_editions -----
        $trophyEditionsData = [
            ['year' => 2023],
            ['year' => 2024],
            ['year' => 2025],
        ];

        foreach ($trophyEditionsData as $row) {
            DB::table('trophy_editions')->updateOrInsert(
                ['year' => $row['year']],
                ['updated_at' => now(), 'created_at' => now()],
            );
        }

        $edition2023 = DB::table('trophy_editions')->where('year', 2023)->value('id');
        $edition2024 = DB::table('trophy_editions')->where('year', 2024)->value('id');
        $edition2025 = DB::table('trophy_editions')->where('year', 2025)->value('id');

        // ----- prizes (podiums, type 'donneur') -----
        $prizesData = [
            // 2023
            ['trophy_edition_id' => $edition2023, 'company_id' => $patek,     'rank' => 1, 'type' => 'donneur'],
            ['trophy_edition_id' => $edition2023, 'company_id' => $richemont, 'rank' => 2, 'type' => 'donneur'],
            ['trophy_edition_id' => $edition2023, 'company_id' => $bcge,      'rank' => 3, 'type' => 'donneur'],

            // 2024
            ['trophy_edition_id' => $edition2024, 'company_id' => $rolex,     'rank' => 1, 'type' => 'donneur'],
            ['trophy_edition_id' => $edition2024, 'company_id' => $firmenich, 'rank' => 2, 'type' => 'donneur'],
            ['trophy_edition_id' => $edition2024, 'company_id' => $pictet,    'rank' => 3, 'type' => 'donneur'],

            // 2025
            ['trophy_edition_id' => $edition2025, 'company_id' => $pictet, 'rank' => 1, 'type' => 'donneur'],
            ['trophy_edition_id' => $edition2025, 'company_id' => $rolex,  'rank' => 2, 'type' => 'donneur'],
            ['trophy_edition_id' => $edition2025, 'company_id' => $patek,  'rank' => 3, 'type' => 'donneur'],
        ];

        foreach ($prizesData as $row) {
            DB::table('prizes')->updateOrInsert(
                [
                    'trophy_edition_id' => $row['trophy_edition_id'],
                    'rank' => $row['rank'],
                    'type' => $row['type'],
                ],
                [
                    'company_id' => $row['company_id'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }
}
