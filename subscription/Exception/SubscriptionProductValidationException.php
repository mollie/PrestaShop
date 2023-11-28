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

class SubscriptionProductValidationException extends MollieSubscriptionException
{
    public static function cartAlreadyHasSubscriptionProduct(): self
    {
        return new self(
            'Cart already has subscription product',
            ExceptionCode::CART_ALREADY_HAS_SUBSCRIPTION_PRODUCT
        );
    }
}
