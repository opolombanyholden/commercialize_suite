<?php

namespace App\Http\Requests\Tax;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'rate' => ['required', 'numeric', 'min:-100', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'apply_to' => ['required', 'in:all,products,services'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la taxe est requis.',
            'rate.required' => 'Le taux est requis.',
            'rate.max' => 'Le taux ne peut pas dépasser 100%.',
        ];
    }
}
