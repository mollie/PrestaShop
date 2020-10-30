<?php

namespace Mollie\Service\PaymentMethod;

use MolPaymentMethod;

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
