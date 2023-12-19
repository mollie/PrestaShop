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

namespace Mollie\Subscription\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ExceptionCode
{
    //Order error codes starts from 1000

    public const ORDER_FAILED_TO_APPLY_SELECTED_CARRIER = 1001;
    public const ORDER_FAILED_TO_GET_SELECTED_CARRIER_PRICE = 1002;
    public const ORDER_FAILED_TO_FIND_ORDER = 1003;
    public const ORDER_FAILED_TO_FIND_ORDER_DETAIL = 1004;
    public const ORDER_FAILED_TO_FIND_PRODUCT = 1005;
    public const ORDER_FAILED_TO_FIND_CURRENCY = 1006;
    public const ORDER_FAILED_TO_FIND_MOLLIE_CUSTOMER = 1007;
    public const ORDER_FAILED_TO_RETRIEVE_SUBSCRIPTION_INTERVAL = 1008;
    public const ORDER_FAILED_TO_PROVIDE_CARRIER_DELIVERY_PRICE = 1009;
    public const ORDER_FAILED_TO_FIND_COMBINATION = 1010;
    public const ORDER_FAILED_TO_FIND_MATCHING_INTERVAL = 1011;
    public const ORDER_INVALID_SUBSCRIPTION_SETTINGS = 1012;
    public const ORDER_FAILED_TO_FIND_SUBSCRIPTION_PRODUCT = 1013;
    public const ORDER_FAILED_TO_CREATE_SUBSCRIPTION_DATA = 1014;
    public const ORDER_FAILED_TO_SUBSCRIBE_ORDER = 1015;
    public const ORDER_FAILED_TO_CREATE_RECURRING_ORDERS_PRODUCT = 1016;
    public const ORDER_FAILED_TO_CREATE_RECURRING_ORDER = 1017;
    public const ORDER_FAILED_TO_PROVIDE_SUBSCRIPTION_ORDER_AMOUNT = 1018;
    public const ORDER_FAILED_TO_UPDATE_SUBSCRIPTION = 1019;

    //Cart error codes starts from 2000

    public const CART_INVALID_SUBSCRIPTION_SETTINGS = 2001;
    public const CART_ALREADY_HAS_SUBSCRIPTION_PRODUCT = 2002;
    public const CART_SUBSCRIPTION_SERVICE_DISABLED = 2003;
    public const CART_SUBSCRIPTION_CARRIER_INVALID = 2004;

    //Recurring order error codes starts from 3000

    public const RECURRING_ORDER_FAILED_TO_FIND_SELECTED_CARRIER = 3001;
    public const RECURRING_ORDER_FAILED_TO_APPLY_SELECTED_CARRIER = 3002;
    public const RECURRING_ORDER_CART_AND_PAID_PRICE_ARE_NOT_EQUAL = 3003;
    public const RECURRING_ORDER_FAILED_TO_MATCH_SELECTED_CARRIER = 3004;
    public const RECURRING_ORDER_FAILED_TO_DUPLICATE_CART = 3005;
    public const RECURRING_ORDER_SUBSCRIPTION_CART_SHOULD_HAVE_ONE_PRODUCT = 3006;
    public const RECURRING_ORDER_FAILED_TO_CREATE_SPECIFIC_PRICE = 3007;
    public const RECURRING_ORDER_FAILED_TO_HANDLE_SUBSCRIPTION_CART_CLONING = 3008;

    public const UNKNOWN_ERROR = 9001;
}
