<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCompanyRequest;
use App\Models\Company;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = Company::create($request->validated());

        return response()->json([
            'message' => 'Entreprise créée : ' . $company->slug,
            'company' => $company,
        ], 201);
    }
}
