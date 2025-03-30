<?php

namespace App\Enums;

enum MessageSenderRolesEnum: string
{
    case BOT = 'bot';
    case USER = 'user';
    case ADMIN = 'admin';
}
