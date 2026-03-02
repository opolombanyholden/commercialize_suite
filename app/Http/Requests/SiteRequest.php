<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $siteId = $this->route('site')?->id ?? $this->route('site');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('sites', 'code')
                    ->where('company_id', auth()->user()->company_id)
                    ->ignore($siteId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_headquarters' => ['boolean'],
            'is_warehouse' => ['boolean'],
            'is_store' => ['boolean'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'size:2'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du site est obligatoire.',
            'code.required' => 'Le code du site est obligatoire.',
            'code.unique' => 'Ce code de site existe déjà.',
            'email.email' => 'L\'email doit être une adresse valide.',
            'manager_id.exists' => 'Le gestionnaire sélectionné n\'existe pas.',
        ];
    }
}
