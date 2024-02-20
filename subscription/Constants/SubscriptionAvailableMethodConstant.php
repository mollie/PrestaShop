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

namespace Mollie\Subscription\Constants;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionAvailableMethodConstant
{
    public const CREDIT_CARD = 'creditcard';
    public const DIRECT_DEBIT = 'directdebit';
    public const PAYPAL = 'paypal';
    public const NULL = 'null';
}
