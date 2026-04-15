<?php

namespace App\Http\Requests\Quote;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id'   => ['nullable', 'exists:clients,id'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'quote_date' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quote_date'],
            'status' => ['nullable', 'in:draft,sent,accepted,declined'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'subject' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.details' => ['nullable', 'string'],
            'items.*.type' => ['required', 'in:product,service'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'taxes' => ['nullable', 'array'],
            'taxes.*' => ['exists:taxes,id'],
        ];
    }
}
