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

        $orderTotal = (float) $cart->getOrderTotal(true, Cart::BOTH, null, $command->getCarrierId());

        $paymentFeeData = $this->orderFeeService->getPaymentFee($orderTotal, Config::APPLEPAY);

        $paymentFee = $paymentFeeData->getPaymentFeeTaxIncl();

        return [
           'success' => true,
           'data' => [// TODO use calculator
               'amount' => $orderTotal + $paymentFee,
           ],
       ];
    }
}
