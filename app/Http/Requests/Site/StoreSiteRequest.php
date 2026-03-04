<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_headquarters' => $this->boolean('is_headquarters'),
            'is_warehouse'    => $this->boolean('is_warehouse'),
            'is_store'        => $this->boolean('is_store'),
            'is_active'       => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'code'            => ['nullable', 'string', 'max:20'],
            'description'     => ['nullable', 'string', 'max:500'],
            'is_headquarters' => ['boolean'],
            'is_warehouse'    => ['boolean'],
            'is_store'        => ['boolean'],
            'manager_id'      => ['nullable', 'exists:users,id'],
            'email'           => ['nullable', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:30'],
            'address'         => ['nullable', 'string', 'max:500'],
            'city'            => ['nullable', 'string', 'max:100'],
            'postal_code'     => ['nullable', 'string', 'max:20'],
            'country'         => ['nullable', 'string', 'max:100'],
            'latitude'        => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'       => ['nullable', 'numeric', 'between:-180,180'],
            'business_hours'  => ['nullable', 'array'],
            'is_active'       => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du site est requis.',
        ];
    }
}
