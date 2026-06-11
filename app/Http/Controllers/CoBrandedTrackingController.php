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
     * Enregistre une demande de rappel : calcule la date (now + délai le plus long en mois)
     * et stocke l'email. L'envoi se fera plus tard via la commande planifiée reminders:send.
     */
    public function reminder(Request $request, string $brand, string $token): JsonResponse
    {
        $validated = $request->validate([
            'email'  => ['required', 'email', 'max:255'],
            'months' => ['required', 'numeric', 'min:0.1', 'max:60'],
        ]);

        [$collection, $user] = $this->resolveCollectionUser($request, $brand, $token);

        // months peut être fractionnaire (0.5 = 2 semaines) → on convertit en jours.
        $reminderAt = now()->addDays((int) round($validated['months'] * 30.4));

        $collection->users()->updateExistingPivot($user->id, [
            'reminder_at'    => $reminderAt,
            'reminder_email' => $validated['email'],
        ]);

        return response()->json(['ok' => true, 'reminderAt' => $reminderAt->toDateString()]);
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
