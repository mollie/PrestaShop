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

namespace Mollie\Subscription\Handler;

use Mollie\Api\Resources\Subscription as MollieSubscription;
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\Exception\SubscriptionApiException;
use Mollie\Subscription\Factory\CancelSubscriptionDataFactory;
use Mollie\Subscription\Factory\GetSubscriptionDataFactory;
use Mollie\Subscription\Utility\ClockInterface;
use MolRecurringOrder;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionCancellationHandler
{
    /** @var ClockInterface */
    private $clock;
    /** @var SubscriptionApi */
    private $subscriptionApi;
    /** @var CancelSubscriptionDataFactory */
    private $cancelSubscriptionDataFactory;
    /** @var GetSubscriptionDataFactory */
    private $getSubscriptionDataFactory;

    public function __construct(
        ClockInterface $clock,
        SubscriptionApi $subscriptionApi,
        CancelSubscriptionDataFactory $cancelSubscriptionDataFactory,
        GetSubscriptionDataFactory $getSubscriptionDataFactory
    ) {
        $this->clock = $clock;
        $this->subscriptionApi = $subscriptionApi;
        $this->cancelSubscriptionDataFactory = $cancelSubscriptionDataFactory;
        $this->getSubscriptionDataFactory = $getSubscriptionDataFactory;
    }

    public function handle(int $subscriptionId): MollieSubscription
    {
        $cancelSubscriptionData = $this->cancelSubscriptionDataFactory->build($subscriptionId);
        try {
            $subscription = $this->subscriptionApi->cancelSubscription($cancelSubscriptionData);
        } catch (SubscriptionApiException $e) {
            $getSubscriptionData = $this->getSubscriptionDataFactory->build($subscriptionId);
            $subscription = $this->subscriptionApi->getSubscription($getSubscriptionData);
        }

        $recurringOrder = new MolRecurringOrder($subscriptionId);

        if ($subscription->status === $recurringOrder->status) {
            return $subscription;
        }

        $recurringOrder->status = $subscription->status;
        $recurringOrder->cancelled_at = $this->clock->getDateFromTimeStamp(strtotime($subscription->canceledAt)); //todo: maybe we will need to change what date is added
        $recurringOrder->date_update = $this->clock->getCurrentDate();
        $recurringOrder->update();

        return $subscription;
    }
}
