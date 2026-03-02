<?php

namespace App\Enums;

enum UserVersion: string
{
    case LIGHT = 'light';
    case STANDARD = 'standard';
    case PRO = 'pro';
    case ENTERPRISE = 'enterprise';

    public function value(): int
    {
        return match($this) {
            self::LIGHT => 1,
            self::STANDARD => 2,
            self::PRO => 3,
            self::ENTERPRISE => 4,
        };
    }

    public function label(): string
    {
        return match($this) {
            self::LIGHT => 'Light (Gratuit)',
            self::STANDARD => 'Standard',
            self::PRO => 'Pro',
            self::ENTERPRISE => 'Entreprise',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::LIGHT => '#95a5a6',
            self::STANDARD => '#3498db',
            self::PRO => '#9b59b6',
            self::ENTERPRISE => '#e74c3c',
        };
    }

    public function monthlyPrice(): int
    {
        return match($this) {
            self::LIGHT => 0,
            self::STANDARD => 15000,
            self::PRO => 35000,
            self::ENTERPRISE => 75000,
        };
    }

    public function isAtLeast(UserVersion $version): bool
    {
        return $this->value() >= $version->value();
    }

    public static function all(): array
    {
        return [self::LIGHT, self::STANDARD, self::PRO, self::ENTERPRISE];
    }
}
