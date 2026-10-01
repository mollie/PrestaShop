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
use Mollie\Builder\ApplePayDirect\ApplePayLineItemsBuilder;
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
    /** @var ApplePayLineItemsBuilder */
    private $lineItemsBuilder;

    public function __construct(OrderPaymentFeeService $orderPaymentFeeService, ApplePayLineItemsBuilder $lineItemsBuilder)
    {
        $this->orderPaymentFeeService = $orderPaymentFeeService;
        $this->lineItemsBuilder = $lineItemsBuilder;
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
        $originalDeliveryOption = $cart->delivery_option;

        // Apple Pay addresses are soft-deleted, which fails Customer::customerHasAddress inside
        // getPackageShippingCost, so explicit-carrier totals price shipping against the
        // default-country zone; pricing through the cart's delivery option resolves the real zone
        $totals = array_map(function (AppleCarrier $carrier) use ($cart) {
            // raw assignment on purpose: Cart::setDeliveryOption() fires CartRule::autoRemoveFromCart(),
            // which deletes vouchers from the shopper's real cart while this loop is only quoting
            $cart->delivery_option = json_encode([
                $cart->id_address_delivery => $carrier->getCarrierId() . ',',
            ]);

            $orderTotal = (float) number_format(
                $cart->getOrderTotal(true, Cart::BOTH),
                2,
                '.',
                ''
            );

            $paymentFeeData = $this->orderPaymentFeeService->getPaymentFee($orderTotal, Config::APPLEPAY);

            $paymentFee = $paymentFeeData->getPaymentFeeTaxIncl();

            return [
                'type' => 'final',
                'label' => $carrier->getName(),
                'amount' => number_format($orderTotal + $paymentFee, 2, '.', ''),
                'amountWithoutFee' => $orderTotal,
                'lineItems' => $this->lineItemsBuilder->build($cart, $orderTotal, $paymentFee),
            ];
        }, $applePayCarriers);

        $cart->delivery_option = $originalDeliveryOption;

        return $totals;
    }
}
