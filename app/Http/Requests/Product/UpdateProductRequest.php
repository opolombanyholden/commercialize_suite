<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:50'],
            'barcode' => ['nullable', 'string', 'max:50'],
            'type' => ['required', 'in:product,service'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'tax_id' => ['nullable', 'exists:taxes,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'main_image' => ['nullable', 'image', 'max:5120'],
            'remove_main_image' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'max:5120'],
            'track_inventory' => ['boolean'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'stock_alert_threshold' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_published_online' => ['boolean'],
            'is_featured' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'share_title' => ['nullable', 'string', 'max:255'],
            'share_description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
