<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
            'invoice_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.details' => ['nullable', 'string'],
            'items.*.type' => ['required', 'in:product,service'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'taxes' => ['nullable', 'array'],
            'taxes.*' => ['exists:taxes,id'],

            // Options à la création
            'payment_immediate'  => ['nullable', 'boolean'],
            'payment_method'     => ['nullable', 'string', 'in:cash,check,bank_transfer,credit_card,mobile_money,other'],
            'payment_reference'  => ['nullable', 'string', 'max:100'],
            'delivery_immediate' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('items', []) as $index => $item) {
                if (empty($item['product_id']) || ($item['type'] ?? '') !== 'product') {
                    continue;
                }
                $product = \App\Models\Product::find($item['product_id']);
                if (!$product || !$product->track_inventory) {
                    continue;
                }
                $qty = (float) ($item['quantity'] ?? 0);
                if ($qty > (float) $product->stock_quantity) {
                    $validator->errors()->add(
                        "items.{$index}.quantity",
                        "Qté demandée ({$qty}) pour « {$product->name} » > stock disponible ({$product->stock_quantity})."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'client_id.required'   => 'Le client est requis.',
            'client_name.required' => 'Le nom du client est requis.',
            'items.required' => 'Au moins une ligne est requise.',
        ];
    }
}
