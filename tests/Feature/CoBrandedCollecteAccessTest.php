<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Mail\CoBrandedAccessCodeMail;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

    public function test_employee_can_request_access_code_for_allowed_domain(): void
    {
        Mail::fake();
        $this->travelTo('2026-06-15 12:00:00');

        $company = $this->createCompanyWithCollection(
            token: 'active-token',
            start: '2026-06-15 09:00:00',
            end: '2026-06-15 16:30:00',
        );

        $this->postJson('/collecte/' . $company->slug . '/active-token/access-code', [
            'email' => 'personne@example.com',
        ])->assertOk();

        $user = User::where('email', 'personne@example.com')->firstOrFail();
        $this->assertSame(UserRole::User, $user->role);
        $this->assertSame($company->id, $user->company_id);

        $this->assertDatabaseHas('collections_users', [
            'user_id' => $user->id,
        ]);

        Mail::assertSent(CoBrandedAccessCodeMail::class);
    }

    public function test_employee_access_code_rejects_unallowed_domain(): void
    {
        Mail::fake();
        $this->travelTo('2026-06-15 12:00:00');

        $company = $this->createCompanyWithCollection(
            token: 'active-token',
            start: '2026-06-15 09:00:00',
            end: '2026-06-15 16:30:00',
        );

        $this->postJson('/collecte/' . $company->slug . '/active-token/access-code', [
            'email' => 'personne@other.test',
        ])->assertUnprocessable();

        Mail::assertNothingSent();
    }

    public function test_employee_can_login_after_being_attached_to_collection(): void
    {
        $this->travelTo('2026-06-15 12:00:00');

        $company = $this->createCompanyWithCollection(
            token: 'active-token',
            start: '2026-06-15 09:00:00',
            end: '2026-06-15 16:30:00',
        );
        $collectionId = DB::table('collections')->where('access_token', 'active-token')->value('id');
        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Personne test',
            'email' => 'personne@example.com',
            'professional_email' => 'personne@example.com',
            'password' => 'ABC123',
            'role' => UserRole::User,
            'email_validated' => true,
        ]);
        DB::table('collections_users')->insert([
            'collection_id' => $collectionId,
            'user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/collecte/' . $company->slug . '/active-token/login', [
            'email' => 'personne@example.com',
            'password' => 'ABC123',
        ])->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_access_cobranded_collection_without_employee_auth(): void
    {
        $this->travelTo('2026-06-15 12:00:00');

        $company = $this->createCompanyWithCollection(
            token: 'active-token',
            start: '2026-06-15 09:00:00',
            end: '2026-06-15 16:30:00',
        );
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'professional_email' => 'admin@example.test',
            'password' => 'password',
            'role' => UserRole::Admin,
            'email_validated' => true,
        ]);

        $this->actingAs($admin)
            ->get('/collecte/' . $company->slug . '/active-token')
            ->assertOk()
            ->assertSee('"canAccess":true', false);
    }

    private function createCompanyWithCollection(string $token, string $start, string $end): Company
    {
        $company = Company::create([
            'name' => 'Entreprise test',
            'email' => $token . '@example.test',
            'slug' => 'entreprise-' . $token,
            'allowed_email_domains' => 'example.com',
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
