<?php

namespace Mollie\Utility;

use Tools;

class TransactionUtility
{
    public static function isOrderTransaction($transactionId)
    {
        return Tools::substr($transactionId, 0, 3) === 'ord';
    }
}