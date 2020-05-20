<?php

namespace Mollie\Service;

use Mollie\Utility\PaymentFeeUtility;
use MolPaymentMethod;
use Tools;

class OrderFeeService
{
    public function getPaymentFees($methods, $totalPrice)
    {
        foreach ($methods as $index => $method) {
            if ((int)$method['surcharge'] === 0) {
                $methods[$index]['fee'] = false;
                $methods[$index]['fee_display'] = false;
                continue;
            }
            $paymentMethod = new MolPaymentMethod($method['id_payment_method']);
            $paymentFee = PaymentFeeUtility::getPaymentFee($paymentMethod, $totalPrice);
            $methods[$index]['fee'] = $paymentFee;
            $methods[$index]['fee_display'] = Tools::displayPrice($paymentFee);
        }

        return $methods;
    }
}