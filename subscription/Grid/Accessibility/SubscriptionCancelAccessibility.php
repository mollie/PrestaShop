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

declare(strict_types=1);

namespace Mollie\Subscription\Grid\Accessibility;

use Mollie\Api\Types\SubscriptionStatus;
use MolRecurringOrder;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\AccessibilityChecker\AccessibilityCheckerInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionCancelAccessibility implements AccessibilityCheckerInterface
{
    public function isGranted(array $record): bool
    {
        $subscriptionOrder = new MolRecurringOrder($record['id_mol_recurring_order']);

        return $subscriptionOrder->status !== SubscriptionStatus::STATUS_CANCELED;
    }
}
