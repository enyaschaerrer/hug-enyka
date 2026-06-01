<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Mail\CoBrandedAccessCodeMail;
use App\Models\Collection;
use App\Models\User;
use App\Support\EmailDomainList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CoBrandedAuthController extends Controller
{
    public function sendAccessCode(Request $request, string $brand, string $token): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower($validated['email']);
        $this->ensureRateLimit('cobranded-code', $request, $email, 3);

        $collection = $this->collection($brand, $token);
        $this->ensureEmailDomainAllowed($email, $collection);

        $existingUser = User::query()->where('email', $email)->first();
        if ($existingUser && ! $existingUser->role->isOneOf([UserRole::User->value])) {
            throw ValidationException::withMessages([
                'email' => 'Ce compte utilise un accès administrateur.',
            ]);
        }

        $password = $this->generatePassword();

        $user = User::query()->updateOrCreate([
            'email' => $email,
        ], [
            'company_id' => $collection->company_id,
            'name' => $email,
            'professional_email' => $email,
            'password' => $password,
            'role' => UserRole::User,
            'email_validated' => true,
        ]);

        $collection->users()->syncWithoutDetaching([$user->id]);

        Mail::send(new CoBrandedAccessCodeMail(
            $collection->loadMissing('company'),
            $email,
            $password,
            route('public.collecte.cobranded', ['brand' => $brand, 'token' => $token]),
        ));

        return response()->json([
            'message' => 'Un mot de passe personnel a été envoyé à cette adresse.',
        ]);
    }

    public function login(Request $request, string $brand, string $token): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:64'],
        ]);

        $email = strtolower($validated['email']);
        $this->ensureRateLimit('cobranded-login', $request, $email, 5);

        $collection = $this->collection($brand, $token);

        if (! Auth::attempt(['email' => $email, 'password' => $validated['password']])) {
            throw ValidationException::withMessages([
                'email' => 'Identifiants invalides.',
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();
        if (! $user || ! $user->role->isOneOf([UserRole::User->value])) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Ce compte ne peut pas utiliser cet accès.',
            ]);
        }

        if ((int) $user->company_id !== (int) $collection->company_id || ! $collection->users()->whereKey($user->id)->exists()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Ce compte n’est pas autorisé pour cette collecte.',
            ]);
        }

        return response()->json([
            'message' => 'Connexion réussie.',
            'reload' => true,
        ]);
    }

    private function collection(string $brand, string $token): Collection
    {
        return Collection::query()
            ->with('company')
            ->where('access_token', $token)
            ->where('end', '>=', now())
            ->whereHas('company', fn ($query) => $query->where('slug', $brand))
            ->firstOrFail();
    }

    private function ensureEmailDomainAllowed(string $email, Collection $collection): void
    {
        if (! EmailDomainList::emailMatches($email, $collection->company->allowed_email_domains)) {
            throw ValidationException::withMessages([
                'email' => 'Cette adresse email n’est pas autorisée pour cette collecte.',
            ]);
        }
    }

    private function ensureRateLimit(string $prefix, Request $request, string $email, int $maxAttempts): void
    {
        $key = $prefix . '|' . Str::lower($email) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => 'Trop de tentatives. Réessaie dans ' . $seconds . ' secondes.',
            ]);
        }

        RateLimiter::hit($key, 60);
    }

    private function generatePassword(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $password = '';

        for ($i = 0; $i < 6; $i++) {
            $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $password;
    }
}
