<?php

namespace Mollie\Utility;

class MollieStatusUtility
{
    public static function isPaymentFinished($paymentStatus)
    {
        switch ($paymentStatus) {
            case \_PhpScoper5eddef0da618a\Mollie\Api\Types\OrderStatus::STATUS_COMPLETED:
            case \_PhpScoper5eddef0da618a\Mollie\Api\Types\OrderStatus::STATUS_PAID:
            case \_PhpScoper5eddef0da618a\Mollie\Api\Types\OrderStatus::STATUS_SHIPPING:
            case \_PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentStatus::STATUS_AUTHORIZED:
            case \_PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentStatus::STATUS_PAID:
                return true;
            default:
                return false;
        }
    }
}