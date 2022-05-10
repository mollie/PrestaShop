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

use Mollie\DTO\ApplePay\Order;
use Mollie\DTO\ApplePay\Product;
use Mollie\DTO\ApplePay\ShippingContent;

class ApplePayOrderBuilder
{
    public function build(array $products, array $shippingContent, array $billingContent): Order
    {
        return new Order(
            $this->buildAppleProduct($products),
            $this->buildShippingContent($shippingContent),
            $this->buildShippingContent($billingContent)
        );
    }

    private function buildAppleProduct(array $productsParams)
    {
        $products = [];
        foreach ($productsParams as $product) {
            $products[] = new Product(
                $product['id_product'],
                $product['id_product_attribute'],
                $product['quantity_wanted']
            );
        }

        return $products;
    }

    private function buildShippingContent(array $params)
    {
        return new ShippingContent(
            $params['addressLines'],
            $params['administrativeArea'],
            $params['country'],
            $params['countryCode'],
            $params['familyName'],
            $params['givenName'],
            $params['locality'],
            $params['postalCode'],
            $params['emailAddress'] ?? ''
        );
    }
}
