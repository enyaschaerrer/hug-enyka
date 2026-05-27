<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function store(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->credentials())) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        if (! $request->user()?->role->isOneOf([UserRole::SuperAdmin->value, UserRole::Admin->value])) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Ce compte ne peut pas acceder a l administration.',
            ]);
        }

        return response()->json(['redirect' => '/admin']);
    }

    public function destroy(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['redirect' => '/admin/login']);
    }
}
