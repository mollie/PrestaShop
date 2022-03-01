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

class ApplePayProductBuilder
{
    public function build(array $orderParams): Order
    {
        $productParams = $orderParams['product'];

        return new Order(
            $this->buildAppleProduct($productParams),
            $this->buildShippingContent($orderParams['shippingContact']),
            $this->buildShippingContent($orderParams['billingContact'])
        );
    }

    private function buildAppleProduct(array $params)
    {
        return new Product(
            $params['id_product'],
            $params['id_product_attribute'],
            $params['quantity_wanted']
        );
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
