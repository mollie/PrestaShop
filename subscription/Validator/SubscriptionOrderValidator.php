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

declare(strict_types=1);

namespace Mollie\Subscription\Validator;

use Cart;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionOrderValidator
{
    /** @var SubscriptionProductValidator */
    private $subscriptionProduct;

    public function __construct(SubscriptionProductValidator $subscriptionProduct)
    {
        $this->subscriptionProduct = $subscriptionProduct;
    }

    /** Returns true if cart has subscription product */
    public function validate(Cart $cart): bool
    {
        $products = $cart->getProducts();

        $subscriptionProductCount = 0;

        // checks if one of cart products is subscription product
        foreach ($products as $product) {
            if ($this->subscriptionProduct->validate((int) $product['id_product_attribute'])) {
                ++$subscriptionProductCount;
            }
        }

        return $subscriptionProductCount > 0 && $subscriptionProductCount < 2;
    }
}
