<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::query()
            ->whereIn('role', [UserRole::SuperAdmin->value, UserRole::Admin->value])
            ->orderBy('email')
            ->get(['id', 'name', 'email', 'role', 'created_at']);

        return response()->json($users->map(fn (User $user) => $this->payload($user)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:128'],
            'role' => ['required', Rule::in([UserRole::SuperAdmin->value, UserRole::Admin->value])],
        ]);

        $email = strtolower($validated['email']);

        $user = User::create([
            'name' => $email,
            'email' => $email,
            'professional_email' => $email,
            'password' => $validated['password'],
            'role' => $validated['role'],
            'email_validated' => true,
        ]);

        return response()->json([
            'message' => 'Compte créé.',
            'account' => $this->payload($user),
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->ensureAdminAccount($user);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'max:128'],
            'role' => ['required', Rule::in([UserRole::SuperAdmin->value, UserRole::Admin->value])],
        ]);

        $this->ensureLastSuperAdminIsPreserved($user, $validated['role']);

        $email = strtolower($validated['email']);
        $payload = [
            'name' => $email,
            'email' => $email,
            'professional_email' => $email,
            'role' => $validated['role'],
            'email_validated' => true,
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $user->update($payload);

        return response()->json([
            'message' => 'Compte mis à jour.',
            'account' => $this->payload($user->fresh()),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->ensureAdminAccount($user);

        if ((int) $request->user()->id === (int) $user->id) {
            throw ValidationException::withMessages([
                'account' => 'Vous ne pouvez pas supprimer votre propre compte.',
            ]);
        }

        $this->ensureLastSuperAdminIsPreserved($user, UserRole::Admin->value);

        $user->delete();

        return response()->json([
            'message' => 'Compte supprimé.',
        ]);
    }

    private function ensureAdminAccount(User $user): void
    {
        if (! $user->role->isOneOf([UserRole::SuperAdmin->value, UserRole::Admin->value])) {
            abort(404);
        }
    }

    private function ensureLastSuperAdminIsPreserved(User $user, string $nextRole): void
    {
        if ($user->role !== UserRole::SuperAdmin || $nextRole === UserRole::SuperAdmin->value) {
            return;
        }

        $superAdminCount = User::query()
            ->where('role', UserRole::SuperAdmin->value)
            ->count();

        if ($superAdminCount <= 1) {
            throw ValidationException::withMessages([
                'role' => 'Il doit rester au moins un superadmin.',
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(User $user): array
    {
        return [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role->value,
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }
}
