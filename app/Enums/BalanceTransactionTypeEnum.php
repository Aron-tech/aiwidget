<?php

namespace App\Enums;

enum BalanceTransactionTypeEnum: string
{
    case CREDIT = 'credit';
    case DEBIT  = 'debit';
}
