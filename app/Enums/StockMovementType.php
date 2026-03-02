<?php

namespace App\Enums;

enum StockMovementType: string
{
    case IN = 'in';
    case OUT = 'out';
    case TRANSFER = 'transfer';
    case ADJUSTMENT = 'adjustment';

    public function label(): string
    {
        return match($this) {
            self::IN => 'Entrée',
            self::OUT => 'Sortie',
            self::TRANSFER => 'Transfert',
            self::ADJUSTMENT => 'Ajustement',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::IN => 'success',
            self::OUT => 'danger',
            self::TRANSFER => 'info',
            self::ADJUSTMENT => 'warning',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::IN => 'fa-arrow-down',
            self::OUT => 'fa-arrow-up',
            self::TRANSFER => 'fa-exchange-alt',
            self::ADJUSTMENT => 'fa-balance-scale',
        };
    }

    public function increasesStock(): bool
    {
        return $this === self::IN;
    }

    public function decreasesStock(): bool
    {
        return $this === self::OUT;
    }
}
