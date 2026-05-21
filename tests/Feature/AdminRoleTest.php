<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_role_is_cast_to_enum(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::SuperAdmin,
        ]);

        $this->assertSame(UserRole::SuperAdmin, $user->fresh()->role);
    }

    public function test_role_middleware_allows_expected_roles(): void
    {
        Route::middleware('role:superadmin,admin')->get('/test-admin-access', fn () => 'ok');

        $superAdmin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($superAdmin)
            ->get('/test-admin-access')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/test-admin-access')
            ->assertOk();
    }

    public function test_role_middleware_rejects_unauthorized_roles(): void
    {
        Route::middleware('role:superadmin,admin')->get('/test-admin-access-denied', fn () => 'ok');

        $user = User::factory()->create([
            'role' => UserRole::User,
        ]);

        $this->actingAs($user)
            ->get('/test-admin-access-denied')
            ->assertForbidden();
    }

    public function test_database_seeder_creates_superadmin_user(): void
    {
        $this->seed(DatabaseSeeder::class);

        $superAdmin = User::where('email', 'superadmin@example.com')->first();

        $this->assertNotNull($superAdmin);
        $this->assertSame('Super Admin', $superAdmin->name);
        $this->assertSame(UserRole::SuperAdmin, $superAdmin->role);
    }
}
