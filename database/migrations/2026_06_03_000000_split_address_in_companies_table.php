<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'npa')) {
                $table->string('npa', 10)->nullable()->after('address');
            }
            if (! Schema::hasColumn('companies', 'localite')) {
                $table->string('localite', 100)->nullable()->after('npa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'localite')) {
                $table->dropColumn('localite');
            }
            if (Schema::hasColumn('companies', 'npa')) {
                $table->dropColumn('npa');
            }
        });
    }
};
