<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Déplace les logos d'entreprises de public/img/companies vers storage/app/public/companies
     * et met à jour le chemin stocké en base (/img/companies/... → /storage/companies/...).
     *
     * On copie (pas move) : les fichiers d'origine restent en place, la migration est ré-exécutable
     * sans risque, et le déploiement prod (qui contient encore /img/companies) fonctionne pareil.
     */
    public function up(): void
    {
        $companies = DB::table('companies')
            ->whereNotNull('logo')
            ->where('logo', 'like', '/img/companies/%')
            ->get(['id', 'logo']);

        $destDir = storage_path('app/public/companies');
        if (! is_dir($destDir)) {
            @mkdir($destDir, 0775, true);
        }

        foreach ($companies as $company) {
            $filename = basename($company->logo);
            $source = public_path('img/companies/' . $filename);
            $dest = $destDir . '/' . $filename;

            if (file_exists($source) && ! file_exists($dest)) {
                @copy($source, $dest);
            }

            // On ne bascule le chemin BDD que si le fichier est bien disponible dans storage.
            if (file_exists($dest)) {
                DB::table('companies')
                    ->where('id', $company->id)
                    ->update(['logo' => '/storage/companies/' . $filename]);
            }
        }
    }

    /**
     * Re-pointe vers /img/companies (les fichiers d'origine y sont restés).
     */
    public function down(): void
    {
        DB::table('companies')
            ->where('logo', 'like', '/storage/companies/%')
            ->get(['id', 'logo'])
            ->each(function ($company) {
                DB::table('companies')
                    ->where('id', $company->id)
                    ->update(['logo' => '/img/companies/' . basename($company->logo)]);
            });
    }
};
