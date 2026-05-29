<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCompanyRequest;
use App\Http\Requests\Admin\UpdateCompanyRequest;
use App\Models\Collection;
use App\Models\Company;
use App\Models\Form;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class CompanyController extends Controller
{
    public function index(): JsonResponse
    {
        $companies = Company::with(['collections' => fn ($query) => $query->orderByDesc('start')])->get();

        return response()->json($companies->map(fn ($company) => [
            'id' => $company->id,
            'name' => $company->name,
            'slug' => $company->slug,
            'email' => $company->email,
            'employee_count' => $company->employee_count,
            'trophy' => (bool) $company->trophy,
            'collections' => $company->collections->map(fn ($col) => $this->collectionPayload($company, $col)),
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
            'collection_id',
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
        $collection = isset($validated['collection_id'])
            ? $company->collections()->whereKey($validated['collection_id'])->first()
            : $company->collections()->oldest('id')->first();

        if (isset($validated['collection_id']) && ! $collection) {
            abort(404);
        }
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
            'collections' => $company->collections
                ->map(fn ($col) => $this->collectionPayload($company, $col))
                ->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function collectionPayload(Company $company, Collection $collection): array
    {
        return [
            'id' => $collection->id,
            'start' => $collection->start?->toIso8601String(),
            'end' => $collection->end?->toIso8601String(),
            'access_token' => $collection->access_token,
            'linkOneDoc' => $collection->linkOneDoc,
            'url' => '/collecte/' . $company->slug . '/' . $collection->access_token,
            'is_active' => $collection->isActive(),
        ];
    }

    public function registrations(): JsonResponse
    {
        $registrations = Form::query()
            ->latest()
            ->get(['id', 'name', 'email', 'phone', 'address', 'message', 'trophy', 'treated', 'created_at']);

        return response()->json($registrations);
    }

    public function pendingRegistrations(): JsonResponse
    {
        $registrations = Form::query()
            ->where('treated', false)
            ->latest()
            ->get(['id', 'name', 'email', 'phone', 'address', 'message', 'trophy', 'treated', 'created_at']);

        return response()->json($registrations);
    }

    public function toggleTreated(Form $form): JsonResponse
    {
        $form->update(['treated' => !$form->treated]);
        return response()->json(['message' => 'Statut mis à jour.']);
    }

    public function destroyForm(Form $form): JsonResponse
    {
        $form->delete();
        return response()->json(['message' => 'Inscription supprimée.']);
    }

    public function showForm(Form $form): JsonResponse
    {
        $name = trim($form->name);
        $nameWithoutNumbers = trim(preg_replace('/\d+/', '', $name));

        $words = collect(preg_split('/\s+/', $nameWithoutNumbers))
            ->filter(fn($word) => strlen($word) > 2)
            ->values();

        $query = Company::query();

        foreach ($words as $word) {
            $query->orWhere('name', 'like', '%' . $word . '%');
        }

        $query->orWhereRaw('SOUNDEX(name) = SOUNDEX(?)', [$name]);
        $query->orWhereRaw('SOUNDEX(name) = SOUNDEX(?)', [$nameWithoutNumbers]);

        $allCompanies = Company::query()->get(['id', 'name', 'email']);

        $matchingCompanies = $query->get(['id', 'name', 'email'])
            ->merge(
                $allCompanies->filter(function ($company) use ($nameWithoutNumbers) {
                    $companyNameWithoutNumbers = trim(preg_replace('/\d+/', '', $company->name));
                    return stripos($companyNameWithoutNumbers, $nameWithoutNumbers) !== false
                        || stripos($nameWithoutNumbers, $companyNameWithoutNumbers) !== false;
                })
            )
            ->unique('id')
            ->values();

        return response()->json([
            'form'              => $form,
            'matchingCompanies' => $matchingCompanies,
        ]);
    }
}
