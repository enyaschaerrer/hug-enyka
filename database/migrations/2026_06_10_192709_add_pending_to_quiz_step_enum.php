<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE collections_users MODIFY COLUMN quiz_step ENUM('pending', 'quiz', 'chat', 'done') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("UPDATE collections_users SET quiz_step = 'done' WHERE quiz_step = 'pending'");
        DB::statement("ALTER TABLE collections_users MODIFY COLUMN quiz_step ENUM('quiz', 'chat', 'done') NOT NULL DEFAULT 'done'");
    }
};
