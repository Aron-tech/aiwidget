<?php

namespace App\Enums;

enum SystemUsageFeePeriodEnum: string
{
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case SEMI_ANNUAL = 'semi_annual';
    case YEARLY = 'yearly';
    case TRIENNIAL = 'triennial';

    public function label(): string
    {
        return match ($this) {
            self::MONTHLY => __('interface.monthly') . ' - ' . self::getFee() . ' €',
            self::QUARTERLY => __('interface.quarterly') . ' - ' . self::getFee() . ' €',
            self::SEMI_ANNUAL => __('interface.semi_annual') . ' - ' . self::getFee() . ' €',
            self::YEARLY => __('interface.yearly') . ' - ' . self::getFee() . ' €',
            self::TRIENNIAL => __('interface.triennial') . ' - ' . self::getFee() . ' €',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn($case) => [$case->value => $case->getLabel()]
        )->toArray();
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function toDays(): int
    {
        return match ($this) {
            self::MONTHLY => 30,
            self::QUARTERLY => 90,
            self::SEMI_ANNUAL => 180,
            self::YEARLY => 365,
            self::TRIENNIAL => 1100,
        };
    }

    public function getFee(): float
    {
        return match ($this) {
            self::MONTHLY => 4.99,
            self::QUARTERLY => 13.99,
            self::SEMI_ANNUAL => 24.99,
            self::YEARLY => 45.99,
            self::TRIENNIAL => 129.99,
        };
    }
}
