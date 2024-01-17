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

namespace Mollie\Repository;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface OrderCartRuleRepositoryInterface extends ReadOnlyRepositoryInterface
{
    /**
     * @param int $orderId
     * @param int $cartRuleId
     *
     * @return bool
     */
    public function decreaseCustomerUsedCartRuleQuantity($orderId, $cartRuleId);
}
