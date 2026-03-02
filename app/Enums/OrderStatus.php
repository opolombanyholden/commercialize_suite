<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::CONFIRMED => 'Confirmée',
            self::PROCESSING => 'En préparation',
            self::SHIPPED => 'Expédiée',
            self::DELIVERED => 'Livrée',
            self::CANCELLED => 'Annulée',
            self::REFUNDED => 'Remboursée',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'info',
            self::PROCESSING => 'primary',
            self::SHIPPED => 'success',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
            self::REFUNDED => 'secondary',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'fa-clock',
            self::CONFIRMED => 'fa-check',
            self::PROCESSING => 'fa-cog',
            self::SHIPPED => 'fa-truck',
            self::DELIVERED => 'fa-box-open',
            self::CANCELLED => 'fa-times',
            self::REFUNDED => 'fa-undo',
        };
    }

    public function isCancellable(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED, self::PROCESSING]);
    }

    public function isRefundable(): bool
    {
        return in_array($this, [self::CONFIRMED, self::PROCESSING, self::SHIPPED, self::DELIVERED]);
    }
}
