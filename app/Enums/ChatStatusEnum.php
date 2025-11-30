<?php

namespace App\Enums;

enum ChatStatusEnum: string
{
    case INACTIVE = 'inactive';
    case OPEN = 'open';
    case WAITING = 'waiting';
    case CLOSED = 'closed';

    function getLabel(): string
    {
        return match ($this) {
            self::INACTIVE => __('interface.inactive'),
            self::OPEN => __('interface.open'),
            self::WAITING => __('interface.waiting'),
            self::CLOSED => __('interface.closed'),
        };
    }
}
