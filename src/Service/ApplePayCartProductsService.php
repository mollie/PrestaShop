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

namespace Mollie\Service;

use Cart;
use Mollie\DTO\ApplePay\Product;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApplePayCartProductsService
{
    /**
     * @param Product[] $products
     */
    public function addWantedProducts(Cart $cart, array $products): void
    {
        foreach ($products as $product) {
            // a customization row belongs to one cart and deleteProduct() drops it with the line,
            // so a customized line the cart already holds is quoted as it stands
            if ($product->getCustomizationId() && $this->getQuantityInCart($cart, $product, $product->getCustomizationId())) {
                continue;
            }

            $quantity = max($product->getWantedQuantity(), 1);

            // deleteProduct() runs CartRule::autoRemoveFromCart() on the emptied cart, which drops
            // vouchers the shopper entered by code, so a cart already holding the line is left alone
            if ($this->getQuantityInCart($cart, $product, 0) === $quantity) {
                continue;
            }

            $cart->deleteProduct($product->getProductId(), $product->getProductAttribute());
            $cart->updateQty($quantity, $product->getProductId(), $product->getProductAttribute());
        }
    }

    private function getQuantityInCart(Cart $cart, Product $product, int $customizationId): int
    {
        $inCart = $cart->getProductQuantity($product->getProductId(), $product->getProductAttribute(), $customizationId);

        return (int) ($inCart['quantity'] ?? 0);
    }
}
