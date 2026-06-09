<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class KpiController extends Controller
{
    public function index(): JsonResponse
    {
        $activeVisitors = DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes(5)->timestamp)
            ->count();

        $registeredUsers = User::query()
            ->where('role', UserRole::User->value)
            ->count();

        $totalCollaborators = (int) Company::query()->sum('employee_count');
        $participationRate = $this->percentage($registeredUsers, $totalCollaborators);

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
            'live' => [
                'activeVisitors' => [
                    'label' => 'Nombre d\'utilisateurs connectés',
                    'value' => $activeVisitors,
                    'available' => true,
                    'note' => 'Sessions actives sur les 5 dernières minutes.',
                ],
            ],
            'summary' => [
                'registeredUsers' => [
                    'label' => 'Inscrits',
                    'value' => $registeredUsers,
                    'available' => true,
                    'note' => 'Comptes collaborateurs en base.',
                ],
                'participationRate' => [
                    'label' => 'Participation',
                    'value' => $participationRate,
                    'available' => $participationRate !== null,
                    'note' => $participationRate === null
                        ? 'Renseigner le nombre de collaborateurs des entreprises.'
                        : $registeredUsers . ' inscrits / ' . $totalCollaborators . ' collaborateurs.',
                ],
                'donationConversionRate' => [
                    'label' => 'Conversion vers don',
                    'value' => null,
                    'available' => false,
                    'note' => 'Pas encore de table ou événement de dons effectifs.',
                ],
                'labelledCompanies' => [
                    'label' => 'Entreprises labellisées',
                    'value' => $labelledCompanies,
                    'available' => true,
                    'note' => 'Entreprises présentes dans les prix/trophées.',
                ],
            ],
            'funnel' => [
                [
                    'label' => 'Inscrits',
                    'value' => $registeredUsers,
                    'rate' => 100,
                    'available' => true,
                ],
                [
                    'label' => 'Présents le jour J',
                    'value' => null,
                    'rate' => null,
                    'available' => false,
                    'note' => 'Aucun statut de présence n\'est encore stocké.',
                ],
                [
                    'label' => 'Dons effectifs',
                    'value' => null,
                    'rate' => null,
                    'available' => false,
                    'note' => 'Aucune donnée de don effectif n\'est encore stockée.',
                ],
            ],
            'engagement' => [
                'labelledCompanies' => [
                    'label' => 'Entreprises labellisées',
                    'value' => $labelledCompanies,
                    'available' => true,
                    'note' => $anonymousCompanies > 0
                        ? $anonymousCompanies . ' en participation anonyme.'
                        : 'Aucune en participation anonyme.',
                    'tone' => 'success',
                ],
                'companySources' => [
                    'label' => 'Sources des entreprises',
                    'value' => $sourcesBreakdown->count(),
                    'available' => $sourcesBreakdown->isNotEmpty(),
                    'note' => $sourcesBreakdown->isEmpty()
                        ? 'Aucune source renseignée pour le moment.'
                        : $sourcesList,
                    'tone' => 'success',
                ],
                'pageVisits' => [
                    'label' => 'Visites du site public',
                    'value' => null,
                    'available' => false,
                    'note' => 'Nécessite la migration de la table page_visits.',
                    'tone' => 'success',
                ],
                'participationRate' => [
                    'label' => 'Taux de participation',
                    'value' => null,
                    'available' => false,
                    'note' => 'Nécessite la migration first_connected_at sur collections_users.',
                    'tone' => 'success',
                ],
                'conversionRate' => [
                    'label' => 'Conversion connexion → inscription',
                    'value' => null,
                    'available' => false,
                    'note' => 'Tracking des clics OneDoc non encore implémenté.',
                    'tone' => 'success',
                ],
                'questionnaireAbandonRate' => [
                    'label' => 'Taux d\'abandon questionnaire',
                    'value' => null,
                    'available' => false,
                    'note' => 'Tracking du questionnaire non encore implémenté.',
                    'tone' => 'warning',
                ],
            ],
        ]);
    }

    private function percentage(int $value, int $total): ?int
    {
        if ($total <= 0) {
            return null;
        }

        return (int) round(($value / $total) * 100);
    }
}
