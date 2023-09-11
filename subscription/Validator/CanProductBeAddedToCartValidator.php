<?php

declare(strict_types=1);

namespace Mollie\Subscription\Validator;

use Mollie\Adapter\CartAdapter;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Subscription\Exception\ExceptionCode;
use Mollie\Subscription\Exception\SubscriptionProductValidationException;

class CanProductBeAddedToCartValidator
{
    /** @var CartAdapter */
    private $cart;

    /** @var SubscriptionProductValidator */
    private $subscriptionProduct;

    /** @var ToolsAdapter */
    private $tools;

    public function __construct(
        CartAdapter $cart,
        SubscriptionProductValidator $subscriptionProduct,
        ToolsAdapter $tools
    ) {
        $this->cart = $cart;
        $this->subscriptionProduct = $subscriptionProduct;
        $this->tools = $tools;
    }

    /**
     * Validates if product can be added to the cart.
     * Only 1 subscription product can be to the cart
     *
     * @param int $productAttributeId
     *
     * @return bool
     *
     * @throws SubscriptionProductValidationException
     */
    public function validate(int $productAttributeId): bool
    {
        $isSubscriptionDuplicateProduct = $this->tools->getValue('controller');

        if ($isSubscriptionDuplicateProduct === 'subscriptionWebhook') {
            return true;
        }

        $isNewSubscriptionProduct = $this->subscriptionProduct->validate($productAttributeId);

        return !$isNewSubscriptionProduct || $this->validateIfSubscriptionProductCanBeAdded($productAttributeId);
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

        foreach ($cartProducts as $cartProduct) {
            if (!$this->subscriptionProduct->validate((int) $cartProduct['id_product_attribute'])) {
                continue;
            }

            if ((int) $cartProduct['id_product_attribute'] === $productAttributeId) {
                continue;
            }

            throw new SubscriptionProductValidationException('Cart already has subscription product', ExceptionCode::CART_ALREADY_HAS_SUBSCRIPTION_PRODUCT);
        }

        return true;
    }
}
