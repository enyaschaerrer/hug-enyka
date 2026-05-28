<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCompanyRequest;
use App\Http\Requests\Admin\UpdateCompanyRequest;
use App\Models\Collection;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

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

    public function show(Company $company): JsonResponse
    {
        return response()->json($this->companyPayload($company->load('collections')));
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $company = Company::create(Arr::except($validated, [
            'collection_start',
            'collection_end',
            'collection_linkOneDoc',
        ]));

        $this->saveCollection($company, $validated);

        return response()->json([
            'message' => 'Campagne créée : ' . $company->slug,
            'company' => $this->companyPayload($company->load('collections')),
        ], 201);
    }

    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        $validated = $request->validated();
        $company->update(Arr::except($validated, [
            'collection_start',
            'collection_end',
            'collection_linkOneDoc',
        ]));

        $this->saveCollection($company, $validated);

        return response()->json([
            'message' => 'Campagne mise à jour.',
            'company' => $this->companyPayload($company->load('collections')),
        ]);
    }

    public function destroy(Company $company): JsonResponse
    {
        $company->delete();

        return response()->json([
            'message' => 'Campagne supprimée.',
        ]);
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function saveCollection(Company $company, array $validated): void
    {
        $collection = $company->collections()->oldest('id')->first();
        $payload = [
            'start' => $validated['collection_start'],
            'end' => $validated['collection_end'],
            'linkOneDoc' => $validated['collection_linkOneDoc'],
        ];

        if ($collection) {
            $collection->update($payload);
            return;
        }

        Collection::create([
            ...$payload,
            'company_id' => $company->id,
            'access_token' => bin2hex(random_bytes(8)),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function companyPayload(Company $company): array
    {
        return [
            ...$company->toArray(),
            'collections' => $company->collections->map(fn ($col) => [
                'id' => $col->id,
                'start' => $col->start?->toIso8601String(),
                'end' => $col->end?->toIso8601String(),
                'access_token' => $col->access_token,
                'linkOneDoc' => $col->linkOneDoc,
                'url' => '/collecte/' . $company->slug . '/' . $col->access_token,
            ])->values(),
        ];
    }

    public function registrations(): JsonResponse
    {
        $registrations = Company::query()
            ->latest()
            ->get(['id', 'name', 'email', 'created_at']);

        return response()->json($registrations);
    }
}
