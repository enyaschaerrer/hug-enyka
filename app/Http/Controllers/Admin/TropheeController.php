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
        $donorTrophyCounts = DB::table('prizes')
            ->select('company_id', DB::raw('COUNT(*) as donor_trophies_won'))
            ->where('type', 'donneur')
            ->groupBy('company_id');

        $collectionCounts = DB::table('collections')
            ->select('company_id', DB::raw('COUNT(*) as collections_count'))
            ->groupBy('company_id');

        $companies = Company::query()
            ->leftJoinSub($donorTrophyCounts, 'donor_prize_counts', function ($join) {
                $join->on('donor_prize_counts.company_id', '=', 'companies.id');
            })
            ->leftJoinSub($collectionCounts, 'collection_counts', function ($join) {
                $join->on('collection_counts.company_id', '=', 'companies.id');
            })
            ->where('companies.trophy', true)
            ->orderByDesc('donor_trophies_won')
            ->orderBy('companies.name')
            ->get([
                'companies.id',
                'companies.name',
                'companies.logo',
                'companies.email',
                'companies.employee_count',
                'companies.address',
                'companies.npa',
                'companies.localite',
                DB::raw('COALESCE(collection_counts.collections_count, 0) as collections_count'),
                DB::raw('COALESCE(donor_prize_counts.donor_trophies_won, 0) as donor_trophies_won'),
            ]);

        return response()->json($companies);
    }
}
