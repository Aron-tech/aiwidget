<?php

namespace App\Enums;

enum FileTypeEnum: string
{
    case PDF = 'pdf';
    case DOCX = 'docx';
    case TXT = 'txt';

    case HTML = 'html';

    case MD = 'md';
}
