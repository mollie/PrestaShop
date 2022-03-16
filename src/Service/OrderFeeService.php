<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Service;

use Mollie\Utility\PaymentFeeUtility;
use MolOrderFee;
use MolPaymentMethod;
use PrestaShopException;
use Tools;

class OrderFeeService
{
    public function getPaymentFees($methods, $totalPrice)
    {
        foreach ($methods as $index => $method) {
            if (0 === (int) $method['surcharge']) {
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

    public function createOrderFee($cartId, $orderFee)
    {
        $orderFeeObj = new MolOrderFee();
        $orderFeeObj->id_cart = (int) $cartId;
        $orderFeeObj->order_fee = $orderFee;
        try {
            $orderFeeObj->add();
        } catch (\Exception $e) {
            $errorHandler = \Mollie\Handler\ErrorHandler\ErrorHandler::getInstance();
            $errorHandler->handle($e, $e->getCode(), false);
            throw new PrestaShopException('Can\'t save Order fee');
        }
    }
}
