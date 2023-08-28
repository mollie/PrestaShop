<?php

declare(strict_types=1);

namespace Mollie\Subscription\Validator;

use Cart;

class SubscriptionOrderValidator
{
    /** @var SubscriptionProductValidator */
    private $subscriptionProduct;

    public function __construct(SubscriptionProductValidator $subscriptionProduct)
    {
        $this->subscriptionProduct = $subscriptionProduct;
    }

    /** Returns true if cart has subscription products */
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
