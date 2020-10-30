<?php

namespace Mollie\Service\PaymentMethod;

/**
 * Payment methods are being retrieved both from api and the ones stored in database. The ones that are stored
 * can be dragged in admin so this service can be used to call anywhere and sort payment options accordingly.
 */
interface PaymentMethodSortProviderInterface
{
    /**
     * @param array $paymentMethods
     * @return array
     */
    public function getSortedInAscendingWayForCheckout(array $paymentMethods);

    /**
     * @param array $paymentMethods
     * @return array
     */
    public function getSortedInAscendingWayForConfiguration(array $paymentMethods);
}
