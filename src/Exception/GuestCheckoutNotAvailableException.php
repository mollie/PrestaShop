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

namespace Mollie\Exception;

use Mollie\Exception\Code\ExceptionCode;
use Throwable;

if (!defined('_PS_VERSION_')) {
    exit;
}

class GuestCheckoutNotAvailableException extends MollieException
{
    public static function guestCheckoutDisabled(Throwable $exception = null): self
    {
        return new self(
            'Guest checkout is not available.',
            ExceptionCode::INFRASTRUCTURE_GUEST_CHECKOUT_NOT_AVAILABLE,
            $exception
        );
    }
}
