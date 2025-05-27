<?php

namespace App\Enums;
enum MessageSenderRolesEnum: string
{
    case BOT = 'bot';
    case USER = 'user';
    case ADMIN = 'admin';

    public function getLabel(): string
    {
        return match ($this) {
            self::BOT => __('Bot'),
            self::USER => __('User'),
            self::ADMIN => __('Admin'),
        };
    }
}
