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
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'phone'    => 'nullable|string|max:20',
            'address'  => 'required|string|max:255',
            'zip_code' => ['required', 'string', 'regex:/^\d{4}$/'],
            'locality' => 'required|string|max:100',
            'message'  => 'required|string',
            'trophy'   => 'boolean',
        ]);

        $form = Form::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'address'  => $validated['address'],
            'zip_code' => $validated['zip_code'],
            'locality' => $validated['locality'],
            'message'  => $validated['message'],
            'trophy'   => $validated['trophy'] ?? false,
        ]);

        Mail::send(new CompanyRegistrationMail($form));

        return response()->json(['message' => 'Inscription enregistrée.'], 201);
    }

    public function storePrize(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'labelled' => 'required|boolean',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255',
        ]);

        $form = Form::create([
            'name'   => $validated['name'],
            'email'  => $validated['email'],
            'trophy' => true,
        ]);

        Mail::send(new CompanyRegistrationMail($form));

        return response()->json(['message' => 'Candidature enregistrée.'], 201);
    }
}