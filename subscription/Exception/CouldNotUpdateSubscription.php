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

class CouldNotUpdateSubscription extends MollieSubscriptionException
{
    public static function failedToUpdateSubscription(\Throwable $exception, string $subscriptionId): self
    {
        return new self(
            sprintf(
                'Failed to update subscription. Subscription ID: (%s)',
                $subscriptionId
            ),
            ExceptionCode::ORDER_FAILED_TO_UPDATE_SUBSCRIPTION,
            $exception
        );
    }
}
