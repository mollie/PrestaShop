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
    public const ORDER_FAILED_TO_FIND_ORDER_CART = 1002;
    public const ORDER_FAILED_TO_FIND_ORDER_CUSTOMER = 1003;
    public const ORDER_FAILED_TO_APPLY_SELECTED_CARRIER = 1004;
    public const ORDER_FAILED_TO_FIND_ORDER_DELIVERY_ADDRESS = 1005;
    public const ORDER_FAILED_TO_FIND_ORDER_DELIVERY_COUNTRY = 1006;
    public const ORDER_FAILED_TO_GET_SELECTED_CARRIER_PRICE = 1007;
    public const ORDER_FAILED_TO_FIND_ORDER = 1008;
    public const ORDER_FAILED_TO_FIND_ORDER_DETAIL = 1009;
    public const ORDER_FAILED_TO_FIND_PRODUCT = 1010;
    public const ORDER_FAILED_TO_FIND_CURRENCY = 1011;

    //Cart error codes starts from 2000

    public const CART_ALREADY_HAS_SUBSCRIPTION_PRODUCT = 2001;

    //Recurring order error codes starts from 3000

    public const RECURRING_ORDER_FAILED_TO_FIND_SELECTED_CARRIER = 3001;
    public const RECURRING_ORDER_FAILED_TO_APPLY_SELECTED_CARRIER = 3002;
    public const RECURRING_ORDER_CART_AND_PAID_PRICE_ARE_NOT_EQUAL = 3003;
}
