<?php

namespace Mollie\Handler\Settings;

interface PaymentMethodPositionHandlerInterface
{
    /**
     * @param array $positions - key is id of MolPaymentMethod and value is numeric position.
     * @return mixed
     */
    public function savePositions(array $positions);
}
