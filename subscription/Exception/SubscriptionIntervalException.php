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

declare(strict_types=1);

namespace Mollie\Subscription\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionIntervalException extends MollieSubscriptionException
{
    public static function failedToFindCombination(int $productAttributeId): self
    {
        return new self(
            sprintf(
                'Failed to find combination. Product attribute ID: (%s)',
                $productAttributeId
            ),
            ExceptionCode::ORDER_FAILED_TO_FIND_COMBINATION
        );
    }

    public static function failedToFindMatchingInterval(int $productAttributeId): self
    {
        return new self(
            sprintf(
                'Failed to find matching interval. Product attribute ID: (%s)',
                $productAttributeId
            ),
            ExceptionCode::ORDER_FAILED_TO_FIND_MATCHING_INTERVAL
        );
    }
}
