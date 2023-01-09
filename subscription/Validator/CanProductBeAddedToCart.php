<?php

declare(strict_types=1);

namespace Mollie\Subscription\Validator;

use Cart;
use Mollie\Subscription\Exception\ProductValidationException;
use Mollie\Subscription\Exception\SubscriptionProductValidationException;

class CanProductBeAddedToCart
{
    /** @var Cart */
    private $cart;

    /** @var SubscriptionProduct */
    private $subscriptionProduct;

    /** @var string */
    private $error;

    public function __construct(Cart $cart, SubscriptionProduct $subscriptionProduct)
    {
        $this->cart = $cart;
        $this->subscriptionProduct = $subscriptionProduct;
    }

    /**
     * Validates if product can be added to the cart.
     * Only 1 subscription product can be in cart and no other products can be added if there are subscription products
     * For now we only allow one subscription product with any quantities, later might need to add logic to allow more products
     *
     * @param int $productAttributeId
     *
     * @return bool
     */
    public function validate(int $productAttributeId): bool
    {
        $isNewSubscriptionProduct = $this->subscriptionProduct->validate($productAttributeId);

        if ($isNewSubscriptionProduct) {
            return $this->validateIfSubscriptionProductCanBeAdded($productAttributeId);
        }

        return $this->validateIfProductCanBeAdded();
    }

    /**
     * @param int $productAttributeId
     *
     * @return bool
     *
     * @throws SubscriptionProductValidationException
     */
    private function validateIfSubscriptionProductCanBeAdded(int $productAttributeId): bool
    {
        $cartProducts = $this->cart->getProducts();
        $numberOfProductsInCart = count($cartProducts);
        // we can only have 1 product in cart if its subscription product
        if ($numberOfProductsInCart > 1) {
            throw new SubscriptionProductValidationException('Cart has multiple products', SubscriptionProductValidationException::MULTTIPLE_PRODUCTS_IN_CART);
        }

        // if it's the same product we can add more of the same product
        if ($numberOfProductsInCart === 1) {
            $cartProduct = reset($cartProducts);

            $isTheSameProduct = $productAttributeId === (int) $cartProduct['id_product_attribute'];

            if (!$isTheSameProduct) {
                throw new SubscriptionProductValidationException('Cart has multiple products', SubscriptionProductValidationException::MULTTIPLE_PRODUCTS_IN_CART);
            }
        }

        return true;
    }

    /**
     * @return bool
     *
     * @throws ProductValidationException
     */
    private function validateIfProductCanBeAdded(): bool
    {
        $cartProducts = $this->cart->getProducts();
        foreach ($cartProducts as $cartProduct) {
            $isSubscriptionProduct = $this->subscriptionProduct->validate((int) $cartProduct['id_product_attribute']);
            if ($isSubscriptionProduct) {
                throw new ProductValidationException('Cart has subscription products', ProductValidationException::SUBSCRIPTTION_PRODUCTS_IN_CART);
            }
        }

        return true;
    }
}
