<?php

namespace App\Enums;

enum FileTypeEnum: string
{
    case PDF = 'pdf';
    case DOCX = 'docx';

    case DOC = 'doc';
    case TXT = 'txt';

    case HTML = 'html';

    case MD = 'md';
}
