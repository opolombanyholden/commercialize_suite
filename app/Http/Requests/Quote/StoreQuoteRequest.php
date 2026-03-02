<?php

namespace App\Http\Requests\Quote;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'quote_date' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quote_date'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            
            // Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.details' => ['nullable', 'string'],
            'items.*.type' => ['required', 'in:product,service'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            
            // Taxes
            'taxes' => ['nullable', 'array'],
            'taxes.*' => ['exists:taxes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Le client est requis.',
            'items.required' => 'Au moins une ligne est requise.',
            'items.min' => 'Au moins une ligne est requise.',
            'items.*.description.required' => 'La description est requise pour chaque ligne.',
            'items.*.quantity.required' => 'La quantité est requise.',
            'items.*.unit_price.required' => 'Le prix unitaire est requis.',
        ];
    }
}
