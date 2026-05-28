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
        $registeredUsers = User::query()
            ->where('role', UserRole::User->value)
            ->count();

        $totalCollaborators = (int) Company::query()->sum('employee_count');
        $participationRate = $this->percentage($registeredUsers, $totalCollaborators);

        $labelledCompanies = DB::table('prizes')
            ->distinct('company_id')
            ->count('company_id');

        $recommendedCompanies = Company::query()
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->count();

        $questionnaireRows = DB::table('collections_users')->count();
        $questionnaireAbandonments = DB::table('collections_users')
            ->where('abandonment', true)
            ->count();

        return response()->json([
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
                    'note' => 'Aucun statut de présence n’est encore stocké.',
                ],
                [
                    'label' => 'Dons effectifs',
                    'value' => null,
                    'rate' => null,
                    'available' => false,
                    'note' => 'Aucune donnée de don effectif n’est encore stockée.',
                ],
            ],
            'engagement' => [
                'questionnaireAbandonRate' => [
                    'label' => 'Abandon questionnaire',
                    'value' => $this->percentage($questionnaireAbandonments, $questionnaireRows),
                    'available' => $questionnaireRows > 0,
                    'note' => $questionnaireRows > 0
                        ? $questionnaireAbandonments . ' abandons / ' . $questionnaireRows . ' parcours.'
                        : 'La table collections_users existe, mais aucun parcours n’est encore enregistré.',
                    'tone' => 'warning',
                ],
                'recommendedCompanies' => [
                    'label' => 'Entreprises recommandées',
                    'value' => $recommendedCompanies,
                    'available' => true,
                    'note' => 'Entreprises avec une source/référent renseigné.',
                    'tone' => 'success',
                ],
                'qrScans' => [
                    'label' => 'QR codes scannés',
                    'value' => null,
                    'available' => false,
                    'note' => 'Aucun tracking de scan QR pour le moment.',
                    'tone' => 'success',
                ],
                'mailClicks' => [
                    'label' => 'Clics sur le mail',
                    'value' => null,
                    'available' => false,
                    'note' => 'Aucun tracking d’email pour le moment.',
                    'tone' => 'success',
                ],
                'bannerUsage' => [
                    'label' => 'Bannière utilisée',
                    'value' => null,
                    'available' => false,
                    'note' => 'Aucun événement bannière n’est encore stocké.',
                    'tone' => 'success',
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
