<?php

namespace App\Enums;

enum KeyTypesEnum: string
{
    case MODERATOR = 'moderator';
    case CUSTOMER = 'customer';
    case DEVELOPER = 'developer';


    public function getLabel(): string
    {
        return match ($this) {
            KeyTypesEnum::MODERATOR => __('enum.moderator'),
            KeyTypesEnum::CUSTOMER => __('enum.customer'),
            KeyTypesEnum::DEVELOPER => __('enum.developer'),
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn ($case) => [$case->value => $case->getLabel()]
        )->toArray();
    }
}
