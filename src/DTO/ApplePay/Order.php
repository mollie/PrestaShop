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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Order
{
    /**
     * @var Product[]
     */
    private $products;
    /**
     * @var ShippingContent
     */
    private $shippingContent;
    /**
     * @var ShippingContent
     */
    private $billingContent;

    public function __construct(
        array $products,
        ShippingContent $shippingContent,
        ShippingContent $billingContent
    ) {
        $this->products = $products;
        $this->shippingContent = $shippingContent;
        $this->billingContent = $billingContent;
    }

    public function getProducts(): array
    {
        return $this->products;
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
