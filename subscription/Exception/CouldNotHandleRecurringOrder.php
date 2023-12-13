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

class CouldNotHandleRecurringOrder extends MollieSubscriptionException
{
    public static function failedToHandleSubscriptionCartCloning(\Throwable $exception): self
    {
        return new self(
            'Failed to handle subscription cart cloning',
            ExceptionCode::RECURRING_ORDER_FAILED_TO_HANDLE_SUBSCRIPTION_CART_CLONING,
            $exception
        );
    }

    public static function failedToMatchSelectedCarrier(
        int $activeSubscriptionCarrierId,
        int $orderSubscriptionCarrierId
    ): self {
        return new self(
            sprintf('Failed to match selected carrier. active_carrier_id: (%s), order_carrier_id: (%s),',
                $activeSubscriptionCarrierId,
                $orderSubscriptionCarrierId
            ),
            ExceptionCode::RECURRING_ORDER_FAILED_TO_MATCH_SELECTED_CARRIER
        );
    }

    public static function failedToFindSelectedCarrier(): self
    {
        return new self(
            'Failed to find selected carrier',
            ExceptionCode::RECURRING_ORDER_FAILED_TO_FIND_SELECTED_CARRIER
        );
    }

    public static function failedToApplySelectedCarrier(int $expectedCarrierId, int $currentCarrierId): self
    {
        return new self(
            sprintf(
                'Failed to apply selected carrier. Expected carrier ID: (%s). Current carrier ID: (%s)',
                $expectedCarrierId,
                $currentCarrierId
            ),
            ExceptionCode::RECURRING_ORDER_FAILED_TO_APPLY_SELECTED_CARRIER
        );
    }

    public static function cartAndPaidPriceAreNotEqual(): self
    {
        return new self(
            'Cart and paid price are not equal',
            ExceptionCode::RECURRING_ORDER_CART_AND_PAID_PRICE_ARE_NOT_EQUAL
        );
    }
}
