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

use Mollie;
use Mollie\Errors\Http\HttpStatusCode;
use Mollie\Exception\TransactionException;
use Mollie\Subscription\Api\PaymentApi;
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\Factory\GetSubscriptionDataFactory;
use Mollie\Subscription\Factory\UpdateSubscriptionDataFactory;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Utility\ClockInterface;
use Mollie\Utility\SecureKeyUtility;

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
    /** @var GetSubscriptionDataFactory */
    private $getSubscriptionDataFactory;
    /** @var Mollie */
    private $mollie;

    public function __construct(
        SubscriptionApi $subscriptionApi,
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        UpdateSubscriptionDataFactory $subscriptionDataFactory,
        PaymentApi $paymentApi,
        ClockInterface $clock,
        GetSubscriptionDataFactory $getSubscriptionDataFactory,
        Mollie $mollie
    ) {
        $this->subscriptionApi = $subscriptionApi;
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->subscriptionDataFactory = $subscriptionDataFactory;
        $this->paymentApi = $paymentApi;
        $this->clock = $clock;
        $this->getSubscriptionDataFactory = $getSubscriptionDataFactory;
        $this->mollie = $mollie;
    }

    /**
     * @throws \Throwable
     */
    public function handle(string $transactionId, string $subscriptionId)
    {
        /** @var \MolRecurringOrder $recurringOrder */
        $recurringOrder = $this->recurringOrderRepository->findOrFail(['mollie_subscription_id' => $subscriptionId]);

        $subscriptionData = $this->getSubscriptionDataFactory->build((int) $recurringOrder->id);
        $subscription = $this->subscriptionApi->getSubscription($subscriptionData);

        $key = SecureKeyUtility::generateReturnKey(
            $recurringOrder->id_customer,
            $recurringOrder->id_cart,
            $this->mollie->name
        );

        if ($key !== $subscription->metadata->secure_key) {
            throw new TransactionException('Security key is incorrect.', HttpStatusCode::HTTP_UNAUTHORIZED);
        }

        $molPayment = $this->paymentApi->getPayment($transactionId);

        $subscriptionUpdateData = $this->subscriptionDataFactory->build($recurringOrder, $molPayment->mandateId);
        $newSubscription = $this->subscriptionApi->updateSubscription($subscriptionUpdateData);
        $recurringOrder->payment_method = $molPayment->method;
        $recurringOrder->mollie_subscription_id = $newSubscription->id;
        $recurringOrder->date_update = $this->clock->getCurrentDate();
        $recurringOrder->update();

        return $this->subscriptionApi->updateSubscription($subscriptionUpdateData);
    }
}
