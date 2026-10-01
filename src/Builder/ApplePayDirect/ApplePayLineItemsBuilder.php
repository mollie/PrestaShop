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

namespace Mollie\Builder\ApplePayDirect;

use Cart;
use Mollie\Factory\ModuleFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApplePayLineItemsBuilder
{
    const FILE_NAME = 'ApplePayLineItemsBuilder';

    /** @var \Mollie */
    private $module;

    public function __construct(ModuleFactory $moduleFactory)
    {
        $this->module = $moduleFactory->getModule();
    }

    /**
     * Splits the cart total (without payment fee) into the lines shown above the sheet total.
     * Priced through the cart's current delivery option, so callers set it first.
     */
    public function build(Cart $cart, float $orderTotal, float $paymentFee): array
    {
        $productsTaxExcl = $this->round($cart->getOrderTotal(false, Cart::ONLY_PRODUCTS));
        $shipping = $this->round($cart->getOrderTotal(true, Cart::ONLY_SHIPPING));
        $discounts = $this->round($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS));

        // VAT takes the remainder, so the lines always add up to the charged total
        // even when PrestaShop rounds per line or per item
        $tax = $this->round($orderTotal - $productsTaxExcl - $shipping + $discounts);

        $lineItems = [
            $this->line($this->module->l('Products (tax excl.)', self::FILE_NAME), $productsTaxExcl),
        ];

        if ($discounts > 0) {
            $lineItems[] = $this->line($this->module->l('Discount', self::FILE_NAME), -$discounts);
        }

        $lineItems[] = $this->line($this->module->l('VAT', self::FILE_NAME), $tax);
        $lineItems[] = $this->line($this->module->l('Shipping', self::FILE_NAME), $shipping);

        if ($paymentFee > 0) {
            $lineItems[] = $this->line($this->module->l('Payment fee', self::FILE_NAME), $paymentFee);
        }

        return $lineItems;
    }

    private function line(string $label, float $amount): array
    {
        return [
            'type' => 'final',
            'label' => $label,
            'amount' => number_format($amount, 2, '.', ''),
        ];
    }

    private function round($amount): float
    {
        return (float) number_format((float) $amount, 2, '.', '');
    }
}
