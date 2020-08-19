<?php

namespace Mollie\Utility;

use _PhpScoper5eddef0da618a\Mollie\Api\Types\OrderStatus;
use _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentStatus;
use Mollie\Config\Config;

class MollieStatusUtility
{
    public static function isPaymentFinished($paymentStatus)
    {
        switch ($paymentStatus) {
            case OrderStatus::STATUS_COMPLETED:
            case OrderStatus::STATUS_PAID:
            case OrderStatus::STATUS_SHIPPING:
            case PaymentStatus::STATUS_AUTHORIZED:
            case PaymentStatus::STATUS_PAID:
            case Config::STATUS_PAID_ON_BACKORDER:
                return true;
            default:
                return false;
        }
    }
}