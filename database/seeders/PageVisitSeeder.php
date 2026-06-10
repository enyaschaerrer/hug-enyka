<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PageVisitSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('page_visits')->truncate();

        // Répartition sur les 8 derniers mois, volume croissant vers le mois en cours
        $volumes = [18, 24, 31, 45, 52, 67, 89, 104];

        foreach ($volumes as $i => $count) {
            $monthStart = now()->startOfMonth()->subMonths(count($volumes) - 1 - $i);

            $rows = [];
            for ($j = 0; $j < $count; $j++) {
                $visitedAt = $monthStart->copy()->addSeconds(random_int(0, $monthStart->daysInMonth * 86400 - 1));
                $rows[] = [
                    'ip_hash'    => hash('sha256', fake()->ipv4() . $j . $i),
                    'created_at' => $visitedAt,
                    'updated_at' => $visitedAt,
                ];
            }

            DB::table('page_visits')->insert($rows);
        }
    }
}
