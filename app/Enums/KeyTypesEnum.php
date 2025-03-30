<?php

namespace App\Enums;

enum KeyTypesEnum: string
{
    case MODERATOR = 'moderator';
    case OWNER = 'owner';
    case DEVELOPER = 'developer';


    public function getLabel(): string
    {
        return match ($this) {
            KeyTypesEnum::MODERATOR => 'Weboldal moderátor',
            KeyTypesEnum::OWNER => 'Weboldal tulajdonos',
            KeyTypesEnum::DEVELOPER => 'Fejlesztő',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn ($case) => [$case->value => $case->getLabel()]
        )->toArray();
    }
}