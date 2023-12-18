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

use Mollie\Subscription\Api\PaymentApi;
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\Factory\UpdateSubscriptionDataFactory;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Utility\ClockInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionPaymentMethodUpdateHandler
{
    /** @var SubscriptionApi */
    private $subscriptionApi;
    /** @var RecurringOrderRepositoryInterface */
    private $recurringOrderRepository;
    /** @var UpdateSubscriptionDataFactory */
    private $subscriptionDataFactory;
    /** @var PaymentApi */
    private $paymentApi;
    /** @var ClockInterface */
    private $clock;

    public function __construct(
        SubscriptionApi $subscriptionApi,
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        UpdateSubscriptionDataFactory $subscriptionDataFactory,
        PaymentApi $paymentApi,
        ClockInterface $clock
    ) {
        $this->subscriptionApi = $subscriptionApi;
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->subscriptionDataFactory = $subscriptionDataFactory;
        $this->paymentApi = $paymentApi;
        $this->clock = $clock;
    }

    /**
     * @throws \Throwable
     */
    public function handle(string $transactionId, string $subscriptionId)
    {
        $molPayment = $this->paymentApi->getPayment($transactionId);

        /** @var \MolRecurringOrder $recurringOrder */
        $recurringOrder = $this->recurringOrderRepository->findOrFail(['mollie_subscription_id' => $subscriptionId]);

        $subscriptionUpdateData = $this->subscriptionDataFactory->build($recurringOrder, $molPayment->mandateId);
        $newSubscription = $this->subscriptionApi->updateSubscription($subscriptionUpdateData);
        $recurringOrder->payment_method = $molPayment->method;
        $recurringOrder->mollie_subscription_id = $newSubscription->id;
        $recurringOrder->date_update = $this->clock->getCurrentDate();
        $recurringOrder->update();

        return $this->subscriptionApi->updateSubscription($subscriptionUpdateData);
    }
}
