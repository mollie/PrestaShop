<?php

namespace Mollie\Utility;

use _PhpScoper5eddef0da618a\Mollie\Api\Endpoints\OrderEndpoint;
use Tools;

class TransactionUtility
{
    public static function isOrderTransaction($transactionId)
    {
        $length = Tools::strlen(OrderEndpoint::RESOURCE_ID_PREFIX);

        return Tools::substr($transactionId, 0, $length) === OrderEndpoint::RESOURCE_ID_PREFIX;
    }
}