<?php

namespace App\Enums;

enum TransactionSourceTypeEnum: string
{
    case CREDIT_CARD = 'credit_card';
    case NORMAL = 'normal';
    case RECONCILIATION = 'reconciliation';
}
