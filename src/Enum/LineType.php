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

class LineType
{
    /**
     * For Orders API
     *
     * Will be deprecated in the end of 2025.
     */
    const ORDER = 'OrderLine';

    /**
     * For Payments API
     */
    const PAYMENT = 'PaymentLine';
}
