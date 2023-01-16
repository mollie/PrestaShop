<?php

declare(strict_types=1);

namespace Mollie\Subscription\Handler;

use Mollie\Subscription\Utility\ClockInterface;

class RecurringOrderCancellation
{
    /** @var ClockInterface */
    private $clock;

    public function __construct(ClockInterface $clock)
    {
        $this->clock = $clock;
    }

    public function handle(int $subscriptionId, string $status, string $canceledAt): void
    {
        $recurringOrder = new \MolSubRecurringOrder($subscriptionId);
        $recurringOrder->status = $status;
        $recurringOrder->cancelled_at = $this->clock->getDateFromTimeStamp(strtotime($canceledAt)); //todo: maybe we will need to change what date is added
        $recurringOrder->date_update = $this->clock->getCurrentDate();
        $recurringOrder->update();
    }
}
