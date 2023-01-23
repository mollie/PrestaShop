<?php

declare(strict_types=1);

namespace Mollie\Subscription\Handler;

use Mollie\Subscription\Utility\ClockInterface;
use MolRecurringOrder;

class SubscriptionCancellationHandler
{
    /** @var ClockInterface */
    private $clock;

    public function __construct(ClockInterface $clock)
    {
        $this->clock = $clock;
    }

    public function handle(int $subscriptionId, string $status, string $canceledAt): void
    {
        $recurringOrder = new MolRecurringOrder($subscriptionId);
        $recurringOrder->status = $status;
        $recurringOrder->cancelled_at = $this->clock->getDateFromTimeStamp(strtotime($canceledAt)); //todo: maybe we will need to change what date is added
        $recurringOrder->date_update = $this->clock->getCurrentDate();
        $recurringOrder->update();
    }
}
