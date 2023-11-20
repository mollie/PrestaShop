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

namespace Mollie\Exception\Code;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ExceptionCode
{
    // Infrastructure error codes starts from 1000

    public const INFRASTRUCTURE_FAILED_TO_INSTALL_ORDER_STATE = 1001;
    public const INFRASTRUCTURE_UNKNOWN_ERROR = 1002;
    public const INFRASTRUCTURE_LOCK_EXISTS = 1003;
    public const INFRASTRUCTURE_LOCK_ON_ACQUIRE_IS_MISSING = 1004;
    public const INFRASTRUCTURE_LOCK_ON_RELEASE_IS_MISSING = 1005;

    public const FAILED_TO_FIND_CUSTOMER_ADDRESS = 2001;

    //Order error codes starts from 3000

    public const ORDER_FAILED_TO_UPDATE_ORDER_TOTALS = 3001;
    public const ORDER_FAILED_TO_INSERT_ORDER_PAYMENT_FEE = 3002;
    public const ORDER_FAILED_TO_RETRIEVE_PAYMENT_METHOD = 3003;
    public const ORDER_FAILED_TO_RETRIEVE_PAYMENT_FEE = 3004;
    public const ORDER_FAILED_TO_CREATE_ORDER_PAYMENT_FEE = 3005;
    public const ORDER_FAILED_TO_UPDATE_ORDER_TOTAL_WITH_PAYMENT_FEE = 3006;
}
