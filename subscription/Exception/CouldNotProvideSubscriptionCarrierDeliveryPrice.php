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

class CouldNotProvideSubscriptionCarrierDeliveryPrice extends MollieSubscriptionException
{
    public static function failedToApplySelectedCarrier(int $subscriptionCarrierId): self
    {
        return new self(
            sprintf(
                'Failed to apply selected carrier. Subscription carrier ID: (%s)',
                $subscriptionCarrierId
            ),
            ExceptionCode::ORDER_FAILED_TO_APPLY_SELECTED_CARRIER
        );
    }

    public static function failedToGetSelectedCarrierPrice(int $subscriptionCarrierId): self
    {
        return new self(
            sprintf(
                'Failed to get selected carrier price. Subscription carrier ID: (%s)',
                $subscriptionCarrierId
            ),
            ExceptionCode::ORDER_FAILED_TO_GET_SELECTED_CARRIER_PRICE
        );
    }
}
