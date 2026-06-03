<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TropheeController extends Controller
{
    public function donorCandidates(): JsonResponse
    {
        $now = now();
        $editionYear = $now->year;

        $donorTrophyCounts = DB::table('prizes')
            ->select('company_id', DB::raw('COUNT(*) as donor_trophies_won'))
            ->where('type', 'donneur')
            ->groupBy('company_id');

        $eligibleCollectionCounts = DB::table('collections')
            ->select('company_id', DB::raw('COUNT(*) as eligible_collections_count'))
            ->whereYear('start', $editionYear)
            ->where('start', '<=', $now)
            ->groupBy('company_id');

        $companies = Company::query()
            ->leftJoinSub($donorTrophyCounts, 'donor_prize_counts', function ($join) {
                $join->on('donor_prize_counts.company_id', '=', 'companies.id');
            })
            ->joinSub($eligibleCollectionCounts, 'eligible_collection_counts', function ($join) {
                $join->on('eligible_collection_counts.company_id', '=', 'companies.id');
            })
            ->where('companies.trophy', true)
            ->orderByDesc('donor_trophies_won')
            ->orderBy('companies.name')
            ->get([
                'companies.id',
                'companies.name',
                'companies.email',
                'companies.primaryColor',
                'companies.employee_count',
                'companies.address',
                'companies.npa',
                'companies.localite',
                DB::raw('COALESCE(eligible_collection_counts.eligible_collections_count, 0) as collections_count'),
                DB::raw('COALESCE(donor_prize_counts.donor_trophies_won, 0) as donor_trophies_won'),
            ]);

        return response()->json($companies);
    }
}
