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
            ['name' => 'Salt',    'slug' => 'salt',    'email' => 'demo@salt.test',    'source' => 'seed-home', 'primaryColor' => '#FF002B', 'secondaryColor' => '#1A1A1A', 'thirdColor' => '#F5F5F5'],
            ['name' => 'BCV',     'slug' => 'bcv',     'email' => 'demo@bcv.test',     'source' => 'seed-home', 'primaryColor' => '#009A47', 'secondaryColor' => '#004B27', 'thirdColor' => '#EAF6EE'],
            ['name' => 'Payot',   'slug' => 'payot',   'email' => 'demo@payot.test',   'source' => 'seed-home', 'primaryColor' => '#1A2552', 'secondaryColor' => '#8B7A4B', 'thirdColor' => '#F4F1EB'],
            ['name' => 'Migros',  'slug' => 'migros',  'email' => 'demo@migros.test',  'source' => 'seed-home', 'primaryColor' => '#FF6600', 'secondaryColor' => '#CC5200', 'thirdColor' => '#FFF1E5'],
            ['name' => 'Denner',  'slug' => 'denner',  'email' => 'demo@denner.test',  'source' => 'seed-home', 'primaryColor' => '#E30613', 'secondaryColor' => '#FFCC00', 'thirdColor' => '#FFF7CC'],
            ['name' => 'Samsung', 'slug' => 'samsung', 'email' => 'demo@samsung.test', 'source' => 'seed-home', 'primaryColor' => '#1428A0', 'secondaryColor' => '#000000', 'thirdColor' => '#E6E9F5'],
            ['name' => 'JF Auto', 'slug' => 'jf-auto', 'email' => 'demo@jfauto.test',  'source' => 'seed-home', 'primaryColor' => '#B00020', 'secondaryColor' => '#202020', 'thirdColor' => '#F7E8EA'],
            ['name' => 'Aplicom', 'slug' => 'aplicom', 'email' => 'demo@aplicom.test', 'source' => 'seed-home', 'primaryColor' => '#003D7A', 'secondaryColor' => '#6C8FB1', 'thirdColor' => '#EAF0F6'],
        ];

        foreach ($companiesData as $row) {
            Company::updateOrCreate(['email' => $row['email']], $row);
        }

        $salt    = Company::where('email', 'demo@salt.test')->value('id');
        $bcv     = Company::where('email', 'demo@bcv.test')->value('id');
        $payot   = Company::where('email', 'demo@payot.test')->value('id');
        $migros  = Company::where('email', 'demo@migros.test')->value('id');
        $denner  = Company::where('email', 'demo@denner.test')->value('id');
        $samsung = Company::where('email', 'demo@samsung.test')->value('id');
        $jfauto  = Company::where('email', 'demo@jfauto.test')->value('id');
        $aplicom = Company::where('email', 'demo@aplicom.test')->value('id');

        // ----- collections -----
        $collectionsData = [
            // Salt
            ['company_id' => $salt,    'start' => '2015-06-15 09:00:00', 'end' => '2015-06-15 16:30:00', 'access_token' => 'a3f8c1b2e7d9f041', 'linkOneDoc' => 'https://example.com/onedoc/a3f8c1b2e7d9f041'],
            ['company_id' => $salt,    'start' => '2017-06-15 09:00:00', 'end' => '2017-06-15 16:30:00', 'access_token' => 'b5e2d8a1c4f9e370', 'linkOneDoc' => 'https://example.com/onedoc/b5e2d8a1c4f9e370'],
            ['company_id' => $salt,    'start' => '2019-06-15 09:00:00', 'end' => '2019-06-15 16:30:00', 'access_token' => 'd9c1f4a3e8b6d520', 'linkOneDoc' => 'https://example.com/onedoc/d9c1f4a3e8b6d520'],
            ['company_id' => $salt,    'start' => '2021-06-15 09:00:00', 'end' => '2021-06-15 16:30:00', 'access_token' => 'e4a7d2b8c1f5e930', 'linkOneDoc' => 'https://example.com/onedoc/e4a7d2b8c1f5e930'],
            ['company_id' => $salt,    'start' => '2023-06-15 09:00:00', 'end' => '2023-06-15 16:30:00', 'access_token' => 'f2b6c8d4a1e9f870', 'linkOneDoc' => 'https://example.com/onedoc/f2b6c8d4a1e9f870'],
            ['company_id' => $salt,    'start' => '2025-06-15 09:00:00', 'end' => '2025-06-15 16:30:00', 'access_token' => 'c8d3a5f1b7e4c291', 'linkOneDoc' => 'https://example.com/onedoc/c8d3a5f1b7e4c291'],

            // BCV
            ['company_id' => $bcv,     'start' => '2020-06-15 09:00:00', 'end' => '2020-06-15 16:30:00', 'access_token' => 'a1e5d8b3c4f7a691', 'linkOneDoc' => 'https://example.com/onedoc/a1e5d8b3c4f7a691'],
            ['company_id' => $bcv,     'start' => '2022-06-15 09:00:00', 'end' => '2022-06-15 16:30:00', 'access_token' => 'b7c2f4a8d1e6c351', 'linkOneDoc' => 'https://example.com/onedoc/b7c2f4a8d1e6c351'],
            ['company_id' => $bcv,     'start' => '2024-06-15 09:00:00', 'end' => '2024-06-15 16:30:00', 'access_token' => 'd3a8f5c1b4e9d721', 'linkOneDoc' => 'https://example.com/onedoc/d3a8f5c1b4e9d721'],
            ['company_id' => $bcv,     'start' => '2026-06-15 09:00:00', 'end' => '2026-06-15 16:30:00', 'access_token' => 'e6b1c4f8a3d5e971', 'linkOneDoc' => 'https://example.com/onedoc/e6b1c4f8a3d5e971'],

            // Payot
            ['company_id' => $payot,   'start' => '2020-06-15 09:00:00', 'end' => '2020-06-15 16:30:00', 'access_token' => 'f4d2a8c5b1e7f391', 'linkOneDoc' => 'https://example.com/onedoc/f4d2a8c5b1e7f391'],
            ['company_id' => $payot,   'start' => '2024-06-15 09:00:00', 'end' => '2024-06-15 16:30:00', 'access_token' => 'c1e7b4f2a8d3c561', 'linkOneDoc' => 'https://example.com/onedoc/c1e7b4f2a8d3c561'],

            // Migros
            ['company_id' => $migros,  'start' => '2018-06-15 09:00:00', 'end' => '2018-06-15 16:30:00', 'access_token' => 'a8b3d5c1f4e2a781', 'linkOneDoc' => 'https://example.com/onedoc/a8b3d5c1f4e2a781'],
            ['company_id' => $migros,  'start' => '2020-06-15 09:00:00', 'end' => '2020-06-15 16:30:00', 'access_token' => 'b6f4c8a1d3e5b291', 'linkOneDoc' => 'https://example.com/onedoc/b6f4c8a1d3e5b291'],
            ['company_id' => $migros,  'start' => '2022-06-15 09:00:00', 'end' => '2022-06-15 16:30:00', 'access_token' => 'd2c8f4b1a5e7d961', 'linkOneDoc' => 'https://example.com/onedoc/d2c8f4b1a5e7d961'],
            ['company_id' => $migros,  'start' => '2024-06-15 09:00:00', 'end' => '2024-06-15 16:30:00', 'access_token' => 'e9a5d3f1b8c4e271', 'linkOneDoc' => 'https://example.com/onedoc/e9a5d3f1b8c4e271'],
            ['company_id' => $migros,  'start' => '2026-06-15 09:00:00', 'end' => '2026-06-15 16:30:00', 'access_token' => 'f7c4a8b2d5e1f681', 'linkOneDoc' => 'https://example.com/onedoc/f7c4a8b2d5e1f681'],

            // Denner
            ['company_id' => $denner,  'start' => '2017-06-15 09:00:00', 'end' => '2017-06-15 16:30:00', 'access_token' => 'c5d1b4a8f3e6c292', 'linkOneDoc' => 'https://example.com/onedoc/c5d1b4a8f3e6c292'],
            ['company_id' => $denner,  'start' => '2019-06-15 09:00:00', 'end' => '2019-06-15 16:30:00', 'access_token' => 'a4f8d3b1c7e5a961', 'linkOneDoc' => 'https://example.com/onedoc/a4f8d3b1c7e5a961'],
            ['company_id' => $denner,  'start' => '2025-06-15 09:00:00', 'end' => '2025-06-15 16:30:00', 'access_token' => 'b9c5d2f4a8e1b371', 'linkOneDoc' => 'https://example.com/onedoc/b9c5d2f4a8e1b371'],

            // Samsung
            ['company_id' => $samsung, 'start' => '2018-06-15 09:00:00', 'end' => '2018-06-15 16:30:00', 'access_token' => 'd7a3c8b1f5e2d461', 'linkOneDoc' => 'https://example.com/onedoc/d7a3c8b1f5e2d461'],
            ['company_id' => $samsung, 'start' => '2021-06-15 09:00:00', 'end' => '2021-06-15 16:30:00', 'access_token' => 'e1b8f4c5a3d6e971', 'linkOneDoc' => 'https://example.com/onedoc/e1b8f4c5a3d6e971'],
            ['company_id' => $samsung, 'start' => '2024-06-15 09:00:00', 'end' => '2024-06-15 16:30:00', 'access_token' => 'f5c2a8b3d1e4f871', 'linkOneDoc' => 'https://example.com/onedoc/f5c2a8b3d1e4f871'],
            ['company_id' => $samsung, 'start' => '2026-06-15 09:00:00', 'end' => '2026-06-15 16:30:00', 'access_token' => 'c8a4f1d5b2e7c361', 'linkOneDoc' => 'https://example.com/onedoc/c8a4f1d5b2e7c361'],

            // JF Auto
            ['company_id' => $jfauto,  'start' => '2026-06-15 09:00:00', 'end' => '2026-06-15 16:30:00', 'access_token' => 'a7d3c1b5f8e4a291', 'linkOneDoc' => 'https://example.com/onedoc/a7d3c1b5f8e4a291'],

            // Aplicom
            ['company_id' => $aplicom, 'start' => '2021-06-15 09:00:00', 'end' => '2021-06-15 16:30:00', 'access_token' => 'b4f1c8a3d5e2b761', 'linkOneDoc' => 'https://example.com/onedoc/b4f1c8a3d5e2b761'],
            ['company_id' => $aplicom, 'start' => '2023-06-15 09:00:00', 'end' => '2023-06-15 16:30:00', 'access_token' => 'd8a5c3f1b4e7d291', 'linkOneDoc' => 'https://example.com/onedoc/d8a5c3f1b4e7d291'],
            ['company_id' => $aplicom, 'start' => '2026-06-15 09:00:00', 'end' => '2026-06-15 16:30:00', 'access_token' => 'e3b7d2f5a8c4e162', 'linkOneDoc' => 'https://example.com/onedoc/e3b7d2f5a8c4e162'],
        ];

        foreach ($collectionsData as $row) {
            Collection::updateOrCreate(['access_token' => $row['access_token']], $row);
        }

        // ----- trophy_editions -----
        $trophyEditionsData = [
            ['year' => 2024],
            ['year' => 2025],
            ['year' => 2026],
        ];

        foreach ($trophyEditionsData as $row) {
            DB::table('trophy_editions')->updateOrInsert(
                ['year' => $row['year']],
                ['updated_at' => now(), 'created_at' => now()],
            );
        }

        $edition2024 = DB::table('trophy_editions')->where('year', 2024)->value('id');
        $edition2025 = DB::table('trophy_editions')->where('year', 2025)->value('id');
        $edition2026 = DB::table('trophy_editions')->where('year', 2026)->value('id');

        // ----- prizes -----
        $prizesData = [
            // 2024
            ['trophy_edition_id' => $edition2024, 'company_id' => $salt,    'rank' => 1, 'type' => 'donneur'],
            ['trophy_edition_id' => $edition2024, 'company_id' => $bcv,     'rank' => 2, 'type' => 'donneur'],
            ['trophy_edition_id' => $edition2024, 'company_id' => $migros,  'rank' => 3, 'type' => 'donneur'],

            // 2025
            ['trophy_edition_id' => $edition2025, 'company_id' => $denner,  'rank' => 1, 'type' => 'donneur'],
            ['trophy_edition_id' => $edition2025, 'company_id' => $salt,    'rank' => 2, 'type' => 'donneur'],
            ['trophy_edition_id' => $edition2025, 'company_id' => $payot,   'rank' => 3, 'type' => 'donneur'],

            // 2026
            ['trophy_edition_id' => $edition2026, 'company_id' => $bcv,     'rank' => 1, 'type' => 'donneur'],
            ['trophy_edition_id' => $edition2026, 'company_id' => $samsung, 'rank' => 2, 'type' => 'donneur'],
            ['trophy_edition_id' => $edition2026, 'company_id' => $aplicom, 'rank' => 3, 'type' => 'donneur'],
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
