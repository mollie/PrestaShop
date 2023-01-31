<?php

declare(strict_types=1);

namespace Mollie\Subscription\Handler;

use Mollie\Exception\MollieException;
use Mollie\Subscription\Api\PaymentApi;
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\Factory\UpdateSubscriptionDataFactory;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;

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

    public function __construct(
        SubscriptionApi $subscriptionApi,
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        UpdateSubscriptionDataFactory $subscriptionDataFactory,
        PaymentApi $paymentApi
    ) {
        $this->subscriptionApi = $subscriptionApi;
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->subscriptionDataFactory = $subscriptionDataFactory;
        $this->paymentApi = $paymentApi;
    }

    public function handle(string $transactionId, string $subscriptionId)
    {
        $molPayment = $this->paymentApi->getPayment($transactionId);
        $recurringOrder = $this->recurringOrderRepository->findOneBy(['mollie_subscription_id' => $subscriptionId]);
        if (!$recurringOrder) {
            throw new MollieException('todo');
        }
        $subscriptionUpdateData = $this->subscriptionDataFactory->build($recurringOrder, $molPayment->mandateId);
        $newSubscription = $this->subscriptionApi->updateSubscription($subscriptionUpdateData);
        $recurringOrder->payment_method = $molPayment->method;
        $recurringOrder->mollie_subscription_id = $newSubscription->id;
        $recurringOrder->update();

        return $this->subscriptionApi->updateSubscription($subscriptionUpdateData);
    }
}
