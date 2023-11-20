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

namespace Mollie\Verification\PaymentType;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface PaymentTypeVerificationInterface
{
    /**
     * @param string $transactionId
     *
     * @return bool
     */
    public function verify($transactionId);
}
