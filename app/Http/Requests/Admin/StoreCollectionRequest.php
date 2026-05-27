<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCollectionRequest extends FormRequest
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
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
            'linkOneDoc' => ['required', 'string', 'max:500'],
        ];
    }
}
