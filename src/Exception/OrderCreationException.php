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

namespace Mollie\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderCreationException extends \Exception
{
    const DEFAULT_ORDER_CREATION_EXCEPTION = 1;

    const WRONG_BILLING_PHONE_NUMBER_EXCEPTION = 2;

    const WRONG_SHIPPING_PHONE_NUMBER_EXCEPTION = 3;

    const ORDER_TOTAL_LOWER_THAN_MINIMUM = 4;

    const ORDER_TOTAL_HIGHER_THAN_MAXIMUM = 5;

    const ORDER_RESOURSE_IS_MISSING = 6;

    const ORDER_IS_NOT_CREATED = 7;
}
