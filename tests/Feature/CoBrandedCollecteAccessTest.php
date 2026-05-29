<?php

namespace Tests\Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CoBrandedCollecteAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_cobranded_collection_is_accessible(): void
    {
        $this->travelTo('2026-06-15 12:00:00');
        $company = $this->createCompanyWithCollection(
            token: 'active-token',
            start: '2026-06-15 09:00:00',
            end: '2026-06-15 16:30:00',
        );

        $this->get('/collecte/' . $company->slug . '/active-token')
            ->assertOk();
    }

    public function test_not_expired_cobranded_collection_is_accessible_before_start_date(): void
    {
        $this->travelTo('2026-06-01 12:00:00');
        $company = $this->createCompanyWithCollection(
            token: 'future-token',
            start: '2026-06-15 09:00:00',
            end: '2026-06-15 16:30:00',
        );

        $this->get('/collecte/' . $company->slug . '/future-token')
            ->assertOk();
    }

    public function test_inactive_cobranded_collection_returns_404(): void
    {
        $this->travelTo('2026-06-15 12:00:00');
        $company = $this->createCompanyWithCollection(
            token: 'inactive-token',
            start: '2025-06-15 09:00:00',
            end: '2025-06-15 16:30:00',
        );

        $this->get('/collecte/' . $company->slug . '/inactive-token')
            ->assertNotFound();
    }

    private function createCompanyWithCollection(string $token, string $start, string $end): Company
    {
        $company = Company::create([
            'name' => 'Entreprise test',
            'email' => $token . '@example.test',
            'slug' => 'entreprise-' . $token,
        ]);

        DB::table('collections')->insert([
            'company_id' => $company->id,
            'start' => $start,
            'end' => $end,
            'access_token' => $token,
            'linkOneDoc' => 'https://example.test/onedoc',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $company;
    }
}
