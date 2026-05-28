<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function home(): View
    {
        $companiesData = $this->companiesData();

        // Podium par édition (type 'donneur' pour l'instant).
        $podiumRows = DB::table('prizes as p')
            ->join('trophy_editions as te', 'te.id', '=', 'p.trophy_edition_id')
            ->join('companies as c', 'c.id', '=', 'p.company_id')
            ->where('p.type', 'donneur')
            ->orderBy('te.year')
            ->orderBy('p.rank')
            ->select(['te.year', 'p.rank', 'c.id as company_id', 'c.name as company_name', 'c.logo as company_logo'])
            ->get();

        // Nombre de trophées cumulés par (entreprise, année) : tous les prizes où l'édition est <= année.
        $allPrizes = DB::table('prizes as p')
            ->join('trophy_editions as te', 'te.id', '=', 'p.trophy_edition_id')
            ->where('p.type', 'donneur')
            ->select('p.company_id', 'te.year')
            ->get();

        $trophyCountAt = fn (int $companyId, int $year) => $allPrizes
            ->where('company_id', $companyId)
            ->where('year', '<=', $year)
            ->count();

        $podiumsData = $podiumRows
            ->groupBy('year')
            ->map(function ($rows, $year) use ($trophyCountAt) {
                $year = (int) $year;
                $byRank = $rows->keyBy('rank');
                $entry = fn ($r) => [
                    'name' => $r?->company_name,
                    'logo' => $r?->company_logo,
                    'trophies' => $r ? $trophyCountAt($r->company_id, $year) : 0,
                ];
                return [
                    'year' => $year,
                    'first' => $entry($byRank[1] ?? null),
                    'second' => $entry($byRank[2] ?? null),
                    'third' => $entry($byRank[3] ?? null),
                ];
            })
            ->values();

        // Jury : pas de modèle, données en dur.
        $jury = [
            [
                'name' => 'Antoine Geissbühler',
                'photo' => '/img/jury/antoinegeissbuhler_photo.jpg',
                'bio' => 'Médecin interniste, professeur ordinaire, directeur du Centre de l\'innovation des HUG et médecin-chef du Service de cybersanté et télémédecine.',
            ],
            [
                'name' => 'François Freitas',
                'photo' => '/img/jury/freitasfrancois_photo.jpg',
                'bio' => 'Infirmier responsable de l\'équipe de soins, joignable pour toute question liée aux dons d\'entreprise ou partenariats (CTS).',
            ],
            [
                'name' => 'Robert Mardini',
                'photo' => '/img/jury/robertmardini_photo.jpg',
                'bio' => 'Directeur général des Hôpitaux universitaires de Genève depuis septembre 2024.',
            ],
        ];

        return view('public.home', [
            'jury' => $jury,
            'companies' => $companiesData,
            'podiums' => $podiumsData,
        ]);
    }

    public function label(): View
    {
        return view('public.label', [
            'companies' => $this->companiesData(),
        ]);
    }

    private function companiesData()
    {
        // Entreprises participantes : année d'adhésion = MIN(year(start)) des collectes,
        // nb de trophées = count des prizes liés.
        $companies = Company::query()
            ->withMin('collections', 'start')
            ->orderBy('name')
            ->get();

        $prizeCounts = DB::table('prizes')
            ->select('company_id', DB::raw('COUNT(*) as count'))
            ->whereIn('company_id', $companies->pluck('id'))
            ->groupBy('company_id')
            ->pluck('count', 'company_id');

        return $companies->map(fn ($c) => [
            'name' => $c->name,
            'logo' => $c->logo,
            'adhesionYear' => $c->collections_min_start
                ? Carbon::parse($c->collections_min_start)->year
                : null,
            'trophies' => (int) ($prizeCounts[$c->id] ?? 0),
        ])->values();
    }
}
