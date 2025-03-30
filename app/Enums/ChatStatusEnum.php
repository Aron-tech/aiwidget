<?php

namespace App\Enums;

enum ChatStatusEnum: string
{
    case INACTIVE = 'inactive';
    case OPEN = 'open';
    case WAITING = 'waiting';
    case CLOSED = 'closed';
}