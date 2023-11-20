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

use Mollie\DTO\ApplePay\Product;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApplePayProductBuilder
{
    public function build(array $productParams): array
    {
        $products = [];
        foreach ($productParams as $product) {
            $products[] = new Product(
                $product['id_product'],
                $product['id_product_attribute'],
                $product['quantity_wanted']
            );
        }

        return $products;
    }
}
