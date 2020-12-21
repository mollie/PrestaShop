<?php

namespace Mollie\Handler\Api;

interface OrderEndpointPaymentTypeHandlerInterface
{
    /**
     * @param int $transactionId
     *
     * @return int
     */
    public function retrievePaymentTypeFromTransactionId($transactionId);
}