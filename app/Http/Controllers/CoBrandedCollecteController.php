<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Collection;
use App\Support\EmailDomainList;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoBrandedCollecteController extends Controller
{
    public function show(Request $request, string $brand, string $token): View
    {
        $collection = Collection::query()
            ->with('company')
            ->where('access_token', $token)
            ->whereHas('company', fn ($query) => $query->where('slug', $brand))
            ->firstOrFail();

        abort_unless($collection->isPublicLinkEnabled(), 404);

        $canAccess = $this->canAccessCollection($request, $collection);

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
                    'appointmentUrl' => $canAccess ? $collection->linkOneDoc : null,
                ],
                'auth' => [
                    'canAccess' => $canAccess,
                    'emailPlaceholder' => EmailDomainList::firstEmailExample($collection->company->allowed_email_domains),
                    'accessCodeUrl' => route('public.collecte.cobranded.access-code', ['brand' => $brand, 'token' => $token]),
                    'loginUrl' => route('public.collecte.cobranded.login', ['brand' => $brand, 'token' => $token]),
                    'logoutUrl' => route('public.collecte.cobranded.logout', ['brand' => $brand, 'token' => $token]),
                ],
            ],
        ]);
    }

    private function canAccessCollection(Request $request, Collection $collection): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        if ($user->role->isOneOf([UserRole::SuperAdmin->value, UserRole::Admin->value])) {
            return true;
        }

        return $user->role->isOneOf([UserRole::User->value])
            && (int) $user->company_id === (int) $collection->company_id
            && $collection->users()->whereKey($user->id)->exists();
    }
}
