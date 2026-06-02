<?php

namespace App\Http\Requests\Admin;

use App\Rules\EmailDomainListRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

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
            'slug' => ['required', 'string', 'max:20', 'alpha_num', 'unique:companies,slug'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'address' => ['required', 'string', 'max:500'],
            'npa' => ['required', 'string', 'regex:/^\d{4}$/'],
            'localite' => ['required', 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'employee_count' => ['required', 'integer', 'min:0'],
            'allowed_email_domains' => ['required', 'string', 'max:255', new EmailDomainListRule()],
            'source' => ['nullable', 'string', 'max:255'],
            'is_public' => ['boolean'],
            'trophy' => ['boolean'],
            'logo' => ['required', File::types(['png', 'jpg', 'jpeg', 'webp', 'svg'])->max(5 * 1024)],
            'primaryColor' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondaryColor' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'thirdColor' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'collection_start' => ['required', 'date'],
            'collection_end' => ['required', 'date', 'after:collection_start'],
            'collection_linkOneDoc' => ['required', 'string', 'max:500', 'starts_with:https://www.onedoc.ch/'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('slug')) {
            return;
        }

        $slug = (string) $this->input('slug');
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9]/', '', $slug) ?? '';

        $this->merge([
            'slug' => substr($slug, 0, 20),
        ]);
    }
}
