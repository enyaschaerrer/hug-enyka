<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('collections_users', function (Blueprint $table) {
            $table->enum('quiz_step', ['quiz', 'chat', 'done'])->default('done')->after('user_id');
            $table->boolean('clicked_onedoc')->default(false)->after('quiz_step');
            $table->boolean('connected')->default(false)->after('clicked_onedoc');
            $table->dateTime('waiting_time')->nullable()->after('connected');

            $table->dropColumn('abandonment');
        });

        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();
            $table->string('ip_hash', 64);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_visits');

        Schema::table('collections_users', function (Blueprint $table) {
            $table->boolean('abandonment')->default(false)->after('user_id');

            $table->dropColumn(['quiz_step', 'clicked_onedoc', 'connected', 'waiting_time']);
        });
    }
};
