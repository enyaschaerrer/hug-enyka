<?php

namespace App\Http\Requests\Admin;

use App\Rules\EmailDomainListRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $companyId = $this->route('company')->id;

        if ($this->isCollectionMode()) {
            return [
                'collection_id' => ['nullable', 'integer'],
                'collection_start' => ['required', 'date'],
                'collection_end' => ['required', 'date', 'after:collection_start'],
                'collection_linkOneDoc' => ['required', 'string', 'max:500'],
            ];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:companies,email,' . $companyId],
            'slug' => ['required', 'string', 'max:20', 'alpha_dash', 'unique:companies,slug,' . $companyId],
            'short_description' => ['nullable', 'string', 'max:500'],
            'address' => ['required', 'string', 'max:500'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'employee_count' => ['required', 'integer', 'min:0'],
            'allowed_email_domains' => ['required', 'string', 'max:255', new EmailDomainListRule()],
            'source' => ['nullable', 'string', 'max:255'],
            'is_public' => ['boolean'],
            'trophy' => ['boolean'],
            'logo' => ['nullable', File::types(['png', 'jpg', 'jpeg', 'webp', 'svg'])->max(5 * 1024)],
            'primaryColor' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondaryColor' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'thirdColor' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }

    public function isCollectionMode(): bool
    {
        return $this->query('newCollection') === '1'
            || $this->query->has('collection')
            || $this->filled('collection_id');
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (! $this->isCollectionMode()) {
                $company = $this->route('company');

                if (! $this->hasFile('logo') && empty($company?->logo)) {
                    $validator->errors()->add('logo', 'Le logo de l’entreprise est obligatoire.');
                }

                return;
            }

            $company = $this->route('company');
            $collectionId = $this->input('collection_id');
            $start = Carbon::parse((string) $this->input('collection_start'));
            $end = Carbon::parse((string) $this->input('collection_end'));

            $hasOverlap = $company->collections()
                ->when($collectionId, fn ($query) => $query->whereKeyNot($collectionId))
                ->where('start', '<=', $end)
                ->where('end', '>=', $start)
                ->exists();

            if ($hasOverlap) {
                $validator->errors()->add('collection_start', 'Cette collecte chevauche une collecte existante.');
            }
        });
    }
}
