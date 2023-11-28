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

    public const ORDER_FAILED_TO_FIND_SELECTED_CARRIER = 1001;
    public const ORDER_FAILED_TO_FIND_CART = 1002;
    public const ORDER_FAILED_TO_FIND_CUSTOMER = 1003;
    public const ORDER_FAILED_TO_APPLY_SELECTED_CARRIER = 1004;
    public const ORDER_FAILED_TO_FIND_DELIVERY_ADDRESS = 1005;
    public const ORDER_FAILED_TO_FIND_DELIVERY_COUNTRY = 1006;
    public const ORDER_FAILED_TO_GET_SELECTED_CARRIER_PRICE = 1007;
    public const ORDER_FAILED_TO_FIND_ORDER = 1008;
    public const ORDER_FAILED_TO_FIND_ORDER_DETAIL = 1009;
    public const ORDER_FAILED_TO_FIND_PRODUCT = 1010;
    public const ORDER_FAILED_TO_FIND_CURRENCY = 1011;
    public const ORDER_FAILED_TO_FIND_MOLLIE_CUSTOMER = 1012;
    public const ORDER_FAILED_TO_RETRIEVE_SUBSCRIPTION_INTERVAL = 1013;
    public const ORDER_FAILED_TO_PROVIDE_CARRIER_DELIVERY_PRICE = 1014;
    public const ORDER_FAILED_TO_FIND_COMBINATION = 1015;
    public const ORDER_FAILED_TO_FIND_MATCHING_INTERVAL = 1016;

    //Cart error codes starts from 2000

    public const CART_ALREADY_HAS_SUBSCRIPTION_PRODUCT = 2001;
    public const CART_SUBSCRIPTION_SERVICE_DISABLED = 2002;
    public const CART_SUBSCRIPTION_CARRIER_INVALID = 2003;

    //Recurring order error codes starts from 3000

    public const RECURRING_ORDER_FAILED_TO_FIND_SELECTED_CARRIER = 3001;
    public const RECURRING_ORDER_FAILED_TO_APPLY_SELECTED_CARRIER = 3002;
    public const RECURRING_ORDER_CART_AND_PAID_PRICE_ARE_NOT_EQUAL = 3003;
    public const RECURRING_ORDER_FAILED_TO_MATCH_SELECTED_CARRIER = 3004;

    public const UNKNOWN_ERROR = 9001;
}
