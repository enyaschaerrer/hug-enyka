<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class KpiController extends Controller
{
    public function index(): JsonResponse
    {
        $labelledCompanies = DB::table('companies')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('collections')
                    ->whereColumn('collections.company_id', 'companies.id');
            })
            ->count();

        $anonymousCompanies = DB::table('companies')
            ->where('is_public', false)
            ->count();

        $sourcesBreakdown = DB::table('companies')
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->select('source', DB::raw('count(*) as total'))
            ->groupBy('source')
            ->orderByDesc('total')
            ->pluck('total', 'source');

        $sourcesList = $sourcesBreakdown->map(fn ($count, $source) => $source . ' (' . $count . ')')
            ->values()
            ->join(', ');

        return response()->json([
            'engagement' => [
                'labelledCompanies' => [
                    'label' => 'Entreprises labellisées',
                    'value' => $labelledCompanies,
                    'available' => true,
                    'note' => $anonymousCompanies > 0
                        ? $anonymousCompanies . ' en participation anonyme.'
                        : 'Aucune en participation anonyme.',
                ],
                'companySources' => [
                    'label' => 'Sources des entreprises',
                    'value' => $sourcesBreakdown->count(),
                    'available' => $sourcesBreakdown->isNotEmpty(),
                    'note' => $sourcesBreakdown->isEmpty()
                        ? 'Aucune source renseignée pour le moment.'
                        : $sourcesList,
                ],
                'pageVisits' => [
                    'label' => 'Visites du site public',
                    'value' => null,
                    'available' => false,
                    'note' => 'Migration non encore appliquée (table page_visits).',
                ],
                'participationRate' => [
                    'label' => 'Taux de participation',
                    'value' => null,
                    'available' => false,
                    'note' => 'Migration non encore appliquée (colonne connected).',
                ],
                'conversionRate' => [
                    'label' => 'Conversion connexion → inscription',
                    'value' => null,
                    'available' => false,
                    'note' => 'Migration non encore appliquée (colonne clicked_onedoc).',
                ],
                'questionnaireAbandonRate' => [
                    'label' => 'Taux d\'abandon questionnaire',
                    'value' => null,
                    'available' => false,
                    'note' => 'Migration non encore appliquée (colonne quiz_step).',
                ],
            ],
        ]);
    }
}
