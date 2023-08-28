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

        // TODO add exception handling scenario where subscription fails to be created due to multiple subscription products but flow continues

        // checks if one of cart products is subscription product
        foreach ($products as $product) {
            if ($this->subscriptionProduct->validate((int) $product['id_product_attribute'])) {
                return true;
            }
        }

        return false;
    }
}
