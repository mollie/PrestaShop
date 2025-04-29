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

namespace Mollie\Handler\Settings;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface CustomerGroupRestrictionHandlerInterface
{
    /**
     * Save customer group restrictions for a payment method
     *
     * @param int $paymentMethodId
     * @param string $methodId
     *
     * @return void
     */
    public function saveRestrictions(int $paymentMethodId, string $methodId): void;
}
