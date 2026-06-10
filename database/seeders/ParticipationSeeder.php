<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParticipationSeeder extends Seeder
{
    public function run(): void
    {
        $companies = DB::table('companies')
            ->whereNotNull('allowed_email_domains')
            ->get();

        foreach ($companies as $company) {
            $collection = DB::table('collections')
                ->where('company_id', $company->id)
                ->orderByDesc('start')
                ->first();

            if (! $collection) {
                continue;
            }

            $domain         = trim(explode(',', $company->allowed_email_domains)[0]);
            $userCount      = min(25, max(5, (int) ($company->employee_count * 0.04)));
            $connectedRatio = fake()->numberBetween(25, 90) / 100;

            for ($i = 0; $i < $userCount; $i++) {
                $email = "seed.participant.{$company->id}.{$i}@{$domain}";

                $user = User::updateOrCreate(
                    ['professional_email' => $email],
                    [
                        'name'               => fake()->name(),
                        'email'              => $email,
                        'professional_email' => $email,
                        'email_validated'    => true,
                        'role'               => UserRole::User,
                        'company_id'         => $company->id,
                    ],
                );

                $connected = ($i / $userCount) < $connectedRatio;

                // Distribution quiz_step : 15% abandon quiz, 10% abandon chat, 75% done
                $rand     = fake()->numberBetween(1, 100);
                $quizStep = match (true) {
                    $rand <= 15  => 'quiz',
                    $rand <= 25  => 'chat',
                    default      => 'done',
                };

                // clicked_onedoc : ~40% des connectés ayant complété le parcours
                $clickedOnedoc = $connected && $quizStep === 'done' && fake()->boolean(40);

                DB::table('collections_users')->updateOrInsert(
                    ['collection_id' => $collection->id, 'user_id' => $user->id],
                    [
                        'quiz_step'      => $quizStep,
                        'clicked_onedoc' => $clickedOnedoc ? 1 : 0,
                        'connected'      => $connected ? 1 : 0,
                        'waiting_time'   => null,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ],
                );
            }
        }
    }
}
