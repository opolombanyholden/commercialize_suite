<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')
                    ->where('company_id', auth()->user()->company_id)
                    ->ignore($productId),
            ],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'sku')
                    ->where('company_id', auth()->user()->company_id)
                    ->ignore($productId),
            ],
            'barcode' => ['nullable', 'string', 'max:50'],
            'type' => ['required', Rule::in(['product', 'service'])],
            'category_id' => ['nullable', 'exists:categories,id'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'cost_price' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'tax_id' => ['nullable', 'exists:taxes,id'],
            'track_inventory' => ['boolean'],
            'stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'stock_alert_threshold' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:20'],
            'main_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'is_published_online' => ['boolean'],
            'share_title' => ['nullable', 'string', 'max:255'],
            'share_description' => ['nullable', 'string', 'max:500'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du produit est obligatoire.',
            'type.required' => 'Le type est obligatoire.',
            'type.in' => 'Le type doit être "product" ou "service".',
            'price.required' => 'Le prix est obligatoire.',
            'price.numeric' => 'Le prix doit être un nombre.',
            'price.min' => 'Le prix doit être positif.',
            'sku.unique' => 'Ce SKU existe déjà.',
            'slug.unique' => 'Ce slug existe déjà.',
            'main_image.image' => 'L\'image doit être un fichier image.',
            'main_image.max' => 'L\'image ne doit pas dépasser 5 Mo.',
        ];
    }
}
