<?php

declare(strict_types=1);

namespace Mollie\Subscription\Factory;

use Mollie\Subscription\DTO\UpdateSubscriptionData;
use MolRecurringOrder;

class UpdateSubscriptionDataFactory
{
    public function build(MolRecurringOrder $subscription, string $mandateId): UpdateSubscriptionData
    {
        return new UpdateSubscriptionData($subscription->mollie_customer_id, $subscription->mollie_subscription_id, $mandateId);
    }
}
