<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Client
            'client_id' => ['nullable', 'exists:clients,id'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:30'],
            'client_address' => ['nullable', 'string', 'max:500'],
            'client_city' => ['nullable', 'string', 'max:100'],
            'client_postal_code' => ['nullable', 'string', 'max:20'],

            // Dates
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],

            // Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.details' => ['nullable', 'string', 'max:1000'],
            'items.*.type' => ['required', 'in:product,service'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001', 'max:999999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:999999999'],

            // Taxes
            'taxes' => ['nullable', 'array'],
            'taxes.*.tax_id' => ['required', 'exists:taxes,id'],
            'taxes.*.apply_to' => ['required', 'in:all,products,services'],

            // Notes
            'notes' => ['nullable', 'string', 'max:5000'],
            'terms' => ['nullable', 'string', 'max:5000'],
            'subject' => ['nullable', 'string', 'max:500'],

            // Site
            'site_id' => ['nullable', 'exists:sites,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_name.required' => 'Le nom du client est obligatoire.',
            'invoice_date.required' => 'La date de la facture est obligatoire.',
            'due_date.after_or_equal' => 'La date d\'échéance doit être égale ou postérieure à la date de facture.',
            'items.required' => 'La facture doit contenir au moins une ligne.',
            'items.min' => 'La facture doit contenir au moins une ligne.',
            'items.*.description.required' => 'La description de chaque ligne est obligatoire.',
            'items.*.type.required' => 'Le type de chaque ligne est obligatoire.',
            'items.*.quantity.required' => 'La quantité est obligatoire.',
            'items.*.quantity.min' => 'La quantité doit être supérieure à 0.',
            'items.*.unit_price.required' => 'Le prix unitaire est obligatoire.',
        ];
    }
}
