<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'npa') && ! Schema::hasColumn('companies', 'zip_code')) {
                $table->renameColumn('npa', 'zip_code');
            }
            if (Schema::hasColumn('companies', 'localite') && ! Schema::hasColumn('companies', 'locality')) {
                $table->renameColumn('localite', 'locality');
            }
        });

        Schema::table('forms', function (Blueprint $table) {
            if (Schema::hasColumn('forms', 'npa') && ! Schema::hasColumn('forms', 'zip_code')) {
                $table->renameColumn('npa', 'zip_code');
            }
            if (Schema::hasColumn('forms', 'localite') && ! Schema::hasColumn('forms', 'locality')) {
                $table->renameColumn('localite', 'locality');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'zip_code') && ! Schema::hasColumn('companies', 'npa')) {
                $table->renameColumn('zip_code', 'npa');
            }
            if (Schema::hasColumn('companies', 'locality') && ! Schema::hasColumn('companies', 'localite')) {
                $table->renameColumn('locality', 'localite');
            }
        });

        Schema::table('forms', function (Blueprint $table) {
            if (Schema::hasColumn('forms', 'zip_code') && ! Schema::hasColumn('forms', 'npa')) {
                $table->renameColumn('zip_code', 'npa');
            }
            if (Schema::hasColumn('forms', 'locality') && ! Schema::hasColumn('forms', 'localite')) {
                $table->renameColumn('locality', 'localite');
            }
        });
    }
};
