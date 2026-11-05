<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderCreationException extends \Exception
{
    public const DEFAULT_ORDER_CREATION_EXCEPTION = 1;

    public const WRONG_BILLING_PHONE_NUMBER_EXCEPTION = 2;

    public const WRONG_SHIPPING_PHONE_NUMBER_EXCEPTION = 3;

    public const ORDER_TOTAL_LOWER_THAN_MINIMUM = 4;

    public const ORDER_TOTAL_HIGHER_THAN_MAXIMUM = 5;

    public const ORDER_RESOURSE_IS_MISSING = 6;

    public const ORDER_IS_NOT_CREATED = 7;

    public const WRONG_FAMILY_NAME = 8;

    public const WRONG_GIVEN_NAME = 9;
}
