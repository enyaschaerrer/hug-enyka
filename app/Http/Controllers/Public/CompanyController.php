<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\CompanyRegistrationMail;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CompanyController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:companies,email',
            'slug'  => 'required|string|max:20|unique:companies,slug',
        ]);

        $company = Company::create($validated);

        Mail::send(new CompanyRegistrationMail($company));

        return response()->json(['message' => 'Inscription enregistrée.'], 201);
    }
}