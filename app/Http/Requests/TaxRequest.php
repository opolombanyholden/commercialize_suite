<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $taxId = $this->route('tax')?->id ?? $this->route('tax');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('taxes', 'name')
                    ->where('company_id', auth()->user()->company_id)
                    ->ignore($taxId),
            ],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'apply_to' => ['required', Rule::in(['all', 'products', 'services'])],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la taxe est obligatoire.',
            'name.unique' => 'Une taxe avec ce nom existe déjà.',
            'rate.required' => 'Le taux est obligatoire.',
            'rate.numeric' => 'Le taux doit être un nombre.',
            'rate.min' => 'Le taux doit être positif.',
            'rate.max' => 'Le taux ne peut pas dépasser 100%.',
            'apply_to.required' => 'Le champ "Applicable à" est obligatoire.',
            'apply_to.in' => 'La valeur sélectionnée est invalide.',
        ];
    }
}
