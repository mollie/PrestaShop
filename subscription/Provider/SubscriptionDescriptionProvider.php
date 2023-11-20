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

namespace Mollie\Subscription\Provider;

use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionDescriptionProvider
{
    public function getSubscriptionDescription(Order $order)
    {
        return implode('-', [
            'subscription',
            $order->reference,
        ]);
    }
}
