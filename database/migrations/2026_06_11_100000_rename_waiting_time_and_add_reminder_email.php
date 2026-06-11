<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rappel par email pour les inéligibilités à délai :
     * - reminder_at  = date à laquelle envoyer le rappel (ancien waiting_time)
     * - reminder_email = email saisi dans le formulaire
     * Une fois le rappel envoyé, les deux champs sont remis à NULL (pas de colonne « sent »).
     */
    public function up(): void
    {
        Schema::table('collections_users', function (Blueprint $table) {
            $table->renameColumn('waiting_time', 'reminder_at');
        });

        Schema::table('collections_users', function (Blueprint $table) {
            $table->string('reminder_email')->nullable()->after('reminder_at');
        });
    }

    public function down(): void
    {
        Schema::table('collections_users', function (Blueprint $table) {
            $table->dropColumn('reminder_email');
        });

        Schema::table('collections_users', function (Blueprint $table) {
            $table->renameColumn('reminder_at', 'waiting_time');
        });
    }
};
