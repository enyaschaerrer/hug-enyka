<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_list_admin_accounts(): void
    {
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        User::factory()->create(['email' => 'admin@example.com', 'role' => UserRole::Admin]);
        User::factory()->create(['email' => 'employee@example.com', 'role' => UserRole::User]);

        $this->actingAs($superAdmin)
            ->getJson('/admin/api/accounts')
            ->assertOk()
            ->assertJsonFragment(['email' => 'admin@example.com'])
            ->assertJsonMissing(['email' => 'employee@example.com']);
    }

    public function test_admin_cannot_manage_accounts(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->getJson('/admin/api/accounts')
            ->assertForbidden();

        $this->actingAs($admin)
            ->get('/admin/comptes')
            ->assertForbidden();
    }

    public function test_superadmin_can_create_admin_account(): void
    {
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($superAdmin)
            ->postJson('/admin/accounts', [
                'email' => 'new-admin@example.com',
                'password' => 'secret-password',
                'role' => UserRole::Admin->value,
            ])
            ->assertCreated()
            ->assertJsonPath('account.email', 'new-admin@example.com')
            ->assertJsonPath('account.role', UserRole::Admin->value);

        $created = User::where('email', 'new-admin@example.com')->firstOrFail();
        $this->assertSame('new-admin@example.com', $created->name);
        $this->assertSame('new-admin@example.com', $created->professional_email);
        $this->assertSame(UserRole::Admin, $created->role);
        $this->assertTrue($created->email_validated);
        $this->assertTrue(Hash::check('secret-password', $created->password));
    }

    public function test_last_superadmin_cannot_be_demoted_or_deleted(): void
    {
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($superAdmin)
            ->patchJson('/admin/accounts/' . $superAdmin->id, [
                'email' => $superAdmin->email,
                'password' => '',
                'role' => UserRole::Admin->value,
            ])
            ->assertUnprocessable();

        $this->actingAs($superAdmin)
            ->deleteJson('/admin/accounts/' . $superAdmin->id)
            ->assertUnprocessable();
    }
}
