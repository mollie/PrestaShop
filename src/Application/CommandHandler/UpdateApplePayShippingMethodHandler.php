<?php

namespace Mollie\Application\CommandHandler;

use Cart;
use Mollie\Application\Command\UpdateApplePayShippingMethod;
use Mollie\Config\Config;
use Mollie\Service\OrderFeeService;

final class UpdateApplePayShippingMethodHandler
{
    /**
     * @var OrderFeeService
     */
    private $orderFeeService;

    public function __construct(OrderFeeService $orderFeeService)
    {
        $this->orderFeeService = $orderFeeService;
    }

    public function handle(UpdateApplePayShippingMethod $command): array
    {
        $cart = new Cart($command->getCartId());
        $cart->id_carrier = $command->getCarrierId();
        $cart->setDeliveryOption([
           $cart->id_address_delivery => $command->getCarrierId() . ',',
       ]);
        $cart->update();
        $orderTotal = $cart->getOrderTotal(true, Cart::BOTH, null, $command->getCarrierId());
        $fee = $this->orderFeeService->getPaymentFee($orderTotal, Config::APPLEPAY);

        return [
           'success' => true,
           'data' => [
               'amount' => $orderTotal + $fee,
           ],
       ];
    }
}
