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

class CouldNotCreateSubscription extends MollieSubscriptionException
{
    public static function invalidSubscriptionSettings(\Throwable $exception): self
    {
        return new self(
            'Invalid subscription settings.',
            ExceptionCode::ORDER_INVALID_SUBSCRIPTION_SETTINGS,
            $exception
        );
    }

    public static function failedToFindSubscriptionProduct(): self
    {
        return new self(
            'Failed to find subscription product.',
            ExceptionCode::ORDER_FAILED_TO_FIND_SUBSCRIPTION_PRODUCT
        );
    }

    public static function failedToCreateSubscriptionData(\Throwable $exception): self
    {
        return new self(
            'Failed to create subscription data.',
            ExceptionCode::ORDER_FAILED_TO_CREATE_SUBSCRIPTION_DATA,
            $exception
        );
    }

    public static function failedToSubscribeOrder(\Throwable $exception): self
    {
        return new self(
            'Failed to subscribe order.',
            ExceptionCode::ORDER_FAILED_TO_SUBSCRIBE_ORDER,
            $exception
        );
    }

    public static function failedToCreateRecurringOrdersProduct(\Throwable $exception): self
    {
        return new self(
            'Failed to create recurring orders product.',
            ExceptionCode::ORDER_FAILED_TO_CREATE_RECURRING_ORDERS_PRODUCT,
            $exception
        );
    }

    public static function failedToCreateRecurringOrder(\Throwable $exception): self
    {
        return new self(
            'Failed to create recurring order.',
            ExceptionCode::ORDER_FAILED_TO_CREATE_RECURRING_ORDER,
            $exception
        );
    }
}
