<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminKpiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_read_available_kpis(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $company = Company::create([
            'name' => 'Entreprise test',
            'email' => 'contact@example.test',
            'slug' => 'entreprise-test',
            'employee_count' => 10,
            'source' => 'Entreprise partenaire',
        ]);

        User::factory()->count(4)->create([
            'role' => UserRole::User,
            'company_id' => $company->id,
        ]);

        $editionId = DB::table('trophy_editions')->insertGetId([
            'year' => 2026,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('prizes')->insert([
            'trophy_edition_id' => $editionId,
            'company_id' => $company->id,
            'rank' => 1,
            'type' => 'donneur',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $collectionId = DB::table('collections')->insertGetId([
            'company_id' => $company->id,
            'start' => '2026-06-15 09:00:00',
            'end' => '2026-06-15 16:30:00',
            'access_token' => 'testtoken2026',
            'linkOneDoc' => 'https://example.test/onedoc',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $users = User::where('role', UserRole::User->value)->take(4)->get();
        foreach ($users as $index => $user) {
            DB::table('collections_users')->insert([
                'collection_id' => $collectionId,
                'user_id' => $user->id,
                'abandonment' => $index === 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('sessions')->insert([
            [
                'id' => 'active-session-1',
                'user_id' => null,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Feature test',
                'payload' => '',
                'last_activity' => now()->subMinutes(2)->timestamp,
            ],
            [
                'id' => 'active-session-2',
                'user_id' => null,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Feature test',
                'payload' => '',
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => 'inactive-session',
                'user_id' => null,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Feature test',
                'payload' => '',
                'last_activity' => now()->subMinutes(8)->timestamp,
            ],
        ]);

        $this->actingAs($admin)
            ->getJson('/admin/api/kpis')
            ->assertOk()
            ->assertJsonPath('live.activeVisitors.value', 2)
            ->assertJsonPath('summary.registeredUsers.value', 4)
            ->assertJsonPath('summary.participationRate.value', 40)
            ->assertJsonPath('summary.labelledCompanies.value', 1)
            ->assertJsonPath('engagement.recommendedCompanies.value', 1)
            ->assertJsonPath('engagement.questionnaireAbandonRate.value', 25)
            ->assertJsonPath('summary.donationConversionRate.available', false);
    }
}
