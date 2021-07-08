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

namespace Mollie\Handler\OrderTotal;

use Mollie\Exception\OrderTotalRestrictionException;

interface OrderTotalUpdaterHandlerInterface
{
    /**
     * @return bool
     *
     * @throws OrderTotalRestrictionException
     */
    public function handleOrderTotalUpdate();
}
