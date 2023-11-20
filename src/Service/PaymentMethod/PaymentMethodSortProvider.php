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

namespace Mollie\Service\PaymentMethod;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class PaymentMethodSortProvider implements PaymentMethodSortProviderInterface
{
    public function getSortedInAscendingWayForCheckout(array $paymentMethods)
    {
        usort($paymentMethods, function (array $a, array $b) {
            if ($a['position'] === $b['position']) {
                return 0;
            }

            return ($a['position'] < $b['position']) ? -1 : 1;
        });

        return $paymentMethods;
    }

    public function getSortedInAscendingWayForConfiguration(array $paymentMethods)
    {
        usort($paymentMethods, function (array $a, array $b) {
            if ($a['obj']->position === $b['obj']->position) {
                return 0;
            }

            return ($a['obj']->position < $b['obj']->position) ? -1 : 1;
        });

        return $paymentMethods;
    }
}
