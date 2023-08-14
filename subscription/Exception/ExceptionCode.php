<?php

namespace Mollie\Subscription\Exception;

class ExceptionCode
{
    //Order error codes starts from 1000

    public const ORDER_FAILED_TO_CREATE_ORDER_PAYMENT_FEE = 1001;
    public const ORDER_FAILED_TO_UPDATE_ORDER_TOTAL_WITH_PAYMENT_FEE = 1002;
}
