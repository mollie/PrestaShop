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

namespace Mollie\Enum;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentTypeEnum
{
    const PAYMENT_TYPE_PAYMENT = 0;

    const PAYMENT_TYPE_ORDER = 1;
}
