<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TropheeController extends Controller
{
    private const TYPES = ['donneur', 'ambassadeur', 'prixJury'];

    public function overview(): JsonResponse
    {
        return response()->json($this->overviewPayload());
    }

    public function assign(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:' . implode(',', self::TYPES)],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'rank' => ['required', 'integer', 'min:1', 'max:3'],
        ]);

        $type = (string) $validated['type'];
        $rank = (int) $validated['rank'];

        if ($type === 'prixJury' && $rank !== 1) {
            abort(422, 'Le Coup de cœur du jury ne peut avoir qu’un seul gagnant.');
        }

        DB::transaction(function () use ($type, $validated, $rank) {
            $editionId = $this->currentEditionId(true);
            $companyId = (int) $validated['company_id'];

            DB::table('prizes')
                ->where('trophy_edition_id', $editionId)
                ->where('type', $type)
                ->where(function ($query) use ($companyId, $rank) {
                    $query->where('company_id', $companyId)
                        ->orWhere('rank', $rank);
                })
                ->delete();

            DB::table('prizes')->insert([
                'trophy_edition_id' => $editionId,
                'company_id' => $companyId,
                'rank' => $rank,
                'type' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return response()->json($this->overviewPayload());
    }

    public function remove(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:' . implode(',', self::TYPES)],
            'rank' => ['required', 'integer', 'min:1', 'max:3'],
        ]);

        $editionId = $this->currentEditionId();

        if ($editionId !== null) {
            DB::table('prizes')
                ->where('trophy_edition_id', $editionId)
                ->where('type', (string) $validated['type'])
                ->where('rank', (int) $validated['rank'])
                ->delete();
        }

        return response()->json($this->overviewPayload());
    }

    /**
     * @return array<string, mixed>
     */
    private function overviewPayload(): array
    {
        $now = now();
        $editionYear = $now->year;
        $editionId = $this->currentEditionId();

        return [
            'editionYear' => $editionYear,
            'tabs' => [
                'donneur' => $this->tabPayload('donneur', $editionYear, $editionId, $now),
                'ambassadeur' => $this->tabPayload('ambassadeur', $editionYear, $editionId, $now),
                'prixJury' => $this->tabPayload('prixJury', $editionYear, $editionId, $now),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function tabPayload(string $type, int $editionYear, ?int $editionId, $now): array
    {
        $candidates = $this->candidatesForType($type, $editionYear, $editionId, $now);
        $currentWinners = $this->currentWinnersForType($type, $editionId);
        $maxRank = $type === 'prixJury' ? 1 : 3;

        return [
            'type' => $type,
            'mode' => $type === 'prixJury' ? 'single' : 'podium',
            'max_rank' => $maxRank,
            'is_complete' => $currentWinners->count() === $maxRank,
            'candidates' => $candidates->values(),
            'current_winners' => $currentWinners->values(),
            'history' => $this->historyForType($type, $editionYear)->values(),
        ];
    }

    private function currentEditionId(bool $createIfMissing = false): ?int
    {
        $editionYear = now()->year;
        $existingId = DB::table('trophy_editions')
            ->where('year', $editionYear)
            ->value('id');

        if ($existingId || ! $createIfMissing) {
            return $existingId ? (int) $existingId : null;
        }

        return DB::table('trophy_editions')->insertGetId([
            'year' => $editionYear,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function candidatesForType(string $type, int $editionYear, ?int $editionId, $now): Collection
    {
        $typeAlias = $type . '_prize_counts';

        $typePrizeCounts = DB::table('prizes')
            ->select('company_id', DB::raw('COUNT(*) as trophies_won'))
            ->where('type', $type)
            ->groupBy('company_id');

        $eligibleCollectionCounts = DB::table('collections')
            ->select('company_id', DB::raw('COUNT(*) as eligible_collections_count'))
            ->whereYear('start', $editionYear)
            ->where('start', '<=', $now)
            ->groupBy('company_id');

        $currentEditionPrizes = DB::table('prizes')
            ->select('company_id', 'rank')
            ->when($editionId !== null, fn ($query) => $query->where('trophy_edition_id', $editionId))
            ->when($editionId === null, fn ($query) => $query->whereRaw('1 = 0'))
            ->where('type', $type);

        return Company::query()
            ->leftJoinSub($typePrizeCounts, $typeAlias, function ($join) use ($typeAlias) {
                $join->on($typeAlias . '.company_id', '=', 'companies.id');
            })
            ->joinSub($eligibleCollectionCounts, 'eligible_collection_counts', function ($join) {
                $join->on('eligible_collection_counts.company_id', '=', 'companies.id');
            })
            ->leftJoinSub($currentEditionPrizes, 'current_edition_prizes', function ($join) {
                $join->on('current_edition_prizes.company_id', '=', 'companies.id');
            })
            ->where('companies.trophy', true)
            ->orderByDesc('current_edition_prizes.rank')
            ->orderByDesc('trophies_won')
            ->orderBy('companies.name')
            ->get([
                'companies.id',
                'companies.name',
                'companies.logo',
                'companies.created_at',
                'companies.primaryColor',
                'companies.employee_count',
                'companies.address',
                'companies.zip_code as npa',
                'companies.locality as localite',
                DB::raw('COALESCE(eligible_collection_counts.eligible_collections_count, 0) as collections_count'),
                DB::raw('COALESCE(' . $typeAlias . '.trophies_won, 0) as trophies_won'),
                DB::raw('current_edition_prizes.rank as current_rank'),
            ]);
    }

    private function currentWinnersForType(string $type, ?int $editionId): Collection
    {
        if ($editionId === null) {
            return collect();
        }

        return DB::table('prizes as p')
            ->join('companies as c', 'c.id', '=', 'p.company_id')
            ->where('p.trophy_edition_id', $editionId)
            ->where('p.type', $type)
            ->orderBy('p.rank')
            ->get([
                'p.rank',
                'c.id',
                'c.name',
                'c.logo',
                'c.primaryColor',
                'c.employee_count',
                'c.address',
                'c.zip_code as npa',
                'c.locality as localite',
            ]);
    }

    private function historyForType(string $type, int $editionYear): Collection
    {
        return DB::table('prizes as p')
            ->join('trophy_editions as te', 'te.id', '=', 'p.trophy_edition_id')
            ->join('companies as c', 'c.id', '=', 'p.company_id')
            ->where('p.type', $type)
            ->where('te.year', '<', $editionYear)
            ->orderByDesc('te.year')
            ->orderBy('p.rank')
            ->get([
                'te.year',
                'p.rank',
                'c.id',
                'c.name',
                'c.logo',
                'c.primaryColor',
            ])
            ->groupBy('year')
            ->map(fn (Collection $rows, $year) => [
                'year' => (int) $year,
                'winners' => $rows->values(),
            ])
            ->values();
    }
}
