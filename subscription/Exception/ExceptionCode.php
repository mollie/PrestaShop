<?php

namespace Mollie\Subscription\Exception;

class ExceptionCode
{
    //Order error codes starts from 1000

    public const ORDER_FAILED_TO_FIND_SELECTED_CARRIER = 1001;
    public const ORDER_FAILED_TO_FIND_ORDER_CART = 1002;
    public const ORDER_FAILED_TO_FIND_ORDER_CUSTOMER = 1003;
    public const ORDER_FAILED_TO_APPLY_SELECTED_CARRIER = 1004;
    public const ORDER_FAILED_TO_FIND_ORDER_DELIVERY_ADDRESS = 1005;
    public const ORDER_FAILED_TO_FIND_ORDER_DELIVERY_COUNTRY = 1006;
    public const ORDER_FAILED_TO_GET_SELECTED_CARRIER_PRICE = 1007;

    //Cart error codes starts from 2000

    public const CART_ALREADY_HAS_SUBSCRIPTION_PRODUCT = 2001;
}
