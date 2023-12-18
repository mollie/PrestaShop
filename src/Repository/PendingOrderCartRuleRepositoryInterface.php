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

use Mollie\Shared\Infrastructure\Repository\ReadOnlyRepositoryInterface;
use MolPendingOrderCartRule;
use Order;
use OrderCartRule;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface PendingOrderCartRuleRepositoryInterface extends ReadOnlyRepositoryInterface
{
    /**
     * @param int $orderId
     * @param int $cartRuleId
     */
    public function removePreviousPendingOrderCartRule($orderId, $cartRuleId);

    /**
     * Used to create MolPendingOrderCartRule from OrderCartRule to be used later on successful payment to increase customer used cart rule quantity.
     *
     * @param int $orderId
     * @param int $cartRuleId
     */
    public function createPendingOrderCartRule($orderId, $cartRuleId, OrderCartRule $orderCartRule);

    /**
     * Used to create OrderCartRule from MolPendingOrderCartRule
     */
    public function usePendingOrderCartRule(Order $order, MolPendingOrderCartRule $pendingOrderCartRule);
}
