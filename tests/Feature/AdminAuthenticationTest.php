<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_is_accessible(): void
    {
        $this->get('/admin/login')
            ->assertOk();
    }

    public function test_guest_is_redirected_to_admin_login_when_visiting_admin_dashboard(): void
    {
        $this->get('/admin')
            ->assertRedirect('/admin/login');
    }

    public function test_superadmin_can_login_and_access_admin_dashboard(): void
    {
        $superAdmin = User::factory()->create([
            'email' => 'superadmin@example.com',
            'role' => UserRole::SuperAdmin,
        ]);

        $this->from('/admin/login')
            ->post('/admin/login', [
                'email' => 'superadmin@example.com',
                'password' => 'password',
            ])
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($superAdmin);

        $this->get('/admin')
            ->assertOk();
    }

    public function test_user_role_cannot_login_to_admin(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'role' => UserRole::User,
        ]);

        $this->from('/admin/login')
            ->post('/admin/login', [
                'email' => 'user@example.com',
                'password' => 'password',
            ])
            ->assertRedirect('/admin/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_logout(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)
            ->post('/admin/logout')
            ->assertRedirect('/admin/login');

        $this->assertGuest();
    }
}
