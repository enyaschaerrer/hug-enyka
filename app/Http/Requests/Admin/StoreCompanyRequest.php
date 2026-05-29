<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:companies,email'],
            'slug' => ['required', 'string', 'max:20', 'alpha_dash', 'unique:companies,slug'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'address' => ['nullable', 'string', 'max:500'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'employee_count' => ['nullable', 'integer', 'min:0'],
            'allowed_email_domains' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'trophy' => ['boolean'],
            'logo' => ['nullable', 'string', 'max:255'],
            'primaryColor' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondaryColor' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'thirdColor' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'collection_start' => ['required', 'date'],
            'collection_end' => ['required', 'date', 'after:collection_start'],
            'collection_linkOneDoc' => ['required', 'string', 'max:500'],
        ];
    }
}
