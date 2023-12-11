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

class CouldNotProvideSubscriptionCarrierData extends MollieSubscriptionException
{
    public static function failedToFindOrder(int $orderId): self
    {
        return new self(
            sprintf(
                'Failed to find order. Order ID: (%s).',
                $orderId
            ),
            ExceptionCode::ORDER_FAILED_TO_FIND_ORDER
        );
    }

    public static function failedToFindSubscriptionProduct(): self
    {
        return new self(
            'Failed to find subscription product.',
            ExceptionCode::ORDER_FAILED_TO_FIND_SUBSCRIPTION_PRODUCT
        );
    }

    public static function failedToProvideSubscriptionOrderAmount(\Throwable $exception): self
    {
        return new self(
            'Failed to provide subscription order amount.',
            ExceptionCode::ORDER_FAILED_TO_PROVIDE_SUBSCRIPTION_ORDER_AMOUNT,
            $exception
        );
    }
}
