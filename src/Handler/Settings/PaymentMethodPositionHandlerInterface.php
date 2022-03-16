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

interface PaymentMethodPositionHandlerInterface
{
    /**
     * @param array $positions - key is id of MolPaymentMethod and value is numeric position
     *
     * @return mixed
     */
    public function savePositions(array $positions);
}
