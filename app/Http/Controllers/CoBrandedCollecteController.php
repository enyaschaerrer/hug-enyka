<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\View\View;

class CoBrandedCollecteController extends Controller
{
    public function show(string $brand, string $token): View
    {
        $collection = Collection::query()
            ->with('company')
            ->where('access_token', $token)
            ->where('end', '>=', now())
            ->whereHas('company', fn ($query) => $query->where('slug', $brand))
            ->firstOrFail();

        return view('app', [
            'coBrandedCollecte' => [
                'company' => [
                    'name' => $collection->company->name,
                    'logo' => $collection->company->logo,
                    'shortDescription' => $collection->company->short_description,
                    'slug' => $collection->company->slug,
                    'colors' => [
                        'primary' => $collection->company->primaryColor,
                        'secondary' => $collection->company->secondaryColor,
                        'third' => $collection->company->thirdColor,
                    ],
                ],
                'collection' => [
                    'start' => $collection->start?->toIso8601String(),
                    'end' => $collection->end?->toIso8601String(),
                    'appointmentUrl' => $collection->linkOneDoc,
                ],
            ],
        ]);
    }
}
