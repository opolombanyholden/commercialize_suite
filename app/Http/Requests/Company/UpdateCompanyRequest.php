<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'legal_name'          => ['nullable', 'string', 'max:255'],
            'email'               => ['required', 'email', 'max:255'],
            'phone'               => ['nullable', 'string', 'max:20'],
            'website'             => ['nullable', 'url', 'max:255'],
            'address'             => ['nullable', 'string', 'max:500'],
            'city'                => ['nullable', 'string', 'max:100'],
            'postal_code'         => ['nullable', 'string', 'max:20'],
            'state'               => ['nullable', 'string', 'max:100'],
            'country'             => ['nullable', 'string', 'size:2'],
            'tax_id'              => ['nullable', 'string', 'max:50'],
            'registration_number' => ['nullable', 'string', 'max:50'],
            'bank_name'           => ['nullable', 'string', 'max:100'],
            'bank_account'        => ['nullable', 'string', 'max:50'],
            'iban'                => ['nullable', 'string', 'max:50'],
            'swift'               => ['nullable', 'string', 'max:20'],
            'currency'            => ['nullable', 'string', 'size:3'],
            'timezone'            => ['nullable', 'string', 'max:50'],
            'logo'                => ['nullable', 'image', 'max:2048'],
            'remove_logo'         => ['nullable', 'boolean'],
        ];
    }
}
