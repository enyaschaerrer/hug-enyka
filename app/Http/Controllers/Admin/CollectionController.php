<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCollectionRequest;
use App\Http\Requests\Admin\UpdateCollectionRequest;
use App\Models\Collection;
use App\Models\Company;
use Illuminate\Http\JsonResponse;

class CollectionController extends Controller
{
    public function store(StoreCollectionRequest $request, Company $company): JsonResponse
    {
        $validated = $request->validated();

        $collection = Collection::create([
            'company_id' => $company->id,
            'start' => $validated['start'],
            'end' => $validated['end'],
            'access_token' => bin2hex(random_bytes(8)),
            'linkOneDoc' => $validated['linkOneDoc'],
        ]);

        return response()->json([
            'message' => 'Collecte créée.',
            'collection' => [
                'id' => $collection->id,
                'start' => $collection->start->toIso8601String(),
                'end' => $collection->end->toIso8601String(),
                'access_token' => $collection->access_token,
                'linkOneDoc' => $collection->linkOneDoc,
                'url' => '/collecte/' . $company->slug . '/' . $collection->access_token,
            ],
        ], 201);
    }

    public function update(UpdateCollectionRequest $request, Company $company, Collection $collection): JsonResponse
    {
        $collection->update($request->validated());

        return response()->json([
            'message' => 'Collecte mise à jour.',
            'collection' => [
                'id' => $collection->id,
                'start' => $collection->start->toIso8601String(),
                'end' => $collection->end->toIso8601String(),
                'access_token' => $collection->access_token,
                'linkOneDoc' => $collection->linkOneDoc,
                'url' => '/collecte/' . $company->slug . '/' . $collection->access_token,
            ],
        ]);
    }
}
