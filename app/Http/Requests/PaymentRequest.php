<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $invoice = $this->route('invoice');
        $maxAmount = $invoice ? $invoice->balance : 999999999;

        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $maxAmount],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference' => [
                'nullable',
                'string',
                'max:100',
                // Référence obligatoire pour certains moyens de paiement
                Rule::requiredIf(function () {
                    $method = $this->input('payment_method');
                    return in_array($method, ['check', 'bank_transfer']);
                }),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.min' => 'Le montant doit être supérieur à 0.',
            'amount.max' => 'Le montant ne peut pas dépasser le solde restant.',
            'payment_date.required' => 'La date de paiement est obligatoire.',
            'payment_date.before_or_equal' => 'La date de paiement ne peut pas être dans le futur.',
            'payment_method.required' => 'Le mode de paiement est obligatoire.',
            'reference.required_if' => 'La référence est obligatoire pour ce mode de paiement.',
        ];
    }
}
