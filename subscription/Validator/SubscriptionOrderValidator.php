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

    public function validate(Cart $cart): bool
    {
        $products = $cart->getProducts();
        // only one product can be subscribed at a time
        if (count($products) !== 1) {
            return false;
        }

        // checks if product is subscription product
        // foreach is not necessary but might need to add more possible products for subscription in later updates
        foreach ($products as $product) {
            if ($this->subscriptionProduct->validate((int) $product['id_product_attribute'])) {
                return true;
            }
        }

        return false;
    }
}
