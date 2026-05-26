<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('email')->unique();
            $table->string('telephone', 50)->nullable();
            $table->unsignedInteger('employee_count')->nullable();
            $table->string('slug', 20)->unique()->nullable();
            $table->string('allowed_email_domains', 255)->nullable();
            $table->string('source')->nullable();
            $table->string('primaryColor')->nullable();
            $table->string('secondaryColor')->nullable();
            $table->string('thirdColor')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->string('professional_email')->nullable()->unique()->after('email');
            $table->boolean('email_validated')->default(false)->after('role');
        });

        DB::table('users')
            ->whereNull('professional_email')
            ->update(['professional_email' => DB::raw('email')]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('professional_email')->nullable(false)->change();
            $table->string('password')->nullable()->change();
        });

        Schema::create('trophy_editions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('year')->unique();
            $table->timestamps();
        });

        Schema::create('prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trophy_edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('rank');
            $table->enum('type', ['prixJury', 'ambassadeur', 'donneur']);
            $table->timestamps();

            $table->unique(['trophy_edition_id', 'company_id', 'type']);
            $table->unique(['trophy_edition_id', 'rank', 'type']);
        });

        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->dateTime('start');
            $table->dateTime('end');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('year');
            $table->string('access_token')->unique();
            $table->string('linkOneDoc');
            $table->timestamps();

            $table->unique(['company_id', 'month', 'year']);
        });

        Schema::create('collections_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('abandonment')->default(false);
            $table->timestamps();

            $table->unique(['collection_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections_users');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('prices');
        Schema::dropIfExists('trophy_editions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropUnique(['professional_email']);
            $table->dropColumn(['company_id', 'professional_email', 'email_validated']);
            $table->string('password')->nullable(false)->change();
        });

        Schema::dropIfExists('companies');
    }
};
