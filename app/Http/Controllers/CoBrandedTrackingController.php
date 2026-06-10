<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoBrandedTrackingController extends Controller
{
    /**
     * Enregistre l'étape atteinte dans le questionnaire (quiz → chat → done).
     * L'abandon est déduit côté KPI (quiz_step != 'done').
     */
    public function quizStep(Request $request, string $brand, string $token): JsonResponse
    {
        $validated = $request->validate([
            'step' => ['required', 'in:quiz,chat,done'],
        ]);

        [$collection, $user] = $this->resolveCollectionUser($request, $brand, $token);

        $collection->users()->updateExistingPivot($user->id, [
            'quiz_step' => $validated['step'],
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Enregistre le clic sur le lien de prise de rendez-vous OneDoc.
     */
    public function onedocClick(Request $request, string $brand, string $token): JsonResponse
    {
        [$collection, $user] = $this->resolveCollectionUser($request, $brand, $token);

        $collection->users()->updateExistingPivot($user->id, [
            'clicked_onedoc' => true,
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Résout la collection (brand + token) et l'utilisateur connecté, en s'assurant
     * qu'il participe bien à cette collecte. Renvoie [Collection, User].
     *
     * @return array{0: Collection, 1: \App\Models\User}
     */
    private function resolveCollectionUser(Request $request, string $brand, string $token): array
    {
        $collection = Collection::query()
            ->where('access_token', $token)
            ->whereHas('company', fn ($query) => $query->where('slug', $brand))
            ->firstOrFail();

        $user = $request->user();

        abort_unless(
            $user
                && $user->role->isOneOf([UserRole::User->value])
                && (int) $user->company_id === (int) $collection->company_id
                && $collection->users()->whereKey($user->id)->exists(),
            403,
        );

        return [$collection, $user];
    }
}
