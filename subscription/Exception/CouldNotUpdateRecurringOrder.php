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

class CouldNotUpdateRecurringOrder extends MollieSubscriptionException
{
    public static function failedToFindOrder(int $orderId): self
    {
        return new self(
            sprintf('Failed to find order. Order ID: (%s)', $orderId),
            ExceptionCode::RECURRING_ORDER_FAILED_TO_FIND_ORDER
        );
    }
}
