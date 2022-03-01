<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */

namespace Mollie\DTO\ApplePay;

class Order
{

    /**
     * @var Product
     */
    private $product;
    /**
     * @var ShippingContent
     */
    private $shippingContent;
    /**
     * @var ShippingContent
     */
    private $billingContent;

    public function __construct(
        Product $product,
        ShippingContent $shippingContent,
        ShippingContent $billingContent
    ) {
        $this->product = $product;
        $this->shippingContent = $shippingContent;
        $this->billingContent = $billingContent;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getShippingContent(): ShippingContent
    {
        return $this->shippingContent;
    }

    public function getBillingContent(): ShippingContent
    {
        return $this->billingContent;
    }
}
