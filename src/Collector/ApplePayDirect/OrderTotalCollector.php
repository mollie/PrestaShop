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

namespace Mollie\Collector\ApplePayDirect;

use Cart;
use Mollie\Config\Config;
use Mollie\DTO\ApplePay\Carrier\Carrier as AppleCarrier;
use Mollie\Service\OrderPaymentFeeService;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderTotalCollector
{
    /** @var OrderPaymentFeeService */
    private $orderPaymentFeeService;

    public function __construct(OrderPaymentFeeService $orderPaymentFeeService)
    {
        $this->orderPaymentFeeService = $orderPaymentFeeService;
    }

    /**
     * @param AppleCarrier[] $applePayCarriers
     *
     * @return array|array
     *
     * @throws \Exception
     */
    public function getOrderTotals($applePayCarriers, Cart $cart)
    {
        return array_map(function (AppleCarrier $carrier) use ($cart) {
            $orderTotal = (float) number_format($cart->getOrderTotal(true, Cart::BOTH, null, $carrier->getCarrierId()), 2, '.', '');

            $paymentFeeData = $this->orderPaymentFeeService->getPaymentFee($orderTotal, Config::APPLEPAY);

            $paymentFee = $paymentFeeData->getPaymentFeeTaxIncl();

            return [
                'type' => 'final',
                'label' => $carrier->getName(),
                'amount' => $orderTotal + $paymentFee,
                'amountWithoutFee' => $orderTotal,
            ];
        }, $applePayCarriers);
    }
}
