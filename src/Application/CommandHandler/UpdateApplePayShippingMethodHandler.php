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

use Carrier;
use Cart;
use Mollie\Application\Command\UpdateApplePayShippingMethod;
use Mollie\Builder\ApplePayDirect\ApplePayCarriersBuilder;
use Mollie\Config\Config;
use Mollie\Service\OrderPaymentFeeService;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class UpdateApplePayShippingMethodHandler
{
    /**
     * @var OrderPaymentFeeService
     */
    private $orderPaymentFeeService;

    /**
     * @var ApplePayCarriersBuilder
     */
    private $applePayCarriersBuilder;

    public function __construct(
        OrderPaymentFeeService $orderPaymentFeeService,
        ApplePayCarriersBuilder $applePayCarriersBuilder
    ) {
        $this->orderPaymentFeeService = $orderPaymentFeeService;
        $this->applePayCarriersBuilder = $applePayCarriersBuilder;
    }

    public function handle(UpdateApplePayShippingMethod $command): array
    {
        $carrier = new Carrier($command->getCarrierId());

        if (in_array(
            (int) $carrier->id_reference,
            $this->applePayCarriersBuilder->getExcludedCarrierReferences(),
            true
        )) {
            return [
                'success' => false,
            ];
        }

        $cart = new Cart($command->getCartId());

        $cart->id_carrier = $command->getCarrierId();
        $cart->setDeliveryOption([
           $cart->id_address_delivery => $command->getCarrierId() . ',',
       ]);

        $cart->update();

        $orderTotal = (float) $cart->getOrderTotal(true, Cart::BOTH);

        $paymentFeeData = $this->orderPaymentFeeService->getPaymentFee($orderTotal, Config::APPLEPAY);

        $paymentFee = $paymentFeeData->getPaymentFeeTaxIncl();

        return [
           'success' => true,
           'data' => [// TODO use calculator
               'amount' => number_format($orderTotal + $paymentFee, 2, '.', ''),
           ],
       ];
    }
}
