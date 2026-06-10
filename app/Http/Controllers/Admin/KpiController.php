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
        $publicLabelled = DB::table('companies')
            ->where('is_public', true)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('collections')
                    ->whereColumn('collections.company_id', 'companies.id');
            })
            ->count();

        $anonymousCompanies = DB::table('companies')
            ->where('is_public', false)
            ->count();

        $labelledCompanies = $publicLabelled + $anonymousCompanies;


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

        $connectedTotal = DB::table('collections_users')->where('connected', 1)->distinct('user_id')->count('user_id');
        $clickedTotal   = DB::table('collections_users')->where('clicked_onedoc', 1)->count();

        $conversionByCompany = DB::table('companies')
            ->join('collections', 'collections.company_id', '=', 'companies.id')
            ->leftJoin('collections_users', 'collections_users.collection_id', '=', 'collections.id')
            ->select(
                'companies.name',
                'companies.primaryColor',
                DB::raw('COUNT(DISTINCT CASE WHEN collections_users.connected = 1 THEN collections_users.user_id END) as connected_count'),
                DB::raw('COUNT(DISTINCT CASE WHEN collections_users.clicked_onedoc = 1 THEN collections_users.user_id END) as clicked_count'),
            )
            ->groupBy('companies.id', 'companies.name', 'companies.primaryColor')
            ->get()
            ->map(function ($c) {
                $rate = $c->connected_count > 0
                    ? (int) round($c->clicked_count / $c->connected_count * 100)
                    : null;

                return [
                    'name'         => $c->name,
                    'primaryColor' => $c->primaryColor ?? '#888888',
                    'connected'    => (int) $c->clicked_count,
                    'total'        => (int) $c->connected_count,
                    'rate'         => $rate,
                ];
            })
            ->sortByDesc('rate')
            ->values();

        $quizStepCounts = DB::table('collections_users')
            ->select('quiz_step', DB::raw('count(*) as total'))
            ->where('quiz_step', '!=', 'pending')
            ->groupBy('quiz_step')
            ->pluck('total', 'quiz_step');

        $quizTotal   = $quizStepCounts->sum();
        $quizAbandons = ($quizStepCounts->get('quiz') ?? 0) + ($quizStepCounts->get('chat') ?? 0);

        $abandonByCompany = DB::table('companies')
            ->join('collections', 'collections.company_id', '=', 'companies.id')
            ->join('collections_users', 'collections_users.collection_id', '=', 'collections.id')
            ->select(
                'companies.name',
                'companies.primaryColor',
                DB::raw("COUNT(DISTINCT CASE WHEN collections_users.quiz_step = 'quiz' THEN collections_users.user_id END) as quiz_count"),
                DB::raw("COUNT(DISTINCT CASE WHEN collections_users.quiz_step = 'chat' THEN collections_users.user_id END) as chat_count"),
                DB::raw("COUNT(DISTINCT CASE WHEN collections_users.quiz_step IN ('quiz', 'chat') THEN collections_users.user_id END) as abandon_count"),
                DB::raw("COUNT(DISTINCT CASE WHEN collections_users.quiz_step != 'pending' THEN collections_users.user_id END) as total_count"),
            )
            ->groupBy('companies.id', 'companies.name', 'companies.primaryColor')
            ->get()
            ->map(function ($c) {
                $rate = $c->total_count > 0
                    ? (int) round($c->abandon_count / $c->total_count * 100)
                    : null;
                return [
                    'name'         => $c->name,
                    'primaryColor' => $c->primaryColor ?? '#888888',
                    'connected'    => (int) $c->abandon_count,
                    'total'        => (int) $c->total_count,
                    'rate'         => $rate,
                    'quizRate'     => $c->total_count > 0 ? (int) round($c->quiz_count / $c->total_count * 100) : null,
                    'chatRate'     => $c->total_count > 0 ? (int) round($c->chat_count / $c->total_count * 100) : null,
                ];
            })
            ->sortByDesc('rate')
            ->values();

        $abandonSteps = [
            'total'     => $quizTotal,
            'totalRate' => $quizTotal > 0 ? (int) round($quizAbandons / $quizTotal * 100) : null,
            'steps'     => [
                [
                    'label' => 'Abandon au quiz',
                    'count' => (int) ($quizStepCounts->get('quiz') ?? 0),
                    'rate'  => $quizTotal > 0 ? (int) round(($quizStepCounts->get('quiz') ?? 0) / $quizTotal * 100) : null,
                ],
                [
                    'label' => 'Abandon au chat',
                    'count' => (int) ($quizStepCounts->get('chat') ?? 0),
                    'rate'  => $quizTotal > 0 ? (int) round(($quizStepCounts->get('chat') ?? 0) / $quizTotal * 100) : null,
                ],
            ],
        ];

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
            ->map(fn ($count, $source) => ['source' => $source, 'count' => $count])
            ->sortByDesc('count')
            ->values();

        $freeTextSources = collect();

        return response()->json([
            'engagement' => [
                'labelledCompanies' => [
                    'label'          => 'Entreprises labellisées',
                    'value'          => $labelledCompanies,
                    'available'      => true,
                    'note'           => null,
                    'publicCount'    => $publicLabelled,
                    'anonymousCount' => $anonymousCompanies,
                ],
                'companySources' => [
                    'label' => 'Sources de prise de contact',
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
                    'label'     => 'Collaborateurs s\'étant connectés',
                    'value'     => $connectedTotal,
                    'available' => true,
                    'note'      => null,
                    'companies' => $participationByCompany->sortByDesc('connected')->values(),
                ],
                'participationRate' => [
                    'label'     => 'Taux d\'inscription des collaborateurs',
                    'value'     => $this->globalRateFromCompanies($participationByCompany),
                    'available' => true,
                    'note'      => null,
                    'companies' => $participationByCompany,
                ],
                'conversionRate' => [
                    'label'     => 'Prise de rendez-vous OneDoc',
                    'value'     => $this->globalRateFromCompanies($conversionByCompany),
                    'available' => $connectedTotal > 0,
                    'note'      => $connectedTotal > 0 ? $clickedTotal . ' clics / ' . $connectedTotal . ' connectés.' : 'Aucun connecté pour le moment.',
                    'companies' => $conversionByCompany,
                ],
                'questionnaireAbandonRate' => [
                    'label'        => 'Taux d\'abandon au questionnaire',
                    'value'        => $abandonSteps['totalRate'],
                    'available'    => $abandonSteps['total'] > 0,
                    'note'         => $abandonSteps['total'] > 0 ? null : 'Aucun parcours enregistré.',
                    'abandonSteps' => $abandonSteps['steps'],
                    'companies'    => $abandonByCompany,
                ],
            ],
        ]);
    }

    private function globalRateFromCompanies(\Illuminate\Support\Collection $companies): ?float
    {
        $totalConnected = $companies->sum('connected');
        $totalEmployees = $companies->sum('total');

        return $totalEmployees > 0
            ? round($totalConnected / $totalEmployees * 100, 1)
            : null;
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
