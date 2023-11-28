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

class CouldNotValidateSubscriptionSettings extends MollieSubscriptionException
{
    public static function subscriptionServiceDisabled(): self
    {
        return new self(
            'Subscription service disabled.',
            ExceptionCode::CART_SUBSCRIPTION_SERVICE_DISABLED
        );
    }

    public static function subscriptionCarrierInvalid(): self
    {
        return new self(
            'Subscription carrier invalid.',
            ExceptionCode::CART_SUBSCRIPTION_CARRIER_INVALID
        );
    }
}
