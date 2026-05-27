<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCompanyRequest;
use App\Models\Company;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    public function index(): JsonResponse
    {
        $companies = Company::with('collections')->get();

        return response()->json($companies->map(fn ($company) => [
            'id' => $company->id,
            'name' => $company->name,
            'slug' => $company->slug,
            'email' => $company->email,
            'employee_count' => $company->employee_count,
            'collections' => $company->collections->map(fn ($col) => [
                'id' => $col->id,
                'start' => $col->start?->toIso8601String(),
                'end' => $col->end?->toIso8601String(),
                'access_token' => $col->access_token,
                'linkOneDoc' => $col->linkOneDoc,
                'url' => '/collecte/' . $company->slug . '/' . $col->access_token,
            ]),
        ]));
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = Company::create($request->validated());

        return response()->json([
            'message' => 'Entreprise créée : ' . $company->slug,
            'company' => $company,
        ], 201);
    }
}
