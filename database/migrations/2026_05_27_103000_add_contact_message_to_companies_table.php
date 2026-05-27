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
        if (! Schema::hasColumn('companies', 'contact_message')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->text('contact_message')->nullable()->after('email');
            });
        }

        if (Schema::hasColumn('collections', 'month') && Schema::hasColumn('collections', 'year')) {
            Schema::table('collections', function (Blueprint $table) {
                $table->index('company_id');
                $table->dropUnique(['company_id', 'month', 'year']);
                $table->dropColumn(['month', 'year']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('collections', 'month') && ! Schema::hasColumn('collections', 'year')) {
            Schema::table('collections', function (Blueprint $table) {
                $table->unsignedTinyInteger('month')->nullable()->after('end');
                $table->unsignedInteger('year')->nullable()->after('month');
                $table->unique(['company_id', 'month', 'year']);
                $table->dropIndex(['company_id']);
            });
        }

        if (Schema::hasColumn('companies', 'contact_message')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('contact_message');
            });
        }
    }
};
