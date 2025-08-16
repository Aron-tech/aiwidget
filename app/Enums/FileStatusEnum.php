<?php

namespace App\Enums;

enum FileStatusEnum : string
{
    case UPLOADED = 'uploaded';
    case PROCESSING = 'processing';
    case INDEXED = 'indexed';
    case FAILED = 'failed';
}
