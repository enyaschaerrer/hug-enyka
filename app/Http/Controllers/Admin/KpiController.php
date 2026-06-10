<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $participationByCompany = DB::table('companies')
            ->join('collections', 'collections.company_id', '=', 'companies.id')
            ->leftJoin('collections_users', 'collections_users.collection_id', '=', 'collections.id')
            ->select(
                'companies.id',
                'companies.name',
                'companies.primaryColor',
                'companies.employee_count',
                DB::raw('COUNT(DISTINCT CASE WHEN collections_users.connected = 1 THEN collections_users.user_id END) as connected_count'),
            )
            ->groupBy('companies.id', 'companies.name', 'companies.logo', 'companies.employee_count')
            ->get()
            ->map(function ($c) {
                $rate = $c->employee_count > 0
                    ? (int) round(($c->connected_count / $c->employee_count) * 100)
                    : null;

                return [
                    'name'         => $c->name,
                    'primaryColor' => $c->primaryColor ?? '#888888',
                    'connected'    => (int) $c->connected_count,
                    'total'        => (int) $c->employee_count,
                    'rate'         => $rate,
                ];
            })
            ->sortByDesc('rate')
            ->values();

        $predefinedOptions = [
            'Réseaux sociaux',
            'Recherche web / site HUG',
            'Recommandation d\'une entreprise',
            'Recommandation d\'un collaborateur',
            'Contact direct des HUG / CTS',
            'Événement / présentation',
            'Bouche à oreille',
        ];

        $sourcesBreakdown = DB::table('companies')
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->select('source', DB::raw('count(*) as total'))
            ->groupBy('source')
            ->orderByDesc('total')
            ->pluck('total', 'source');

        $predefinedSources = $sourcesBreakdown
            ->filter(fn ($count, $source) => in_array($source, $predefinedOptions))
            ->map(fn ($count, $source) => ['source' => $source, 'count' => $count])
            ->values();

        $freeTextSources = $sourcesBreakdown
            ->filter(fn ($count, $source) => ! in_array($source, $predefinedOptions))
            ->keys()
            ->values();

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
                    'note' => $sourcesBreakdown->isEmpty() ? 'Aucune source renseignée pour le moment.' : null,
                    'predefined' => $predefinedSources,
                    'freeText' => $freeTextSources,
                ],
                'pageVisits' => [
                    'label' => 'Visites du site public',
                    'value' => null,
                    'available' => true,
                    'note' => null,
                ],
                'connectedUsers' => [
                    'label'     => 'Collaborateurs connectés',
                    'value'     => DB::table('collections_users')->where('connected', 1)->distinct('user_id')->count('user_id'),
                    'available' => true,
                    'note'      => 'Ont accédé au site co-brandé au moins une fois.',
                ],
                'participationRate' => [
                    'label' => 'Taux de participation',
                    'value' => null,
                    'available' => true,
                    'note' => null,
                    'companies' => $participationByCompany,
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

    public function pageVisits(Request $request): JsonResponse
    {
        $period = $request->string('period', '30d')->toString();

        $from = match ($period) {
            '3m'   => now()->subMonths(3),
            '6m'   => now()->subMonths(6),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $count = DB::table('page_visits')
            ->where('created_at', '>=', $from)
            ->count();

        return response()->json(['count' => $count]);
    }
}
