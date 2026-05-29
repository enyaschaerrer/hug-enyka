<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\CompanyRegistrationMail;
use App\Models\Form;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CompanyFormController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'trophy'  => 'boolean',
        ]);

        $form = Form::create([
            'name'    => $validated['name'],
            'email'   => $validated['email'],
            'phone'   => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'message' => $validated['message'] ?? null,
            'trophy'  => $validated['trophy'] ?? false,
        ]);

        Mail::send(new CompanyRegistrationMail($form));

        return response()->json(['message' => 'Inscription enregistrée.'], 201);
    }
}