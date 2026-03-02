<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case VIEWED = 'viewed';
    case PARTIAL = 'partial';
    case PAID = 'paid';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Brouillon',
            self::SENT => 'Envoyée',
            self::VIEWED => 'Vue',
            self::PARTIAL => 'Paiement partiel',
            self::PAID => 'Payée',
            self::OVERDUE => 'En retard',
            self::CANCELLED => 'Annulée',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'secondary',
            self::SENT => 'info',
            self::VIEWED => 'primary',
            self::PARTIAL => 'warning',
            self::PAID => 'success',
            self::OVERDUE => 'danger',
            self::CANCELLED => 'dark',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::DRAFT => 'fa-file-alt',
            self::SENT => 'fa-paper-plane',
            self::VIEWED => 'fa-eye',
            self::PARTIAL => 'fa-hourglass-half',
            self::PAID => 'fa-check-circle',
            self::OVERDUE => 'fa-exclamation-triangle',
            self::CANCELLED => 'fa-times-circle',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::DRAFT]);
    }

    public function isDeletable(): bool
    {
        return in_array($this, [self::DRAFT, self::CANCELLED]);
    }
}
