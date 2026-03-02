<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clientId = $this->route('client')?->id ?? $this->route('client');

        return [
            'type' => ['required', Rule::in(['individual', 'business'])],
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'required_if:type,business', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('clients', 'email')
                    ->where('company_id', auth()->user()->company_id)
                    ->ignore($clientId),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'size:2'],
            'payment_terms' => ['nullable', 'integer', 'min:0', 'max:365'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Le type de client est obligatoire.',
            'type.in' => 'Le type doit être "individual" ou "business".',
            'name.required' => 'Le nom est obligatoire.',
            'company_name.required_if' => 'Le nom de l\'entreprise est obligatoire pour les clients professionnels.',
            'email.email' => 'L\'email doit être une adresse valide.',
            'email.unique' => 'Cet email est déjà utilisé par un autre client.',
        ];
    }
}
