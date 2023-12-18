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

namespace Mollie\Subscription\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CouldNotHandleOriginalSubscriptionCartCloning extends MollieSubscriptionException
{
    public static function failedToFindCart(int $cartId): self
    {
        return new self(
            sprintf('Failed to find cart. Cart ID: (%s)', $cartId),
            ExceptionCode::RECURRING_ORDER_FAILED_TO_FIND_CART
        );
    }

    public static function failedToDuplicateCart(int $cartId): self
    {
        return new self(
            sprintf('Failed to duplicate cart. Cart ID: (%s)', $cartId),
            ExceptionCode::RECURRING_ORDER_FAILED_TO_DUPLICATE_CART
        );
    }

    public static function failedToFindRecurringOrderProduct(int $recurringOrderProduct): self
    {
        return new self(
            sprintf(
                'Failed to find recurring order product. Recurring order product ID: (%s)',
                $recurringOrderProduct
            ),
            ExceptionCode::RECURRING_ORDER_FAILED_TO_FIND_RECURRING_ORDER_PRODUCT
        );
    }

    public static function subscriptionCartShouldHaveOneProduct(int $cartId): self
    {
        return new self(
            sprintf(
                'Subscription cart should have one product. Cart ID: (%s)',
                $cartId
            ),
            ExceptionCode::RECURRING_ORDER_SUBSCRIPTION_CART_SHOULD_HAVE_ONE_PRODUCT
        );
    }

    public static function failedToCreateSpecificPrice(
        \Throwable $exception,
        int $productId,
        int $productAttributeId
    ): self {
        return new self(
            sprintf(
                'Failed to create specific price. Product ID: (%s), product attribute ID: (%s)',
                $productId,
                $productAttributeId
            ),
            ExceptionCode::RECURRING_ORDER_FAILED_TO_CREATE_SPECIFIC_PRICE,
            $exception
        );
    }
}
