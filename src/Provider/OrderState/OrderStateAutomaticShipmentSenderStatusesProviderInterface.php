<?php

namespace Mollie\Provider\OrderState;

interface OrderStateAutomaticShipmentSenderStatusesProviderInterface
{
    /**
     * @return array
     */
    public function provideAutomaticShipmentSenderStatuses();
}