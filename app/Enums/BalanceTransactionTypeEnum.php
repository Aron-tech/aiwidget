<?php

namespace App\Enums;

enum BalanceTransactionTypeEnum: string
{
    case PURCHASE = 'purchase';
    case RECURRING = 'recurring';
    case DEPOSIT = 'deposit';
}
