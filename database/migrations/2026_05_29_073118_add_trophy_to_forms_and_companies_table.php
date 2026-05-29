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
        Schema::table('forms', function (Blueprint $table) {
            $table->boolean('trophy')->default(false)->after('message');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('trophy')->default(false)->after('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('trophy');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('trophy');
        });
    }
};
