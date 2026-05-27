<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate([
            'email' => 'superadmin@example.com',
        ], [
            'name' => 'Super Admin',
            'professional_email' => 'superadmin@example.com',
            'email_validated' => true,
            'password' => Hash::make('password'),
            'role' => UserRole::SuperAdmin,
        ]);
    }
}
