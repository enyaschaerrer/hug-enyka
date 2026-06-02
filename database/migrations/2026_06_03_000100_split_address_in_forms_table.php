<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            if (! Schema::hasColumn('forms', 'npa')) {
                $table->string('npa', 10)->nullable()->after('address');
            }
            if (! Schema::hasColumn('forms', 'localite')) {
                $table->string('localite', 100)->nullable()->after('npa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            if (Schema::hasColumn('forms', 'localite')) {
                $table->dropColumn('localite');
            }
            if (Schema::hasColumn('forms', 'npa')) {
                $table->dropColumn('npa');
            }
        });
    }
};
