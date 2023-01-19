<?php

declare(strict_types=1);

namespace Mollie\Subscription\Grid\Accessibility;

use Mollie\Api\Types\SubscriptionStatus;
use MolRecurringOrder;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\AccessibilityChecker\AccessibilityCheckerInterface;

class SubscriptionCancelAccessibility implements AccessibilityCheckerInterface
{
    public function isGranted(array $record): bool
    {
        $subscriptionOrder = new MolRecurringOrder($record['id_mol_recurring_order']);

        return $subscriptionOrder->status !== SubscriptionStatus::STATUS_CANCELED;
    }
}
