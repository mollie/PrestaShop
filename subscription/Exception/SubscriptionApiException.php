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

class SubscriptionApiException extends MollieSubscriptionException
{
    public const CREATION_FAILED = 0;

    public const CANCELLATION_FAILED = 10;

    public const GETTER_FAILED = 20;

    public const UPDATE_FAILED = 30;
}
