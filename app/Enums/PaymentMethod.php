<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case CHECK = 'check';
    case BANK_TRANSFER = 'bank_transfer';
    case CREDIT_CARD = 'credit_card';
    case MOBILE_MONEY = 'mobile_money';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::CASH => 'Espèces',
            self::CHECK => 'Chèque',
            self::BANK_TRANSFER => 'Virement bancaire',
            self::CREDIT_CARD => 'Carte bancaire',
            self::MOBILE_MONEY => 'Mobile Money',
            self::OTHER => 'Autre',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::CASH => 'fa-money-bill-wave',
            self::CHECK => 'fa-money-check',
            self::BANK_TRANSFER => 'fa-university',
            self::CREDIT_CARD => 'fa-credit-card',
            self::MOBILE_MONEY => 'fa-mobile-alt',
            self::OTHER => 'fa-ellipsis-h',
        };
    }

    public function requiresReference(): bool
    {
        return in_array($this, [self::CHECK, self::BANK_TRANSFER, self::MOBILE_MONEY]);
    }
}
