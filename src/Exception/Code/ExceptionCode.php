<?php

namespace Mollie\Exception\Code;

class ExceptionCode
{
    // Infrastructure error codes starts from 1000

    public const INFRASTRUCTURE_FAILED_TO_INSTALL_ORDER_STATE = 1001;
    public const INFRASTRUCTURE_UNKNOWN_ERROR = 1002;

    public const FAILED_TO_FIND_CUSTOMER_ADDRESS = 2001;

    //Order error codes starts from 3000

    public const ORDER_FAILED_TO_UPDATE_ORDER_TOTALS = 3001;
    public const ORDER_FAILED_TO_INSERT_ORDER_PAYMENT_FEE = 3002;
    public const ORDER_FAILED_TO_RETRIEVE_PAYMENT_METHOD = 3003;
    public const ORDER_FAILED_TO_RETRIEVE_PAYMENT_FEE = 3004;
    public const ORDER_FAILED_TO_CREATE_ORDER_PAYMENT_FEE = 3005;
    public const ORDER_FAILED_TO_UPDATE_ORDER_TOTAL_WITH_PAYMENT_FEE = 3006;
}
