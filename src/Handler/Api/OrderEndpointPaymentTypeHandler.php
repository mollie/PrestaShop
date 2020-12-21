<?php

namespace Mollie\Handler\Api;

use Mollie\Adapter\ToolsAdapter;
use Mollie\Enum\PaymentTypeEnum;
use MolliePrefix\Mollie\Api\Endpoints\OrderEndpoint;

class OrderEndpointPaymentTypeHandler implements OrderEndpointPaymentTypeHandlerInterface
{
    /**
     * @var ToolsAdapter
     */
    private $toolsAdapter;

    public function __construct(ToolsAdapter $toolsAdapter)
    {
        $this->toolsAdapter = $toolsAdapter;
    }

    /**
     * @param int $transactionId
     *
     * @return int
     */
    public function retrievePaymentTypeFromTransactionId($transactionId)
    {
        if ($this->isRegularPayment($transactionId)) {
            return PaymentTypeEnum::PAYMENT_TYPE_REGULAR;
        }

        return PaymentTypeEnum::PAYMENT_TYPE_NOT_FOUND;
    }

    /**
     * @param int $transactionId
     *
     * @return bool
     */
    private function isRegularPayment($transactionId)
    {
        $resourceIdPrefix = OrderEndpoint::RESOURCE_ID_PREFIX;
        $length = $this->toolsAdapter->strlen($resourceIdPrefix);

        if (!$length) {
            return false;
        }

        if ($resourceIdPrefix === $this->toolsAdapter->substr($transactionId, 0, $length)) {
            return false;
        }

        return true;
    }
}
