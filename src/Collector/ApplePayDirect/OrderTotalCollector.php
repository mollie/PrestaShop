<?php

namespace Mollie\Collector\ApplePayDirect;

use Cart;
use Mollie\Config\Config;
use Mollie\DTO\ApplePay\Carrier\Carrier as AppleCarrier;
use Mollie\Service\OrderFeeService;

class OrderTotalCollector
{
    /** @var OrderFeeService */
    private $orderFeeService;

    public function __construct(OrderFeeService $orderFeeService)
    {
        $this->orderFeeService = $orderFeeService;
    }

    /**
     * @param AppleCarrier[] $applePayCarriers
     * @param Cart $cart
     *
     * @return array|array[]
     *
     * @throws \Exception
     */
    public function getOrderTotals($applePayCarriers, Cart $cart)
    {
        return array_map(function (AppleCarrier $carrier) use ($cart) {
            $orderTotal = (float) number_format($cart->getOrderTotal(true, Cart::BOTH, null, $carrier->getCarrierId()), 2, '.', '');
            $paymentFee = $this->orderFeeService->getPaymentFee($orderTotal, Config::APPLEPAY);

            return [
                'type' => 'final',
                'label' => $carrier->getName(),
                'amount' => $orderTotal + $paymentFee,
                'amountWithoutFee' => $orderTotal,
            ];
        }, $applePayCarriers);
    }
}
